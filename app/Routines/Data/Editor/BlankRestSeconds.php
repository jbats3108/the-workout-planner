<?php

namespace App\Routines\Data\Editor;

/** Coerce cleared rest inputs to 0 before Spatie validates nested payloads. */
final class BlankRestSeconds
{
    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    public static function inGroup(array $group): array
    {
        if (array_key_exists('rest_seconds', $group) && ($group['rest_seconds'] === null || $group['rest_seconds'] === '')) {
            $group['rest_seconds'] = 0;
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public static function inBlock(array $block): array
    {
        foreach (['working', 'warm_up'] as $key) {
            if (isset($block[$key]) && is_array($block[$key])) {
                $block[$key] = self::inGroup($block[$key]);
            }
        }

        return $block;
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    public static function inRoutinePayload(array $properties): array
    {
        if (! isset($properties['blocks']) || ! is_array($properties['blocks'])) {
            return $properties;
        }

        foreach ($properties['blocks'] as $index => $block) {
            if (is_array($block)) {
                $properties['blocks'][$index] = self::inBlock($block);
            }
        }

        return $properties;
    }
}
