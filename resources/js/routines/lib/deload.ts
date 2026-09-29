export function formatDeloadSummary(everyN: number): string {
    return everyN > 0 ? `every ${everyN}` : 'no suggest';
}
