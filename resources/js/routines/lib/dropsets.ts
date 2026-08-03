import type { Block, DropsetRecipe, DropsetSegment } from '@/routines/types';

export function dropsetForIndex(block: Block, setIndex: number): DropsetRecipe | undefined {
    return block.working.dropsets.find((d) => d.set_index === setIndex);
}

export function isDropsetSlot(block: Block, setIndex: number): boolean {
    return !block.is_superset && (dropsetForIndex(block, setIndex)?.segments.length ?? 0) >= 2;
}

export function defaultDropsetSegments(block: Block): DropsetSegment[] {
    const working = block.exercises[0]?.working_weight_kg ?? 20;
    const step = Math.max(2.5, Math.round(working * 0.1 * 2) / 2);
    return [{ weight_kg: working }, { weight_kg: Math.max(0, Math.round((working - step) * 2) / 2) }];
}

export function setSlotKind(block: Block, setIndex: number, kind: 'single' | 'dropset'): void {
    if (block.is_superset) {
        return;
    }
    const existing = dropsetForIndex(block, setIndex);
    if (kind === 'single') {
        block.working.dropsets = block.working.dropsets.filter((d) => d.set_index !== setIndex);
        return;
    }
    if (existing) {
        return;
    }
    block.working.dropsets.push({
        set_index: setIndex,
        segments: defaultDropsetSegments(block),
    });
}

export function addDropsetSegment(block: Block, setIndex: number): void {
    const recipe = dropsetForIndex(block, setIndex);
    if (!recipe) {
        return;
    }
    const last = recipe.segments[recipe.segments.length - 1]?.weight_kg ?? 10;
    const step = 2.5;
    recipe.segments.push({ weight_kg: Math.max(0, Math.round((last - step) * 2) / 2) });
}

export function removeDropsetSegment(block: Block, setIndex: number, segmentIndex: number): void {
    const recipe = dropsetForIndex(block, setIndex);
    if (!recipe || recipe.segments.length <= 2) {
        return;
    }
    recipe.segments.splice(segmentIndex, 1);
}

export function applyRunTheRack(block: Block, setIndex: number, rack: { start: number; end: number; step: number }): void {
    const start = Number(rack.start);
    const end = Number(rack.end);
    const step = Number(rack.step);
    if (!(step > 0) || start < end) {
        return;
    }
    const segments: DropsetSegment[] = [];
    for (let w = start; w >= end - 0.0001; w -= step) {
        segments.push({ weight_kg: Math.round(w * 1000) / 1000 });
    }
    if (segments.length < 2) {
        return;
    }
    const existing = dropsetForIndex(block, setIndex);
    if (existing) {
        existing.segments = segments;
    } else {
        block.working.dropsets.push({ set_index: setIndex, segments });
    }
}

export function trimDropsetsToSetCount(block: Block): void {
    const count = Math.max(1, Number(block.working.set_count) || 1);
    block.working.set_count = count;
    block.working.dropsets = block.working.dropsets.filter((d) => d.set_index < count);
}

export function dropsetSummary(block: Block): string {
    if (block.is_superset || !block.working.dropsets.length) {
        return '';
    }
    return block.working.dropsets.map((d) => `S${d.set_index + 1}:${d.segments.map((s) => s.weight_kg).join('→')}`).join(' · ');
}
