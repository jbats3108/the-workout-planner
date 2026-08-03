export function formatDate(iso: string): string {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
}
