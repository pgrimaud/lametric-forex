<?php

declare(strict_types=1);

namespace Forex;

use Forex\Exception\MissingArgumentException;

class Validator
{
    const MANDATORY_PARAMETERS = [
        'currency-1',
        'currency-2',
        'change',
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
     * @var bool
     */
    private bool $showChange;

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

        $this->pair = addslashes($this->parameters['currency-1']) . '/' . addslashes($this->parameters['currency-2']);

        $this->showChange = (bool)$this->parameters['change'];
    }

    /**
     * @return string
     */
    public function getPair(): string
    {
        return $this->pair;
    }

    /**
     * @return bool
     */
    public function showChange(): bool
    {
        return $this->showChange;
    }
}
