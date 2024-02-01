<?php

namespace SaintSample\LaravelBeelineSms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use SaintSample\LaravelBeelineSms\Contracts\BeelineSmsMessageContract;
use Throwable;

//TODO refactoring
/**
 * Statuses:
 * queued	Сообщение находится в очереди отправки и еще не было передано оператору
 * accepted	Сообщение уже передано оператору
 * delivered	Сообщение успешно доставлено абоненту
 * rejected	Сообщение отклонено оператором
 * undeliverable	Сообщение невозможно доставить из-за недоступности абонента
 * error	Ошибка отправки. Сообщение не было отправлено абоненту
 * expired	Истекло время ожидания финального статуса
 * unknown	Статус сообщения неизвестен
 * aborted	Сообщение отменено пользователем
 *
 */
final class BeelineA2PSMS implements BeelineSmsDriverContract
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var PendingRequest
     */
    private \Illuminate\Http\Client\PendingRequest $client;

    /**
     * @var BeelineSmsMessageContract|mixed
     */
    private string $model;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->config = App::make('config')->get('laravel_beeline_sms');

        $this->model = $this->config['messages']['model'];

        $this->client = Http::withHeaders($this->prepareHeaders())
            ->baseUrl($this->config['api_host'])
            ->throw();

        $this->logger = Log::channel($this->config['log_channel']);
    }

    private function prepareHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json; charset=utf-8'
        ];

        if ($this->config['gzip']) {
            $headers['Content-Encoding'] = 'gzip';
        }

        return $headers;
    }

    private function prepareRequestBody($params): array
    {
        $body = [
            'user' => $this->config['login'],
            'pass' => $this->config['password'],
        ];

        if (!$this->config['gzip']) {
            $body['gzip'] = 'none';
        }

        if (!$this->config['comment']) {
            $body['comment'] = $this->config['comment'];
        }

        return array_merge($body, $params);
    }

    public function send(array $targets, string $message): array
    {
        if (empty($targets)) {
            throw new \Exception();
        }

        $response = $this->handleResponse($this->client->post(
            url: '',
            data: $this->prepareRequestBody([
                'action' => 'post_sms',
                'message' => $message,
                'target' => implode(',', $targets),
                'sender' => $this->config['sender']
            ])
        ));


        if ($this->config['message_registry']) {
            /**
             * @var BeelineSmsMessageContract $message
             */
            $message = App::make($this->model);



            foreach ($response->json()['actions'] as $action) {

                $status = current($this->statusById($action['id'])['actions']);//array_shift($this->statusById($action['id'])['actions']);

                $message->fillFromMappedData(array_merge($action, $status));
            }
        }

        return $response->json();
    }

    public function statusById(string $messageId): array
    {
        $response = $this->handleResponse($this->client->post(
            url: '',
            data: $this->prepareRequestBody([
                'action' => 'status',
                'sms_id' => $messageId,
            ])
        ));

        return $response->json();
    }

    /**
     * @param \DateTimeInterface|string $from
     * @param \DateTimeInterface|string $to
     * @throws \Exception
     */
    public function statusByDate(\DateTimeInterface|string $from, \DateTimeInterface|string $to): array
    {
        if (!$from instanceof \DateTimeInterface) {
            $from = new \DateTime($from);
        }

        if (!$to instanceof \DateTimeInterface) {
            $to = new \DateTime($to);
        }

        $response = $this->handleResponse($this->client->post(
            url: '',
            data: $this->prepareRequestBody([
                    'action' => 'status',
                    'date_from' => $from->format('d.m.Y H:i:s'),
                    'date_to' => $to->format('d.m.Y H:i:s'),
                ])
        ));

        return $response->json();
    }

    /**
     * @param Response $response
     * @return Response
     * @throws RequestException
     * @throws Throwable
     */
    private function handleResponse(Response $response): Response
    {
        return $this->handleError($response);
    }

    /**
     * @throws RequestException|Throwable
     */
    private function handleError(Response $response): Response
    {
        try {
            $result = $response->json();

            if (!empty($result['error'])) {
                $this->logger->error(sprintf('Code "%s": %s.', $result['error']['code'], $result['error']['message']));
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTrace()]);

            throw $e;
        }

        return $response;
    }
}