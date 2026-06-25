<?php

namespace VGA\Classes;

class Cache_Tags_Collector {

    private static ?self $instance = null;
    private array $tags = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function add(string|array $tags): void
    {
        foreach ((array) $tags as $tag) {
            $this->tags[] = $tag;
        }
    }

    public function all(): array
    {
        return array_values(array_unique($this->tags));
    }
}