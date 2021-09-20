<?php

use Forex\{Api, Response, Validator};

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Predis\Client as PredisClient;

require_once __DIR__ . '/../vendor/autoload.php';
$config = include_once __DIR__ . '/../config/parameters.php';

Sentry\init(['dsn' => $config['sentry_key']]);

$response = new Response();

try {
    $validator = new Validator($_GET);
    $validator->check();

    $api = new Api(new GuzzleClient(), new PredisClient());
    $api->fetchData($validator);

    echo $response->data($api->getData(), $validator);

} catch (Exception $exception) {
    echo $response->error($exception->getMessage());
} catch (GuzzleException $exception) {
    echo $response->error();
}
