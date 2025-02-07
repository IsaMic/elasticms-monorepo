<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Extension;

use EMS\Helpers\Standard\Locale;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleFormExtension extends AbstractTypeExtension
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['locale' => null])
            ->setNormalizer('locale', function (Options $options, string|array|null $value) {
                try {
                    $language = $options['language'] ?? null;

                    return ($language) ? Locale::getLanguage($language) : $value;
                } catch (\Throwable) {
                    return $value;
                }
            });
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['locale'] = $options['locale'];
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
