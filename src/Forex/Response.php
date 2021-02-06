<?php

declare(strict_types=1);

namespace Forex;

class Response
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function asJson(array $data = []): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function error($value = 'INTERNAL ERROR'): string
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => $value,
                    'icon'  => 'null',
                ],
            ],
        ]);
    }

    /**
     * @param array $data
     * @param Validator $validator
     *
     * @return string
     */
    public function data(array $data, Validator $validator): string
    {
        $frames = [
            [
                'icon'  => null,
                'index' => 0,
                'text'  => $validator->getPairFormatted(),
            ],
            [
                'icon'  => null,
                'index' => 1,
                'text'  => $data['price'],
            ],
        ];

        return $this->asJson([
            'frames' => $frames,
        ]);
    }
}
