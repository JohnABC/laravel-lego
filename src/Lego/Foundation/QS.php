<?php

namespace JA\Lego\Foundation;

use JA\Lego\Exception\QSNotFoundException;

class QS
{
    const TYPE_QUERY = 'Query';
    const TYPE_STORE = 'Store';
    const TYPES = [self::TYPE_QUERY, self::TYPE_STORE];

    protected $repositories = [
        self::TYPE_QUERY => [
            100 => 0,
            200 => 1,
            300 => 2
        ],
        self::TYPE_STORE => [
            100 => 0,
            200 => 1,
            300 => 2
        ],
    ];

    public function __construct()
    {
        $qs = config('ja-lego.qs', []);
        foreach ($qs as $type => $repositories) {
            foreach ($repositories as $order => $repository) {
                $this->repositories[$type][$order] = $repository;
            }
        }
    }

    public function transform($data, $type)
    {
        if ($data instanceof $type) {
            return $data;
        }

        $repositories = array_filter($this->repositories[$type]);
        ksort($repositories);

        foreach ($repositories as $repository) {
            if ($operator = $repository::parse($data)) {
                return $operator;
            }
        }

        throw new QSNotFoundException("Cannot create {$type} for data");
    }

    public static function transform2Query($data)
    {
        return (new static())->transform($data, static::TYPE_QUERY);
    }

    public static function transform2Store($data)
    {
        return (new static())->transform($data, static::TYPE_STORE);
    }
}