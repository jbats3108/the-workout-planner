/** Trim kg for display (supports 2dp loads like 28.75). */
export function formatKg(kg: number | null | undefined): string {
    if (kg == null) {
        return '—';
    }

    return String(parseFloat(kg.toFixed(2)));
}
