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

/** Map of side grams → denomination → count on that side */
function achievableSideLoads(inventory: Inventory): Record<number, Record<number, number>> {
    let reachable: Record<number, Record<number, number>> = { 0: {} };

    const denoms = Object.keys(inventory)
        .map(Number)
        .sort((a, b) => b - a);

    for (const denominationG of denoms) {
        const meta = inventory[denominationG];
        const next: Record<number, Record<number, number>> = { ...reachable };
        for (let n = 1; n <= meta.count; n++) {
            const add = n * denominationG;
            for (const [sumStr, combo] of Object.entries(reachable)) {
                const sum = Number(sumStr);
                const newSum = sum + add;
                if (next[newSum] !== undefined) continue;
                next[newSum] = { ...combo, [denominationG]: n };
            }
        }
        reachable = next;
    }

    return reachable;
}

export function nearestPlateLoad(targetG: number, barG: number, plates: PlateInventoryItem[]): PlateLoadResult | null {
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
    const achievable = achievableSideLoads(inventory);
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

    for (const sideG of sides) {
        const totalG = barG + 2 * sideG;
        const delta = Math.abs(totalG - targetG);
        if (
            bestDelta === null ||
            delta < bestDelta ||
            (delta === bestDelta && Math.abs(sideG - desiredSide) < Math.abs((bestSide ?? 0) - desiredSide))
        ) {
            bestDelta = delta;
            bestSide = sideG;
        }
    }

    if (bestSide === null) return null;

    const combo = achievable[bestSide];
    const per_side: PlateLoadStep[] = Object.entries(combo)
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
