<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Form;

use EMS\FormBundle\Components\Form;
use EMS\FormBundle\FormConfig\SubFormConfig;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubFormType extends Form
{
    /**
     * @param FormInterface<mixed> $form
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['config'] = $options['config'];
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('config')
            ->setAllowedTypes('config', SubFormConfig::class)
        ;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return FormType::class;
    }

    #[\Override]
    public function getBlockPrefix(): ?string
    {
        return 'ems_subform';
    }
}
