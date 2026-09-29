export type ProgressionStyle = 'straight_sets' | 'progressive_overload';
export type ProgressiveMidBlock = 'ask' | 'auto';

export function formatProgressionSummary(style: ProgressionStyle, midBlock: ProgressiveMidBlock): string {
    if (style === 'straight_sets') {
        return 'Straight Sets';
    }

    return midBlock === 'auto' ? 'Progressive · Auto' : 'Progressive · Ask';
}
