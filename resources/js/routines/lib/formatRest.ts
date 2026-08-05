export function formatRest(seconds: number): string {
    if (seconds < 60) {
        return `${seconds}s`;
    }
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return s ? `${m}m ${s}s` : `${m}m`;
}

/** Cleared number inputs become '' / NaN via v-model.number — treat as 0 rest. */
export function normalizeRestSeconds(value: unknown): number {
    if (typeof value === 'number' && Number.isFinite(value) && value >= 0) {
        return Math.min(3600, Math.floor(value));
    }
    if (typeof value === 'string' && value.trim() !== '') {
        const parsed = Number(value);
        if (Number.isFinite(parsed) && parsed >= 0) {
            return Math.min(3600, Math.floor(parsed));
        }
    }
    return 0;
}
