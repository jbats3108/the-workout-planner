import type { DeloadSettingsDensity, DropsetEditorDensity, EditorDensity } from '@/routines/types';

export const dropsetEditorDensity: Record<EditorDensity, DropsetEditorDensity> = {
    desktop: {
        card: 'rounded-lg border border-border bg-background p-3',
        setLabel: 'text-xs font-medium text-muted-foreground',
        select: 'rounded border border-border bg-card px-2 py-1 text-xs',
        segmentRow: 'mb-1 flex items-center gap-1',
        weightInput: 'w-20 rounded border border-border bg-card px-2 py-1 font-mono text-sm',
        addDropContainer: 'mt-2 flex flex-wrap items-center gap-2',
        addDropButton: 'text-xs text-primary',
        rackControls: 'flex flex-wrap items-end gap-1',
        rackLabel: 'text-[10px] text-muted-foreground',
        rackInput: 'mt-0.5 w-16 rounded border border-border bg-card px-1 py-1 font-mono text-xs',
        rackFillButton: 'rounded border border-primary/40 px-2 py-1 text-xs text-primary hover:bg-primary/10',
        rackFillLabel: 'Fill',
    },
    mobile: {
        card: 'rounded-xl border border-border bg-background p-3',
        setLabel: 'text-sm font-medium',
        select: 'rounded-lg border border-border bg-card px-2 py-1 text-sm',
        segmentRow: 'mb-1.5 flex items-center gap-2',
        weightInput: 'w-24 rounded-lg border border-border bg-card px-2 py-1.5 font-mono text-base',
        addDropContainer: 'mt-1',
        addDropButton: 'text-xs text-primary',
        rackControls: 'grid grid-cols-3 gap-2',
        rackLabel: 'text-[10px] text-muted-foreground',
        rackInput: 'mt-0.5 w-full rounded-lg border border-border bg-card px-2 py-1.5 font-mono text-sm',
        rackFillButton: 'col-span-3 mt-2 w-full rounded-lg border border-primary/40 py-2 text-xs text-primary',
        rackFillLabel: 'Fill from rack',
    },
};

export const deloadSettingsDensity: Record<EditorDensity, DeloadSettingsDensity> = {
    desktop: {
        fieldsGrid: 'mt-3 grid max-w-xl gap-3 sm:grid-cols-2',
        fieldLabel: 'block text-sm',
        fieldTitle: 'text-muted-foreground',
        fieldHint: 'mt-0.5 block text-xs text-muted-foreground',
        input: 'mt-1.5 w-full rounded border border-border bg-card px-2 py-1.5 font-mono text-sm',
        weightHint: 'Working kg × factor (e.g. 0.8 → 80%)',
        repsHint: 'Target reps × factor (e.g. 0.8 → round down)',
    },
    mobile: {
        fieldsGrid: '',
        fieldLabel: 'block',
        fieldTitle: 'text-xs text-muted-foreground',
        fieldHint: 'mt-0.5 block text-[11px] text-muted-foreground',
        input: 'mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg',
        weightHint: 'Working kg × factor (0.8 = 80%)',
        repsHint: 'Target reps × factor',
    },
};
