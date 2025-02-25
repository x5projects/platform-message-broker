<?php

declare(strict_types=1);

namespace Platform\Components\MessageBroker\Http\Controller;

use Platform\Components\MessageBroker\Service\MessageBrokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MessageBrokerController
{
    public function __construct(
        protected MessageBrokerInterface $messageBroker,
        protected ResponseInterface $response,
        protected StreamFactoryInterface $streamFactory
    ) {}

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $message = $data['message'] ?? 'Default message';
        $channel = $data['channel'] ?? 'default.channel';

        try {
            $this->messageBroker->publish($channel, ['message' => $message]);

            $stream = $this->streamFactory->createStream(
                json_encode([
                    'success' => true,
                    'message' => $message
                ])
            );
            return $this->response
                ->withBody($stream)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $stream = $this->streamFactory->createStream(
                json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ])
            );
            return $this->response
                ->withBody($stream)
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
    public function __destruct()
    {
        $this->messageBroker->disconnect();
    }
}
