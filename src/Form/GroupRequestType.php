<?php declare(strict_types = 1);

namespace App\Form;

use App\Entity\GroupRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GroupRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Group name'])
            ->add('email', EmailType::class, ['label' => 'Organizer email'])
            ->add('urlname', TextType::class, ['label' => 'Meetup urlname'])
            ->add('comment', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', GroupRequest::class);
    }
}
