<?php

declare(strict_types=1);

namespace Platform\Components\MessageBroker\Config;

use ArrayObject;

/**
 * MessageBrokerConfig class
 *
 * This class handles configuration for connection to a message broker service (NATS,...).
 * It extends ArrayObject to allow accessing configuration values as object properties.
 *
 * @property string $host The hostname or IP address
 * @property int $port The port number to connect to
 * @property int $timeout Maximum time to wait when attempting to establish a connection (in seconds)
 * @property bool $reconnect Enable/disable automatic reconnection if the connection is lost
 */
final class MessageBrokerConfig extends ArrayObject
{
    private static array $cachedAttributes = [];

    public function __construct(array $input = [])
    {
        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS);
        self::$cachedAttributes = array_merge(self::$cachedAttributes, $input);
    }

    /**
     * Magic getter for accessing attributes as properties.
     */
    public function __get(string $name)
    {
        // Check cache first
        return self::$cachedAttributes[$name] ?? $this[$name] ?? null;
    }

    /**
     * Magic setter for setting attributes as properties.
     */
    public function __set(string $name, $value): void
    {
        $this[$name] = $value;
        self::$cachedAttributes[$name] = $value;
    }

    /**
     * Magic isset for checking property existence.
     */
    public function __isset(string $name): bool
    {
        return isset(self::$cachedAttributes[$name]) || isset($this[$name]);
    }

    /**
     * Magic unset for removing properties.
     */
    public function __unset(string $name): void
    {
        unset(self::$cachedAttributes[$name], $this[$name]);
    }

    /**
     * Get all cached attributes.
     */
    public static function getCachedAttributes(): array
    {
        return self::$cachedAttributes;
    }
}
