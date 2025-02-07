<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Nature;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ItemsType extends AbstractType
{
    public const PREFIX = 'item-';

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $result = $options['result'];

        foreach ($result['hits']['hits'] as $hit) {
            $builder->add(\implode('', [self::PREFIX, $hit['_id']]), HiddenType::class, [
                'attr' => [
                ],
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'result' => [],
        ]);
    }
}
