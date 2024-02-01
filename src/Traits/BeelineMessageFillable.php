<?php

namespace SaintSample\LaravelBeelineSms\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

/**
 * @mixin Model
 */
trait BeelineMessageFillable
{
    protected array $providerAttributeMapping = [
        'sms_id' => 'external_message_id',
        'id' => 'external_message_id',
        'stc_code' => 'status',
        'target' => 'target',
        'phone' => 'target',
        'text' => 'message',
        'message' => 'message',
        'close_time' => 'sent_at',
    ];

    public function fillFromMappedData(array $attributes): Model
    {
        $attributes = $this->mapData($attributes);

        if (!empty($attributes['message'])) {
            $attributes['message'] = Crypt::encryptString($attributes['message']);
        }

        if (!empty($attributes['sent_at'])) {
            $attributes['sent_at'] = Carbon::createFromFormat('d.m.y H:i:s', $attributes['sent_at'])
                ->format('Y-m-d H:i:s');
        }

        return $this->newModelQuery()
            ->updateOrCreate(
                [
                    'external_message_id' => $attributes['external_message_id'] ?? null
                ],
                $attributes
            );
    }

    public function mapData(array $attributes): array
    {
        //TODO needs refactoring
        $result = [];

        /*        foreach ($attributes as $k => $v) {
                    $key = $this->providerAttributeMapping[$k] ?? $k;

                    $result[$key] = $v;
                }*/

        foreach ($this->providerAttributeMapping as $k => $v) {
            if (!empty($attributes[$k])) {
                $result[$v] = $attributes[$k];
            }
        }

        return $result;
    }

    public function getExternalId(): ?string
    {
        return $this->external_message_id;
    }

    public function getStatusField(): ?string
    {
        return 'status';
    }
}