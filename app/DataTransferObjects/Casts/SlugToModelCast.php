<?php

namespace App\DataTransferObjects\Casts;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class SlugToModelCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?Model
    {
        $arguments = collect($property->attributes->first(WithCast::class)->arguments);

        $modelName = $arguments->filter(function ($argument) {
            if (is_string($argument)) {
                return class_exists($argument) && array_key_exists(HasSlug::class, class_uses($argument));
            }

            return false;
        })->first();

        return $modelName::lookup($value);
    }
}
