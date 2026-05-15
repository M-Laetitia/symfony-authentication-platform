<?php

namespace App\Command;

use App\Entity\Media;
use App\Enum\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:regenerate-image-cache',
    description: 'Regenerates LiipImagine cache for all media files in the database'
)]
class RegenerateImageCacheCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilterManager $filterManager,
        private CacheManager $cacheManager,
        private DataManager $dataManager,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Regenerate only a specific filter')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Regenerate only for a specific MediaType (e.g., article_cover)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $filterOption = $input->getOption('filter');
        $typeOption = $input->getOption('type');

        $io->title('Regenerating Image Cache');

        // Get all media from database
        $mediaRepository = $this->em->getRepository(Media::class);
        $allMedia = $mediaRepository->findAll();

        if (empty($allMedia)) {
            $io->warning('No media files found in database.');
            return Command::SUCCESS;
        }

        // Filter by type if specified
        if ($typeOption) {
            $allMedia = array_filter($allMedia, function(Media $media) use ($typeOption) {
                return $media->getType()->value === $typeOption;
            });
        }

        $io->info(sprintf('Found %d media file(s) to process.', count($allMedia)));

        $progressBar = $io->createProgressBar(count($allMedia));
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($allMedia as $media) {
            try {
                $relativePath = $media->getPath();
                $mediaType = $media->getType();

                // Get filters for this media type
                $filters = $filterOption 
                    ? [$filterOption] 
                    : $this->getFiltersForMediaType($mediaType);

                foreach ($filters as $filterName) {
                    try {
                        $this->cacheManager->store(
                            $this->filterManager->applyFilter(
                                $this->dataManager->find($filterName, $relativePath),
                                $filterName
                            ),
                            $relativePath,
                            $filterName
                        );
                    } catch (\Throwable $e) {
                        $this->logger->error("Failed to generate filter '$filterName' for '$relativePath': " . $e->getMessage());
                        $errorCount++;
                    }
                }

                $successCount++;
            } catch (\Throwable $e) {
                $this->logger->error("Error processing media {$media->getId()}: " . $e->getMessage());
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);

        $io->success(sprintf(
            'Cache regeneration complete! Successfully processed: %d, Errors: %d',
            $successCount,
            $errorCount
        ));

        return Command::SUCCESS;
    }

    /**
     * Maps MediaType to relevant LiipImagine filters
     * Same mapping as in MediaUploader service
     */
    private function getFiltersForMediaType(MediaType $type): array
    {
        return match($type) {
            MediaType::ARTICLE_COVER => [
                'article_cover',
                'blog_thumb',
                'blog_thumb_mobile',
                'article_cover_thumb'
            ],
            MediaType::ARTICLE_IMAGE => [
                'thumbnail_small',
                'thumbnail_medium',
                'thumbnail_large'
            ],
            MediaType::PORTFOLIO_COVER => [
                'gallery_thumb_desktop',
                'gallery_thumb_mobile',
                'gallery_thumb_index',
                'gallery_thumb_edit'
            ],
            MediaType::PORTFOLIO_FEATURED => [
                'works_thumb_landscape',
                'works_thumb_portrait'
            ],
            MediaType::GALLERY_SERIES => [
                'gallery_thumb_desktop',
                'gallery_thumb_mobile'
            ],
            MediaType::AVATAR => [
                'thumbnail_small'
            ],
            default => []
        };
    }
}
