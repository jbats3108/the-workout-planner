<?php

namespace App\DataTransferObjects\Casts;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class SlugToModelCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?Model
    {
        $modelName = $property->type->type->name;

        return $modelName::lookup($value);
    }
}
