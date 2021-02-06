<?php

declare(strict_types=1);

namespace Forex;

use Forex\Exception\MissingArgumentException;

class Validator
{
    const MANDATORY_PARAMETERS = [
        'currency-1',
        'currency-2',
    ];

    /**
     * @var array
     */
    private array $parameters;

    /**
     * @var string
     */
    private string $pair;

    /**
     * @var string
     */
    private string $pairFormatted;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @throws MissingArgumentException
     */
    public function check(): void
    {
        foreach (self::MANDATORY_PARAMETERS as $mandatoryParameter) {
            if (!isset($this->parameters[$mandatoryParameter])) {
                throw new MissingArgumentException('Check app configuration');
            }
        }

        $this->pair          = addslashes($this->parameters['currency-1']) . addslashes($this->parameters['currency-2']);
        $this->pairFormatted = addslashes($this->parameters['currency-1']) . '/' . addslashes($this->parameters['currency-2']);
    }

    /**
     * @return string
     */
    public function getPair(): string
    {
        return strtoupper($this->pair);
    }

    /**
     * @return string
     */
    public function getPairFormatted(): string
    {
        return strtoupper($this->pairFormatted);
    }
}
