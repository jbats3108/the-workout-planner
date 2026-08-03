export function formatDeloadSummary(weightFactor: number, repsFactor: number, everyN: number): string {
    const cadence = everyN > 0 ? `every ${everyN}` : 'no suggest';

    return `${weightFactor}× weight · ${repsFactor}× reps · ${cadence}`;
}
