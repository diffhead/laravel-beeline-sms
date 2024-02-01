<?php

namespace SaintSample\LaravelBeelineSms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SaintSample\LaravelBeelineSms\Contracts\BeelineSmsMessageContract;
use SaintSample\LaravelBeelineSms\Facades\BeelineSms;

class UpdateStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected BeelineSmsMessageContract $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BeelineSmsMessageContract $message)
    {
        $this->message = $message;
    }

    //TODO refactoring
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = BeelineSms::statusById($this->message->getExternalId());

        foreach ($response['actions'] as $action) {
            $this->message->fillFromMappedData($action);
        }
    }
}