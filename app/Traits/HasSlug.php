<?php

declare(strict_types=1);

namespace App\Traits;

trait HasSlug
{
    public function getSlug(): string
    {
        return $this->slug;
    }

    public static function lookup(string $slug): ?self
    {
        return static::firstWhere('slug', $slug);
    }
}
