<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SoapRequest extends AbstractRequest
{
    /** @var array{operation: string, wsdl: string|null, options: array<string, mixed>} */
    private readonly array $endpoint;

    /**
     * @param array<mixed> $endpoint
     */
    public function __construct(array $endpoint)
    {
        /** @var array{operation: string, wsdl: string|null, options: array<string, mixed>} $endpoint */
        $endpoint = $this->resolveEndpoint($endpoint);
        $this->endpoint = $endpoint;
    }

    public function getOperation(): string
    {
        return $this->endpoint['operation'];
    }

    public function getWsdl(): ?string
    {
        return $this->endpoint['wsdl'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->endpoint['options'];
    }

    #[\Override]
    protected function getEndpointOptionResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired('operation')
            ->setDefaults([
                'wsdl' => null,
                'options' => [],
            ]);

        return $optionsResolver;
    }
}
