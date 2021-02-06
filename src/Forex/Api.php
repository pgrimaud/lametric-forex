<?php

declare(strict_types=1);

namespace Forex;

use Forex\Exception\InvalidArgumentException;
use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Api
{
    const DATA_ENDPOINT = 'https://fcsapi.com/api-v3/forex/latest';

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
     * @var string
     */
    private string $apiKey;

    /**
     * @param GuzzleClient $guzzleClient
     * @param PredisClient $predisClient
     * @param string $apiKey
     */
    public function __construct(GuzzleClient $guzzleClient, PredisClient $predisClient, string $apiKey)
    {
        $this->guzzleClient = $guzzleClient;
        $this->predisClient = $predisClient;
        $this->apiKey       = $apiKey;
    }

    /**
     * @param string $pair
     *
     * @throws InvalidArgumentException
     */
    public function fetchData(string $pair): void
    {
        $redisKey = 'lametric:forex-' . $pair;

        $launchesFile = $this->predisClient->get($redisKey);
        $ttl          = $this->predisClient->ttl($redisKey);

        if (!$launchesFile || $ttl < 0) {
            $this->data = $this->callApi($pair);

            // save to redis
            $this->predisClient->set($redisKey, json_encode($this->data), 60 * 60 * 3);
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
        $data     = json_decode((string)$resource->getBody(), true);

        if (!$data['status']) {
            throw new InvalidArgumentException('Invalid pair');
        }

        return [
            'price'  => $data['response'][0]['price'],
            'change' => $data['response'][0]['chg_per'],
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
        return self::DATA_ENDPOINT . '?symbol=' . $pair . '&access_key=' . $this->apiKey;
    }
}
