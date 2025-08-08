<?php

declare(strict_types=1);

namespace App\Traits;

trait HasName
{
    public function getName(): string
    {
        return $this->name;
    }
}
