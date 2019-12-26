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
    public function asJson($data = [])
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function error($value = 'INTERNAL ERROR')
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => $value,
                    'icon'  => 'null'
                ]
            ]
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
                'text'  => $validator->getPair(),
            ],
            [
                'icon'  => null,
                'index' => 1,
                'text'  => $data['price'],
            ],
        ];

        if ($validator->showChange()) {
            $frames[] = [
                'icon'  => null,
                'index' => 2,
                'text'  => $data['change'],
            ];
        }

        return $this->asJson([
            'frames' => $frames
        ]);
    }
}
