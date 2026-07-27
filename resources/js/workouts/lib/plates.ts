import {
    defaultBarG,
    gramsToKg,
    nearestPlateLoad,
    usesBarbellPlates,
    type PlateLoadResult,
} from '@/lib/plateCalculator';
import type { PlateProfile } from '@/settings/types';

export function resolvePlateLoad(
    weightKg: number | null | undefined,
    equipment: string | null,
    plateProfile: PlateProfile,
): PlateLoadResult | null {
    if (weightKg == null || Number.isNaN(weightKg) || !usesBarbellPlates(equipment)) {
        return null;
    }
    const barG = defaultBarG(plateProfile.bars);
    if (barG === null) {
        return null;
    }
    return nearestPlateLoad(Math.round(weightKg * 1000), barG, plateProfile.plates);
}

export function formatPlateStackLabel(load: PlateLoadResult, weightUnit: string): string {
    if (!load.per_side.length) {
        return `${gramsToKg(load.bar_g)}${weightUnit} bar only`;
    }
    const plates = load.per_side.map((s) => `${s.count}×${gramsToKg(s.denomination_g)}`).join(' + ');
    return `${gramsToKg(load.bar_g)} bar + ${plates} / side`;
}

export function formatLoadStack(
    equipment: string | null,
    weightKg: number | null | undefined,
    plateProfile: PlateProfile,
    weightUnit: string,
): string | null {
    const load = resolvePlateLoad(weightKg, equipment, plateProfile);
    if (!load) {
        return null;
    }
    return formatPlateStackLabel(load, weightUnit);
}
