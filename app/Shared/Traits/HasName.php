<?php

declare(strict_types=1);

namespace App\Shared\Traits;

trait HasName
{
    public function getName(): string
    {
        return $this->name;
    }
}
