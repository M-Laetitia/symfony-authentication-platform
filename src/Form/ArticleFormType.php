<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\ArticleType;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The title cannot be empty',
                    ]),
                    new Assert\Length([
                        'max' => 150,
                        'maxMessage' => 'The title cannot exceed {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Article title',
                    'maxlength' => 150,
                ],
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'Excerpt',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 160,
                        'maxMessage' => 'The excerpt cannot exceed {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Short summary of the article (max 160 characters)',
                    'maxlength' => 160,
                    'rows' => 3,
                ],
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'Meta Title (SEO)',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The meta title is required',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'The meta title cannot exceed {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Title for search engines',
                    'maxlength' => 255,
                ],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta Description (SEO)',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The meta description is required',
                    ]),
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'The meta description cannot exceed {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Description for search engines',
                    'rows' => 3,
                    'maxlength' => 500,
                ],
            ])
            ->add('introduction', TextareaType::class, [
                'label' => 'Introduction',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The introduction cannot be empty',
                    ]),
                    new Assert\Length([
                        'min' => 50,
                        'max' => 1000,
                        'minMessage' => 'The introduction must contain at least {{ limit }} characters',
                        'maxMessage' => 'The introduction cannot exceed {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Article introduction (between 50 and 1000 characters)',
                    'rows' => 4,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => ArticleType::DRAFT,
                    'Published' => ArticleType::PUBLISHED,
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'The status is required',
                    ]),
                ],
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Featured article',
                'required' => false,
                'help' => 'Check this box to feature this article on the homepage',
            ])
            ->add('content', HiddenType::class, [
                'mapped' => false, 
                'required' => false,
                'attr' => ['id' => 'article_form_content']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'placeholder' => 'Select a category',
                'required' => false,
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Please select a category',
                    ]),
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'label' => 'Tags',
                'multiple' => true,      
                'expanded' => true,    
                'by_reference' => false,
                'required' => false,
                'help' => 'Select one or more tags for this article',
            ])
            ->add('coverFile', FileType::class, [
                'mapped' => false, 
                'required' => false,
                'label' => 'Cover image',
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WebP)',
                        'maxSizeMessage' => 'The image must not exceed {{ limit }} {{ suffix }}',
                    ]),
                ],
                'help' => 'Accepted formats: JPEG, PNG, WebP (max 5 MB)',
            ])
            ->add('coverAlt', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Cover image short description (alt text)',
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'The alt text cannot exceed {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Image description for accessibility',
                    'maxlength' => 255,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create article',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'csrf_protection' => true, 
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'article_form',
        ]);
    }
}
