<?php

namespace SaintSample\LaravelBeelineSms;

interface BeelineSmsDriverContract
{
    /**
     * @param array<string> $targets
     * @param string $message
     * @return array
     */
    public function send(array $targets, string $message): array;

    public function statusById(string $messageId): array;

    public function statusByDate(\DateTimeInterface|string $from, \DateTimeInterface|string $to): array;
}