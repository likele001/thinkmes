<?php
declare(strict_types=1);

namespace app\common\lib;

class Hook
{
    protected static array $listeners = [];
    protected static bool $halt = false;

    public static function listen(string $name, callable $callback, int $priority = 10): void
    {
        if (!isset(self::$listeners[$name])) {
            self::$listeners[$name] = [];
        }
        self::$listeners[$name][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
        self::sortListeners($name);
    }

    protected static function sortListeners(string $name): void
    {
        if (!isset(self::$listeners[$name])) {
            return;
        }
        usort(self::$listeners[$name], function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    public static function trigger(string $name, array $params = []): array
    {
        $results = [];
        self::$halt = false;

        if (!isset(self::$listeners[$name])) {
            return $results;
        }

        foreach (self::$listeners[$name] as $listener) {
            if (self::$halt) {
                break;
            }

            $result = call_user_func_array($listener['callback'], $params);
            $results[] = $result;

            if ($result instanceof Halt) {
                self::$halt = true;
                $results[] = $result->getData();
                break;
            }
        }

        return $results;
    }

    public static function triggerUntil(string $name, array $params = [], callable $callback = null)
    {
        if (!isset(self::$listeners[$name])) {
            return null;
        }

        self::$halt = false;

        foreach (self::$listeners[$name] as $listener) {
            if (self::$halt) {
                break;
            }

            $result = call_user_func_array($listener['callback'], $params);

            if ($callback && $callback($result)) {
                return $result;
            }

            if ($result instanceof Halt) {
                self::$halt = true;
                return $result->getData();
            }
        }

        return null;
    }

    public static function filter(string $name, $value, array $params = [])
    {
        if (!isset(self::$listeners[$name])) {
            return $value;
        }

        self::$halt = false;

        foreach (self::$listeners[$name] as $listener) {
            if (self::$halt) {
                break;
            }

            $result = call_user_func_array($listener['callback'], array_merge([$value], $params));

            if ($result instanceof Halt) {
                $value = $result->getData();
                self::$halt = true;
                break;
            }

            $value = $result;
        }

        return $value;
    }

    public static function halt($data = null): Halt
    {
        return new Halt($data);
    }

    public static function remove(string $name, callable $callback = null): void
    {
        if (!isset(self::$listeners[$name])) {
            return;
        }

        if ($callback === null) {
            unset(self::$listeners[$name]);
            return;
        }

        foreach (self::$listeners[$name] as $key => $listener) {
            if ($listener['callback'] === $callback) {
                unset(self::$listeners[$name][$key]);
                break;
            }
        }
    }

    public static function has(string $name, callable $callback = null): bool
    {
        if (!isset(self::$listeners[$name])) {
            return false;
        }

        if ($callback === null) {
            return !empty(self::$listeners[$name]);
        }

        foreach (self::$listeners[$name] as $listener) {
            if ($listener['callback'] === $callback) {
                return true;
            }
        }

        return false;
    }

    public static function clear(): void
    {
        self::$listeners = [];
        self::$halt = false;
    }

    public static function getListeners(string $name = null): array
    {
        if ($name === null) {
            return self::$listeners;
        }

        return self::$listeners[$name] ?? [];
    }
}

class Halt
{
    protected $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }
}
