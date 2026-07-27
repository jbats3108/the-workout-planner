import { playerBlock, playerSet } from '@/test/factories';
import { flattenPlayerSets } from '@/workouts/lib/focus';
import { formatRestSeconds, groupLabel, setupHintText, workoutProgressLabel } from '@/workouts/lib/labels';
import { describe, expect, it } from 'vitest';

describe('labels', () => {
    it('formats group labels', () => {
        expect(groupLabel('warm_up')).toBe('Warm-up');
        expect(groupLabel('working')).toBe('Working');
    });

    it('formats rest seconds', () => {
        expect(formatRestSeconds(90)).toBe('1:30');
        expect(formatRestSeconds(30)).toBe('30s');
    });

    it('counts workout progress', () => {
        const flat = flattenPlayerSets([
            playerBlock({
                sets: [playerSet({ completed: true }), playerSet({ id: 2, completed: false })],
            }),
        ]);
        expect(workoutProgressLabel(flat)).toBe('1/2');
    });

    it('describes setup hints', () => {
        const block = playerBlock({ position: 2 });
        expect(setupHintText({ kind: 'setup', blockIndex: 0, phase: 'after_warm_up' }, block)).toContain('Block 2');
    });
});
