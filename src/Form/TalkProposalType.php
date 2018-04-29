<?php

declare(strict_types = 1);

namespace App\Form;

use App\Meetup\TalkProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TalkProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Your name'])
            ->add('email', EmailType::class, ['label' => 'Your email address'])
            ->add('twitter', TextType::class, ['label' => 'Your twitter handle'])
            ->add('abstract', TextareaType::class, ['label' => 'Talk title and short abstract'])
            ->add('bio', TextareaType::class, ['label' => 'Speaker bio'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', TalkProposal::class);
    }
}
