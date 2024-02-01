<?php

namespace SaintSample\LaravelBeelineSms\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BeelineSmsMessageContract
{
    public function fillFromMappedData(array $attributes): Model;

    public function mapData(array $attributes): array;

    public function getExternalId(): ?string;

    public function getStatusField(): ?string;
}