<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;


class CommentFormType extends HoneyPotType
{
    private Security $security;

    public function __construct(Security $security,LoggerInterface $honeyPotLogger,RequestStack $requestStack)
     {
        $this->security = $security;
        parent::__construct($honeyPotLogger, $requestStack);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Write your comment here...',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Comment cannot be empty'
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 2000,
                        'minMessage' => 'Comment is too short',
                        'maxMessage' => 'Comment is too long'
                    ])
                ]
            ])

            ->add('submittedAt', HiddenType::class, [
                'mapped' => false, 
                'data' => time(),    // timestamp when the form is rendered
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            
            // if the user is not logged in, add the pseudo field
            if (!$this->security->getUser()) {
                $form->add('authorName', TextType::class, [
                    'label' => false,
                    'mapped' => false, 
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Your name',
                        'autocomplete' => 'off'
                    ],
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Name is required'
                        ]),
                        new Assert\Length([
                            'min' => 2,
                            'max' => 50,
                            'minMessage' => 'Your name must be at least {{ limit }} characters',
                            'maxMessage' => 'Your name cannot exceed {{ limit }} characters'
                        ])
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'include_author_name' => true,
            'csrf_protection' => true, 
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'comment_form',
            'honeypot_field_1' => 'nickname', 
            'honeypot_field_2' => 'title', 
            'form_type' => ' article comment',
        ]);
    }
}
