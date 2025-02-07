<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Field;

use EMS\CommonBundle\Helper\EmsFields;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class AssetType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['multiple'] ?? false) {
            $builder->add('files', CollectionType::class, [
                'entry_type' => AssetType::class,
                'entry_options' => [
                    'multiple' => false,
                ],
                'allow_add' => true,
                'prototype' => true,
            ]);
        } else {
            $builder->add('sha1', HiddenType::class, [
                'attr' => [
                    'class' => 'sha1',
                ],
                'required' => $options['required'],
            ])
            ->add(EmsFields::CONTENT_IMAGE_RESIZED_HASH_FIELD, HiddenType::class, [
                'attr' => [
                    'class' => 'resized-image-hash',
                ],
                'required' => false,
            ])
            ->add('mimetype', TextType::class, [
                'attr' => [
                    'class' => 'type',
                ],
                'required' => $options['required'],
            ])
            ->add('filename', TextType::class, [
                'attr' => [
                    'class' => 'name',
                ],
                'required' => $options['required'],
            ])
            ->add(EmsFields::CONTENT_FILE_TITLE, TextType::class, [
                'required' => $options['required'],
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('multiple', false);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'assettype';
    }
}
