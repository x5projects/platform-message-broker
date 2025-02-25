<?php

declare(strict_types=1);

namespace Platform\Components\MessageBroker\Service;

use Basis\Nats\Client;
use Basis\Nats\Configuration;
use Platform\Components\MessageBroker\Config\MessageBrokerConfig;

class NatsMessageBroker implements MessageBrokerInterface
{
    private Client $client;
    private bool $connected = false;

    public function __construct(
        private MessageBrokerConfig $config,
    ) {
        $config = new Configuration([
            'host' => (string) $config->host,
            'port' => (int) $config->port,
            'timeout' => (float) $config->timeout,
            'reconnect' => (bool) $config->reconnect,
        ]);

        $config->setDelay(0.001);
        $this->client = new Client($config);
        $this->connect(); // Kết nối ngay khi khởi tạo
    }

    public function connect(): void
    {
        if ($this->connected) {
            return;
        }
        try {
            $this->client->ping();
            $this->connected = true;
        } catch (\Exception $e) {
            $this->connected = false;
            throw new \RuntimeException("Failed to connect to NATS ({$this->config->host}:{$this->config->port}): " . $e->getMessage());
        }
    }

    public function publish(string $subject, array $message): void
    {
        $this->connect(); // Đảm bảo kết nối trước khi publish
        $this->client->publish($subject, json_encode($message));
    }

    public function subscribe(string $subject, callable $callback): void
    {
        $this->connect();
        $this->client->subscribe($subject, function ($message) use ($callback) {
            $callback($message->payload);
        });
        $this->client->process();
    }

    public function disconnect(): void
    {
        $this->client->disconnect();
        $this->connected = false;
    }
}
