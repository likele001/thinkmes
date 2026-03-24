<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Event as ThinkEvent;

class EventDispatcher
{
    protected static array $hooks = [];

    public static function listen(string $event, $listener, int $priority = 10, bool $isHook = false): void
    {
        if ($isHook) {
            Hook::listen($event, $listener, $priority);
            self::$hooks[$event][] = $listener;
        } else {
            ThinkEvent::listen($event, $listener);
        }
    }

    public static function listenHook(string $name, callable $callback, int $priority = 10): void
    {
        Hook::listen($name, $callback, $priority);
        self::$hooks[$name][] = $callback;
    }

    public static function listenEvent(string $event, $listener): void
    {
        ThinkEvent::listen($event, $listener);
    }

    public static function trigger(string $event, $data = null, array $params = [])
    {
        $isHookEvent = in_array($event, config('addon.hooks', []));

        if ($isHookEvent) {
            $hookParams = array_merge([$data], $params);
            return Hook::trigger($event, $hookParams);
        } else {
            return ThinkEvent::trigger($event, $data, $params);
        }
    }

    public static function dispatch(string $event, $payload = [])
    {
        return ThinkEvent::dispatch($event, $payload);
    }

    public static function until(string $event, $data = null)
    {
        return ThinkEvent::until($event, $data);
    }

    public static function triggerUntil(string $name, array $params = [], callable $callback = null)
    {
        return Hook::triggerUntil($name, $params, $callback);
    }

    public static function filter(string $name, $value, array $params = [])
    {
        return Hook::filter($name, $value, $params);
    }

    public static function register(array $events): void
    {
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (is_array($listener)) {
                    $priority = $listener['priority'] ?? 10;
                    $isHook = $listener['hook'] ?? false;
                    self::listen($event, $listener['listener'], $priority, $isHook);
                } else {
                    self::listen($event, $listener);
                }
            }
        }
    }

    public static function remove(string $event, $listener = null): void
    {
        $isHookEvent = in_array($event, config('addon.hooks', []));

        if ($isHookEvent) {
            Hook::remove($event, $listener);
        } else {
            ThinkEvent::remove($event, $listener);
        }
    }

    public static function has(string $event, $listener = null): bool
    {
        $isHookEvent = in_array($event, config('addon.hooks', []));

        if ($isHookEvent) {
            return Hook::has($event, $listener);
        } else {
            return ThinkEvent::has($event, $listener);
        }
    }

    public static function getListeners(string $event = null): array
    {
        $isHookEvent = in_array($event, config('addon.hooks', []));

        if ($isHookEvent) {
            return Hook::getListeners($event);
        } else {
            $events = app()->make('events');
            return $event ? $events->getListeners($event) : [];
        }
    }

    public static function clear(): void
    {
        Hook::clear();
        self::$hooks = [];
    }

    public static function getHookEvents(): array
    {
        return config('addon.hooks', []);
    }
}
