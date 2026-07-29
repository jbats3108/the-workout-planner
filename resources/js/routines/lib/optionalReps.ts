/** Parse an optional reps field; empty string → null (inherit user default). */
export function parseOptionalReps(raw: string): number | null {
    if (raw === '') {
        return null;
    }

    const value = Number(raw);

    return Number.isFinite(value) ? value : null;
}

export function optionalRepsPlaceholder(userDefault: number | null | undefined): string {
    return userDefault != null ? String(userDefault) : 'default';
}
