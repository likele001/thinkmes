<?php
declare(strict_types=1);

namespace app\common\lib;

class Hook
{
    protected static array $listeners = [];

    public static function listen(string $name, callable $callback): void
    {
        if (!isset(self::$listeners[$name])) {
            self::$listeners[$name] = [];
        }
        self::$listeners[$name][] = $callback;
    }

    /**
     * @param array $params
     * @return array 各回调返回值
     */
    public static function trigger(string $name, array $params = []): array
    {
        $results = [];
        if (!isset(self::$listeners[$name])) {
            return $results;
        }
        foreach (self::$listeners[$name] as $cb) {
            $results[] = $cb(...$params);
        }
        return $results;
    }

    public static function remove(string $name): void
    {
        unset(self::$listeners[$name]);
    }
}
