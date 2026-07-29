export type WarmUpStep = {
    percent: number;
    reps: number;
    has_setup_after?: boolean;
};

export type WarmUpDefaultsScope = 'all_blocks' | 'first_block';

export type PlateBar = {
    name: string;
    weight_g: number;
    is_default: boolean;
};

export type PlateRow = {
    denomination_g: number;
    count: number;
    colour: string | null;
};

export type PlateProfile = {
    name: string;
    bars: PlateBar[];
    plates: PlateRow[];
};
