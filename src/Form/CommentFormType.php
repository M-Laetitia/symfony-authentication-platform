<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class CommentFormType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class)
            // ->add('authorName', TextareaType::class);
            // ->add('author', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])

            // if ($options['include_author_name']) {
            //     $builder->add('authorName', TextType::class, [
            //         'label' => 'Votre nom',
            //     ]);
            // }
            ->add('submittedAt', HiddenType::class, [
                'mapped' => false,   // ne correspond pas à une propriété de Comment
                'data' => time(),    // timestamp au moment du rendu du formulaire
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            
            // Si l'utilisateur n'est pas connecté, on ajoute le champ pseudo
            if (!$this->security->getUser()) {
                $form->add('authorName', TextareaType::class, [
                    'label' => 'Votre pseudo',
                    'mapped' => false, 
                    'required' => true
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'include_author_name' => true,
            'csrf_protection' => true, 
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'comment_form',
        ]);
    }
}
