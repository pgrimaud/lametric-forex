<?php

use Forex\{Api, Response, Validator};

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Predis\Client as PredisClient;

require_once __DIR__ . '/../vendor/autoload.php';

$response = new Response();

try {

    $validator = new Validator($_GET);
    $validator->check();

    $credentials = include_once __DIR__ . '/../config/credentials.php';

    $api = new Api(new GuzzleClient(), new PredisClient(), $credentials['key']);
    $api->fetchData($validator->getPair());

    echo $response->data($api->getData(), $validator);

} catch (Exception $exception) {
    echo $response->error($exception->getMessage());
} catch (GuzzleException $exception) {
    echo $response->error();
}

