<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use EMS\SubmissionBundle\Connection\Transformer;
use EMS\SubmissionBundle\Exception\SkipSubmissionException;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class ConnectionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private Transformer $transformer)
    {
    }

    public function transform(string $content): string
    {
        return $this->transformer->transform(\explode('%.%', $content));
    }

    public function skipSubmitException(): never
    {
        throw new SkipSubmissionException();
    }
}
