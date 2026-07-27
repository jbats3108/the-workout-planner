export function filterByQuery<T>(
    items: T[],
    query: string,
    fields: (item: T) => string[],
): T[] {
    const q = query.trim().toLowerCase();
    if (!q) {
        return items;
    }
    return items.filter((item) =>
        fields(item).some((field) => field.toLowerCase().includes(q)),
    );
}
