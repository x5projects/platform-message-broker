<?php

declare(strict_types=1);

namespace Platform\Components\MessageBroker\Service;

interface MessageBrokerInterface
{
    public function connect(): void;

    public function publish(string $subject, array $message): void;

    public function subscribe(string $subject, callable $callback): void;

    public function disconnect(): void;
}
