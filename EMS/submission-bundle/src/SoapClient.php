<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle;

final readonly class SoapClient
{
    private \SoapClient $client;

    /**
     * @param array<mixed> $options
     */
    public function __construct(?string $wsdl, array $options)
    {
        $this->client = new \SoapClient($wsdl, $options);
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    public function call(string $functionName, array $arguments = [])
    {
        return $this->client->__soapCall($functionName, $arguments);
    }
}
