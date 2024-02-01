<?php

namespace SaintSample\LaravelBeelineSms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SaintSample\LaravelBeelineSms\Contracts\BeelineSmsMessageContract;
use SaintSample\LaravelBeelineSms\Traits\BeelineMessageFillable;

class SmsMessage extends Model implements BeelineSmsMessageContract
{
    use BeelineMessageFillable;

    protected $guarded = [];

    public function getTable()
    {
        return config('laravel_beeline_sms.messages.table', parent::getTable());
    }

    public function getFillable(): array
    {
        return array_values($this->providerAttributeMapping);
    }
}