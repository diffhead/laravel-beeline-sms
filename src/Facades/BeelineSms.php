<?php

namespace SaintSample\LaravelBeelineSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class BeelineSms
 * @method static array send(array $targets, string $message)
 * @method static array statusById(string $messageId)
 * @method static array statusByDate(\DateTimeInterface|string $from, \DateTimeInterface|string $to)
 */
class BeelineSms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-beeline-sms';
    }
}