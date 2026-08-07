export type PlateInventoryItem = {
    denomination_g: number;
    count: number;
    colour?: string | null;
};

export type PlateLoadStep = {
    denomination_g: number;
    count: number;
    colour: string | null;
};

export type PlateStackStep = Pick<PlateLoadStep, 'denomination_g' | 'count'>;

export type PlateStack = {
    bar_g: number;
    per_side: PlateStackStep[];
};

export type PlateLoadResult = {
    exact: boolean;
    total_g: number;
    bar_g: number;
    per_side: PlateLoadStep[];
    delta_g: number;
};

type Inventory = Record<number, { count: number; colour: string | null }>;

function normalizeInventory(plates: PlateInventoryItem[]): Inventory {
    const inventory: Inventory = {};
    for (const plate of plates) {
        const denom = plate.denomination_g;
        const perSideMax = Math.floor(plate.count / 2);
        if (denom <= 0 || perSideMax <= 0) continue;
        inventory[denom] = {
            count: perSideMax,
            colour: plate.colour ?? null,
        };
    }
    return inventory;
}

type SideLoad = Record<number, number>;

function plateChangeCount(sideLoad: SideLoad, previousLoad: PlateLoadResult): number {
    const previousCounts = previousLoad.per_side.reduce<Record<number, number>>((counts, step) => {
        counts[step.denomination_g] = step.count;
        return counts;
    }, {});
    const denominations = new Set([...Object.keys(sideLoad).map(Number), ...Object.keys(previousCounts).map(Number)]);

    return [...denominations].reduce(
        (changes, denominationG) => changes + Math.abs((sideLoad[denominationG] ?? 0) - (previousCounts[denominationG] ?? 0)),
        0,
    );
}

/**
 * Compare two stacks for the same side load.
 *
 * Lower plate movement wins when a previous stack exists. Otherwise, prefer
 * the largest available denomination first.
 */
function compareSideLoads(candidate: SideLoad, existing: SideLoad, denominations: number[], previousLoad: PlateLoadResult | null): number {
    if (previousLoad !== null) {
        const candidateChanges = plateChangeCount(candidate, previousLoad);
        const existingChanges = plateChangeCount(existing, previousLoad);

        if (candidateChanges !== existingChanges) {
            return candidateChanges < existingChanges ? -1 : 1;
        }
    }

    for (const denominationG of denominations) {
        const candidateCount = candidate[denominationG] ?? 0;
        const existingCount = existing[denominationG] ?? 0;

        if (candidateCount !== existingCount) {
            return candidateCount > existingCount ? -1 : 1;
        }
    }

    return 0;
}

/** Map of side grams → denomination → count on that side */
function achievableSideLoads(inventory: Inventory, previousLoad: PlateLoadResult | null): Record<number, SideLoad> {
    const denoms = Object.keys(inventory)
        .map(Number)
        .sort((a, b) => b - a);
    let reachable: Record<number, SideLoad> = { 0: {} };

    for (const denominationG of denoms) {
        const meta = inventory[denominationG];
        const next: Record<number, Record<number, number>> = { ...reachable };
        for (let n = 1; n <= meta.count; n++) {
            const add = n * denominationG;
            for (const [sumStr, combo] of Object.entries(reachable)) {
                const sum = Number(sumStr);
                const newSum = sum + add;
                const candidate = { ...combo, [denominationG]: n };
                const existing = next[newSum];

                if (existing === undefined || compareSideLoads(candidate, existing, denoms, previousLoad) < 0) {
                    next[newSum] = candidate;
                }
            }
        }
        reachable = next;
    }

    return reachable;
}

export function nearestPlateLoad(
    targetG: number,
    barG: number,
    plates: PlateInventoryItem[],
    previousLoad: PlateLoadResult | null = null,
): PlateLoadResult | null {
    if (barG < 0 || targetG < 0) return null;

    if (targetG <= barG) {
        return {
            exact: targetG === barG,
            total_g: barG,
            bar_g: barG,
            per_side: [],
            delta_g: barG - targetG,
        };
    }

    const inventory = normalizeInventory(plates);
    const achievable = achievableSideLoads(inventory, previousLoad);
    const sides = Object.keys(achievable).map(Number);

    if (sides.length === 0) {
        return {
            exact: false,
            total_g: barG,
            bar_g: barG,
            per_side: [],
            delta_g: barG - targetG,
        };
    }

    const desiredSide = Math.floor((targetG - barG) / 2);
    let bestSide: number | null = null;
    let bestDelta: number | null = null;
    let bestCombo: SideLoad | null = null;
    const denominations = Object.keys(inventory)
        .map(Number)
        .sort((a, b) => b - a);

    for (const sideG of sides) {
        const totalG = barG + 2 * sideG;
        const delta = Math.abs(totalG - targetG);
        const combo = achievable[sideG];
        const sideDistance = Math.abs(sideG - desiredSide);
        const bestSideDistance = bestSide === null ? null : Math.abs(bestSide - desiredSide);
        const comboComparison = bestCombo === null ? -1 : compareSideLoads(combo, bestCombo, denominations, previousLoad);

        if (
            bestDelta === null ||
            delta < bestDelta ||
            (delta === bestDelta &&
                (sideDistance < (bestSideDistance ?? Number.POSITIVE_INFINITY) || (sideDistance === bestSideDistance && comboComparison < 0)))
        ) {
            bestDelta = delta;
            bestSide = sideG;
            bestCombo = combo;
        }
    }

    if (bestSide === null || bestCombo === null) return null;

    const per_side: PlateLoadStep[] = Object.entries(bestCombo)
        .map(([denom, count]) => ({
            denomination_g: Number(denom),
            count: Number(count),
            colour: inventory[Number(denom)]?.colour ?? null,
        }))
        .filter((s) => s.count > 0)
        .sort((a, b) => b.denomination_g - a.denomination_g);

    const total_g = barG + 2 * bestSide;

    return {
        exact: total_g === targetG,
        total_g,
        bar_g: barG,
        per_side,
        delta_g: total_g - targetG,
    };
}

export function loadFromPlateStack(targetG: number, stack: PlateStack, plates: PlateInventoryItem[]): PlateLoadResult | null {
    if (targetG < 0 || stack.bar_g < 0) {
        return null;
    }

    const inventory = normalizeInventory(plates);
    const counts: SideLoad = {};

    for (const step of stack.per_side) {
        const denominationG = step.denomination_g;
        const count = step.count;
        const maxCount = inventory[denominationG]?.count ?? 0;

        if (!Number.isInteger(denominationG) || denominationG <= 0 || !Number.isInteger(count) || count < 0) {
            return null;
        }

        counts[denominationG] = (counts[denominationG] ?? 0) + count;
        if (counts[denominationG] > maxCount) {
            return null;
        }
    }

    const per_side: PlateLoadStep[] = Object.entries(counts)
        .map(([denom, count]) => ({
            denomination_g: Number(denom),
            count,
            colour: inventory[Number(denom)]?.colour ?? null,
        }))
        .filter((step) => step.count > 0)
        .sort((a, b) => b.denomination_g - a.denomination_g);
    const total_g = stack.bar_g + 2 * per_side.reduce((total, step) => total + step.denomination_g * step.count, 0);

    return {
        exact: total_g === targetG,
        total_g,
        bar_g: stack.bar_g,
        per_side,
        delta_g: total_g - targetG,
    };
}

export function plateStackFromLoad(load: PlateLoadResult): PlateStack {
    return {
        bar_g: load.bar_g,
        per_side: load.per_side.map(({ denomination_g, count }) => ({ denomination_g, count })),
    };
}

export function updatePlateCount(
    targetG: number,
    load: PlateLoadResult,
    denominationG: number,
    change: 1 | -1,
    plates: PlateInventoryItem[],
): PlateLoadResult | null {
    const counts = load.per_side.reduce<SideLoad>((result, step) => {
        result[step.denomination_g] = step.count;
        return result;
    }, {});
    const nextCount = (counts[denominationG] ?? 0) + change;

    if (nextCount < 0) {
        return null;
    }

    if (nextCount === 0) {
        delete counts[denominationG];
    } else {
        counts[denominationG] = nextCount;
    }

    return loadFromPlateStack(
        targetG,
        {
            bar_g: load.bar_g,
            per_side: Object.entries(counts).map(([denom, count]) => ({
                denomination_g: Number(denom),
                count,
            })),
        },
        plates,
    );
}

export function defaultBarG(bars: Array<{ weight_g: number; is_default: boolean }>): number | null {
    const preferred = bars.find((b) => b.is_default) ?? bars[0];
    return preferred?.weight_g ?? null;
}

export function gramsToKg(g: number): number {
    return Math.round(g) / 1000;
}

/** Display grams as kg with 0–1 decimal places (e.g. progression bumps). */
export function formatGramsToKg(g: number): string {
    return (g / 1000).toFixed(g % 1000 === 0 ? 0 : 1);
}

/** Barbell plate stacks apply to barbell / E-Z curl bar work only. */
export function usesBarbellPlates(equipment: string | null | undefined): boolean {
    return equipment === 'barbell' || equipment === 'ez_curl_bar';
}
