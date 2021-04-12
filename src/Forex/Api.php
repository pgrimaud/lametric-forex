<?php

declare(strict_types=1);

namespace Forex;

use Forex\Exception\InvalidArgumentException;
use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Api
{
    const DATA_ENDPOINT = 'https://www.freeforexapi.com/api/live';

    /**
     * @var GuzzleClient
     */
    private GuzzleClient $guzzleClient;

    /**
     * @var PredisClient
     */
    private PredisClient $predisClient;

    /**
     * @var array
     */
    private array $data;

    /**
     * @param GuzzleClient $guzzleClient
     * @param PredisClient $predisClient
     */
    public function __construct(GuzzleClient $guzzleClient, PredisClient $predisClient)
    {
        $this->guzzleClient = $guzzleClient;
        $this->predisClient = $predisClient;
    }

    /**
     * @param string $pair
     *
     * @throws InvalidArgumentException
     */
    public function fetchData(string $pair): void
    {
        $redisKey = 'lametric:forex-' . strtolower($pair);

        $launchesFile = $this->predisClient->get($redisKey);
        $ttl          = $this->predisClient->ttl($redisKey);

        if (!$launchesFile || $ttl < 0) {
            $this->data = $this->callApi($pair);

            // save to redis
            $this->predisClient->set($redisKey, json_encode($this->data));
            $this->predisClient->expire($redisKey, 60 * 5);
        } else {
            $this->data = json_decode($launchesFile, true);
        }
    }

    /**
     * @param string $pair
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function callApi(string $pair): array
    {
        $endpoint = $this->generateApiUrl($pair);

        $resource = $this->guzzleClient->request('GET', $endpoint);
        $data     = json_decode((string) $resource->getBody(), true);

        if ((int) $data['code'] !== 200) {
            throw new InvalidArgumentException('Invalid pair');
        }

        return [
            'price' => $data['rates'][$pair]['rate'] ?? ['price' => 0],
        ];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $pair
     *
     * @return string
     */
    private function generateApiUrl(string $pair): string
    {
        return self::DATA_ENDPOINT . '?pairs=' . $pair;
    }
}
