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
     * @var array
     */
    private array $data;

    /**
     * @param GuzzleClient $guzzleClient
     * @param PredisClient $predisClient
     */
    public function __construct(private GuzzleClient $guzzleClient, private PredisClient $predisClient)
    {
    }

    /**
     * @param Validator $validator
     *
     * @throws InvalidArgumentException
     */
    public function fetchData(Validator $validator): void
    {
        $redisKey = 'lametric:forex-' . strtolower($validator->getPair());

        $launchesFile = $this->predisClient->get($redisKey);
        $ttl          = $this->predisClient->ttl($redisKey);

        if (!$launchesFile || $ttl < 0) {
            $this->data = $this->callApi($validator->getPair(), $validator);

            // save to redis
            $this->predisClient->set($redisKey, json_encode($this->data));
            $this->predisClient->expire($redisKey, 60 * 5);
        } else {
            $this->data = json_decode($launchesFile, true);
        }
    }

    /**
     * @param string    $pair
     * @param Validator $validator
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function callApi(string $pair, Validator $validator): array
    {
        $endpoint = $this->generateApiUrl($pair);

        $resource = $this->guzzleClient->request('GET', $endpoint);
        $data     = json_decode((string) $resource->getBody(), true);

        if ((int) $data['code'] !== 200) {

            // all pairs are USD, so let's try USD => CURRENCY1 => CURRENCY2
            if ($validator->getCurrency1() !== 'USD') {
                $newPairBase    = $this->callApi('USD' . $validator->getCurrency2(), $validator);
                $newPairCompare = $this->callApi('USD' . $validator->getCurrency1(), $validator);

                return [
                    'price' => round($newPairBase['price'] / $newPairCompare['price'], 2),
                ];
            }

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
