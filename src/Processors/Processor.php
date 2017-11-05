<?php

namespace Bcismariu\Laravel\Payments\Processors;

class Processor
{

    protected static $drivers = [
        'konnektive'    => Konnektive::class,
    ];

    public static function make($settings)
    {
        $driver = $settings['driver'];
        if (!array_key_exists($driver, self::$drivers)) {
            throw new \Exception("Unknown driver $driver!");
        }

        $class = self::$drivers[$driver];
        return new $class($settings);
    }
}