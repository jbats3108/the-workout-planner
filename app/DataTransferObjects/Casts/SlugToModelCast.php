<?php

namespace App\DataTransferObjects\Casts;

use App\Models\Exercise;
use App\Models\Routine;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Types\NamedType;

class SlugToModelCast implements Cast
{
    /**
     * @param  CreationContext<Data>  $context
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?Model
    {
        /** @var NamedType $type */
        $type = $property->type->type;
        $modelName = $type->name;

        /** @var string $value */
        return match ($modelName) {
            Exercise::class => Exercise::lookup($value),
            Routine::class => Routine::lookup($value),
            default => null,
        };
    }
}
