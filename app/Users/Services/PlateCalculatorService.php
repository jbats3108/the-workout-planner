<?php

namespace App\Users\Services;

/**
 * Resolve even-loaded barbell plate stacks from a user's plate inventory.
 *
 * Plate `count` is the total number of plates of that denomination (both sides).
 * Loading is always mirrored: each side gets the same plates.
 */
class PlateCalculatorService
{
    /**
     * @param  list<array{denomination_g: int, count: int, colour?: ?string}>  $plates
     * @param  array{per_side: list<array{denomination_g: int, count: int}>}|null  $previousLoad
     * @return array{
     *     exact: bool,
     *     total_g: int,
     *     bar_g: int,
     *     per_side: list<array{denomination_g: int, count: int, colour: ?string}>,
     *     delta_g: int
     * }|null
     */
    public function nearest(int $targetG, int $barG, array $plates, ?array $previousLoad = null): ?array
    {
        if ($barG < 0 || $targetG < 0) {
            return null;
        }

        if ($targetG <= $barG) {
            return [
                'exact' => $targetG === $barG,
                'total_g' => $barG,
                'bar_g' => $barG,
                'per_side' => [],
                'delta_g' => $barG - $targetG,
            ];
        }

        $inventory = $this->normalizeInventory($plates);
        $achievableSides = $this->achievableSideLoads($inventory, $previousLoad);

        if ($achievableSides === []) {
            return [
                'exact' => false,
                'total_g' => $barG,
                'bar_g' => $barG,
                'per_side' => [],
                'delta_g' => $barG - $targetG,
            ];
        }

        $desiredSide = intdiv($targetG - $barG, 2);
        $bestSide = null;
        $bestDelta = null;
        $bestCombo = null;
        $denominations = array_keys($inventory);

        foreach (array_keys($achievableSides) as $sideG) {
            $totalG = $barG + (2 * $sideG);
            $delta = abs($totalG - $targetG);
            $sideDistance = abs($sideG - $desiredSide);
            $bestSideDistance = $bestSide === null ? null : abs($bestSide - $desiredSide);
            $combo = $achievableSides[$sideG];
            $comboComparison = $bestCombo === null
                ? -1
                : $this->compareSideLoads($combo, $bestCombo, $denominations, $previousLoad);

            if ($bestDelta === null
                || $delta < $bestDelta
                || ($delta === $bestDelta
                    && ($sideDistance < ($bestSideDistance ?? PHP_INT_MAX)
                        || ($sideDistance === $bestSideDistance && $comboComparison < 0)))
            ) {
                $bestDelta = $delta;
                $bestSide = $sideG;
                $bestCombo = $combo;
            }
        }

        if ($bestSide === null || $bestCombo === null) {
            return null;
        }

        $perSide = [];
        foreach ($bestCombo as $denominationG => $count) {
            if ($count <= 0) {
                continue;
            }
            $perSide[] = [
                'denomination_g' => $denominationG,
                'count' => $count,
                'colour' => $inventory[$denominationG]['colour'] ?? null,
            ];
        }

        usort($perSide, static fn (array $a, array $b): int => $b['denomination_g'] <=> $a['denomination_g']);

        $totalG = $barG + (2 * $bestSide);

        return [
            'exact' => $totalG === $targetG,
            'total_g' => $totalG,
            'bar_g' => $barG,
            'per_side' => $perSide,
            'delta_g' => $totalG - $targetG,
        ];
    }

    /**
     * @param  list<array{denomination_g: int, count: int, colour?: ?string}>  $plates
     * @return array<int, array{count: int, colour: ?string}>
     */
    private function normalizeInventory(array $plates): array
    {
        $inventory = [];
        foreach ($plates as $plate) {
            $denom = (int) ($plate['denomination_g'] ?? 0);
            $count = (int) ($plate['count'] ?? 0);
            if ($denom <= 0 || $count <= 0) {
                continue;
            }
            $perSideMax = intdiv($count, 2);
            if ($perSideMax <= 0) {
                continue;
            }
            $inventory[$denom] = [
                'count' => $perSideMax,
                'colour' => isset($plate['colour']) ? (is_string($plate['colour']) ? $plate['colour'] : null) : null,
            ];
        }

        krsort($inventory);

        return $inventory;
    }

    /**
     * @param  array<int, array{count: int, colour: ?string}>  $inventory
     * @return array<int, array<int, int>> map of side grams => denomination => count on that side
     */
    private function achievableSideLoads(array $inventory, ?array $previousLoad): array
    {
        /** @var array<int, array<int, int>> $reachable */
        $reachable = [0 => []];
        $denominations = array_keys($inventory);

        foreach ($inventory as $denominationG => $meta) {
            $next = $reachable;
            for ($n = 1; $n <= $meta['count']; $n++) {
                $add = $n * $denominationG;
                foreach ($reachable as $sum => $combo) {
                    $newSum = $sum + $add;
                    $candidate = $combo;
                    $candidate[$denominationG] = $n;
                    if (isset($next[$newSum])
                        && $this->compareSideLoads($candidate, $next[$newSum], $denominations, $previousLoad) >= 0
                    ) {
                        continue;
                    }
                    $next[$newSum] = $candidate;
                }
            }
            $reachable = $next;
        }

        return $reachable;
    }

    /**
     * @param  array<int, int>  $candidate
     * @param  array<int, int>  $existing
     * @param  list<int>  $denominations
     * @param  array{per_side: list<array{denomination_g: int, count: int}>}|null  $previousLoad
     */
    private function compareSideLoads(array $candidate, array $existing, array $denominations, ?array $previousLoad): int
    {
        if ($previousLoad !== null) {
            $candidateChanges = $this->plateChangeCount($candidate, $previousLoad);
            $existingChanges = $this->plateChangeCount($existing, $previousLoad);

            if ($candidateChanges !== $existingChanges) {
                return $candidateChanges < $existingChanges ? -1 : 1;
            }
        }

        foreach ($denominations as $denominationG) {
            $candidateCount = $candidate[$denominationG] ?? 0;
            $existingCount = $existing[$denominationG] ?? 0;

            if ($candidateCount !== $existingCount) {
                return $candidateCount > $existingCount ? -1 : 1;
            }
        }

        return 0;
    }

    /**
     * @param  array<int, int>  $sideLoad
     * @param  array{per_side: list<array{denomination_g: int, count: int}>}  $previousLoad
     */
    private function plateChangeCount(array $sideLoad, array $previousLoad): int
    {
        $previousCounts = [];
        foreach ($previousLoad['per_side'] as $step) {
            $previousCounts[$step['denomination_g']] = $step['count'];
        }

        $denominations = array_unique(array_merge(array_keys($sideLoad), array_keys($previousCounts)));

        return array_sum(array_map(
            static fn (int $denominationG): int => abs(
                ($sideLoad[$denominationG] ?? 0) - ($previousCounts[$denominationG] ?? 0)
            ),
            $denominations,
        ));
    }

    /**
     * Sensible home-gym defaults (kg inventory stored as grams).
     *
     * @return array{name: string, bars: list<array{name: string, weight_g: int, is_default: bool}>, plates: list<array{denomination_g: int, count: int, colour: ?string}>}
     */
    public static function defaultProfilePayload(): array
    {
        return [
            'name' => 'Home gym',
            'bars' => [
                ['name' => 'Olympic', 'weight_g' => 20000, 'is_default' => true],
            ],
            'plates' => [
                ['denomination_g' => 25000, 'count' => 2, 'colour' => 'red'],
                ['denomination_g' => 20000, 'count' => 2, 'colour' => 'blue'],
                ['denomination_g' => 15000, 'count' => 2, 'colour' => 'yellow'],
                ['denomination_g' => 10000, 'count' => 4, 'colour' => 'green'],
                ['denomination_g' => 5000, 'count' => 4, 'colour' => 'white'],
                ['denomination_g' => 2500, 'count' => 4, 'colour' => 'black'],
                ['denomination_g' => 1250, 'count' => 4, 'colour' => 'silver'],
            ],
        ];
    }
}
