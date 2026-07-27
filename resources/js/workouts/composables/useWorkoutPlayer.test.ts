import { plateProfile, playerBlock, playerSet, workoutPayload } from '@/test/factories';
import { inertiaMocks } from '@/test/inertiaMocks';
import { createWorkoutPlayer, useWorkoutPlayer, workoutPlayerKey } from '@/workouts/composables/useWorkoutPlayer';
import * as playerInteraction from '@/workouts/lib/playerInteraction';
import * as restAlert from '@/workouts/lib/restAlert';
import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';

function mountPlayer(overrides: Parameters<typeof workoutPayload>[0] = {}) {
    let player!: ReturnType<typeof createWorkoutPlayer>;
    const Wrapper = defineComponent({
        setup() {
            player = createWorkoutPlayer({
                workout: workoutPayload(overrides),
                plate_profile: plateProfile(),
            });
            return () => h('div');
        },
    });
    mount(Wrapper);
    return player;
}

describe('createWorkoutPlayer', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.spyOn(playerInteraction, 'preparePlayerInteraction').mockImplementation(() => {});
        vi.spyOn(restAlert, 'notifyRestEnded').mockImplementation(() => {});
        vi.stubGlobal(
            'route',
            vi.fn((name: string, _params?: unknown) => `/${String(name)}`),
        );
        vi.stubGlobal(
            'confirm',
            vi.fn(() => true),
        );
        Object.defineProperty(navigator, 'wakeLock', {
            configurable: true,
            value: { request: vi.fn().mockRejectedValue(new Error('unsupported')) },
        });
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('focuses first incomplete set', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ id: 7, completed: false })],
                }),
            ],
        });
        expect(player.focus.value).toEqual({ kind: 'set', blockIndex: 0, setId: 7 });
    });

    it('builds upcoming preview for next set', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ id: 1, completed: true, logged_weight_kg: 90 }), playerSet({ id: 2, set_index: 1, completed: false })],
                }),
            ],
        });
        expect(player.upcoming.value?.exerciseName).toBe('Squat');
        expect(player.upcoming.value?.weightLabel).toBe('90');
    });

    it('syncs draft weight from previous logged set', async () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [
                        playerSet({ id: 1, set_index: 0, completed: true, logged_weight_kg: 95 }),
                        playerSet({ id: 2, set_index: 1, completed: false }),
                    ],
                }),
            ],
        });
        await vi.waitFor(() => {
            expect(player.setForm.weight_kg).toBe(95);
        });
    });

    it('reports progress label', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ completed: true }), playerSet({ id: 2, completed: false })],
                }),
            ],
        });
        expect(player.progressLabel.value).toBe('1/2');
    });

    it('posts set completion payload', () => {
        const player = mountPlayer();
        player.setForm.reps = 5;
        player.setForm.weight_kg = 100;
        player.logSheetOpen.value = true;
        player.completeSet();
        expect(inertiaMocks().inertiaFormPost).toHaveBeenCalledWith(
            '/workouts.sets.complete',
            expect.objectContaining({ preserveScroll: true, only: ['workout'] }),
        );
    });

    it('manages dropset draft segments', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [
                        playerSet({
                            is_dropset: true,
                            segments: [
                                { position: 0, weight_kg: 80 },
                                { position: 1, weight_kg: 70 },
                            ],
                        }),
                    ],
                }),
            ],
        });
        expect(player.draftSegments.value).toEqual([{ weight_kg: 80 }, { weight_kg: 70 }]);
        player.addDropSegment();
        expect(player.draftSegments.value).toHaveLength(3);
        player.removeDropSegment(2);
        expect(player.draftSegments.value).toHaveLength(2);
    });

    it('skips rest and advances focus', () => {
        vi.useFakeTimers();
        inertiaMocks().inertiaFormPost.mockImplementation((_url, options) => {
            options?.onSuccess?.();
        });
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ id: 1, completed: false, rest_seconds: 90 }), playerSet({ id: 2, set_index: 1, completed: false })],
                }),
            ],
        });
        player.logSheetOpen.value = true;
        player.completeSet();
        expect(player.restSecondsLeft.value).toBe(90);
        expect(playerInteraction.preparePlayerInteraction).toHaveBeenCalled();
        player.skipRest();
        expect(player.restSecondsLeft.value).toBe(0);
        expect(player.focus.value).toEqual({ kind: 'set', blockIndex: 0, setId: 1 });
    });

    it('alerts when rest timer completes', () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2026-01-01T12:00:00Z'));
        inertiaMocks().inertiaFormPost.mockImplementation((_url, options) => {
            options?.onSuccess?.();
        });
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ id: 1, completed: false, rest_seconds: 3 }), playerSet({ id: 2, set_index: 1, completed: false })],
                }),
            ],
        });
        player.logSheetOpen.value = true;
        player.completeSet();
        vi.advanceTimersByTime(3000);
        expect(restAlert.notifyRestEnded).toHaveBeenCalled();
        expect(player.restSecondsLeft.value).toBe(0);
    });

    it('acknowledges setup after warm-up', () => {
        vi.useFakeTimers();
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    has_setup_after_warm_up: true,
                    sets: [
                        playerSet({ id: 1, group_type: 'warm_up', completed: true }),
                        playerSet({ id: 2, group_type: 'working', completed: false, rest_seconds: 60 }),
                    ],
                }),
            ],
        });
        expect(player.focus.value.kind).toBe('setup');
        player.acknowledgeSetup();
        expect(player.restSecondsLeft.value).toBe(60);
    });

    it('applies nearest plate load to draft weight', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ equipment: 'barbell', target_weight_kg: 97.5 })],
                }),
            ],
        });
        player.setForm.weight_kg = 97.5;
        player.applyNearestLoad();
        expect(player.setForm.weight_kg).toBe(95);
    });

    it('applies nearest plate load from the main stage target', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ equipment: 'barbell', target_weight_kg: 97.5 })],
                }),
            ],
        });
        expect(player.stageWeightKg.value).toBe(97.5);
        expect(player.stagePlateLoad.value?.exact).toBe(false);

        player.applyStageNearestLoad();

        expect(player.stageWeightKg.value).toBe(95);
        expect(player.setForm.weight_kg).toBe(95);
        expect(player.stagePlateLoad.value?.exact).toBe(true);
    });

    it('keeps stage nearest weight when opening the log sheet', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    sets: [playerSet({ equipment: 'barbell', target_weight_kg: 97.5 })],
                }),
            ],
        });
        player.applyStageNearestLoad();
        player.openLogSheet();

        expect(player.setForm.weight_kg).toBe(95);
        expect(player.stageWeightKg.value).toBe(95);
    });

    it('exposes canPromoteToDropset for working sets', () => {
        const player = mountPlayer();
        expect(player.canPromoteToDropset.value).toBe(true);
    });

    it('promotes working set to dropset', () => {
        const player = mountPlayer();
        player.promoteToDropset();
        expect(inertiaMocks().routerMocks.post).toHaveBeenCalledWith(
            '/workouts.sets.promote-dropset',
            expect.objectContaining({ segments: expect.any(Array) }),
            expect.objectContaining({ preserveScroll: true, only: ['workout'] }),
        );
    });

    it('finishes workout when confirmed', () => {
        const player = mountPlayer();
        player.finishWorkout();
        expect(inertiaMocks().routerMocks.post).toHaveBeenCalledWith('/workouts.finish', {}, expect.any(Object));
    });

    it('abandons workout when confirmed', () => {
        const player = mountPlayer();
        player.abandonWorkout();
        expect(inertiaMocks().routerMocks.post).toHaveBeenCalledWith('/workouts.discard', {}, expect.any(Object));
    });

    it('leaves workout via dashboard visit', () => {
        const player = mountPlayer();
        player.leaveWorkout();
        expect(inertiaMocks().routerMocks.visit).toHaveBeenCalledWith('/dashboard');
    });

    it('cancels leave when confirm is declined', () => {
        vi.mocked(globalThis.confirm).mockReturnValueOnce(false);
        const player = mountPlayer();
        player.leaveWorkout();
        expect(inertiaMocks().routerMocks.visit).not.toHaveBeenCalled();
    });

    it('adds and removes working sets', () => {
        const player = mountPlayer({
            blocks: [
                playerBlock({
                    id: 5,
                    sets: [playerSet({ id: 1, set_index: 0, completed: false }), playerSet({ id: 2, set_index: 1, completed: false })],
                }),
            ],
        });
        expect(player.canAddWorkingSet.value).toBe(true);
        expect(player.canRemoveWorkingSet.value).toBe(true);
        player.addWorkingSet();
        expect(inertiaMocks().routerMocks.post).toHaveBeenCalledWith(
            '/workouts.working-sets.add',
            {},
            expect.objectContaining({ preserveScroll: true, only: ['workout'] }),
        );
        player.removeWorkingSet();
        expect(inertiaMocks().routerMocks.delete).toHaveBeenCalledWith(
            '/workouts.sets.remove',
            expect.objectContaining({ preserveScroll: true, only: ['workout'] }),
        );
    });

    it('opens and cancels the log sheet without posting', () => {
        const player = mountPlayer();
        player.openLogSheet();
        expect(player.logSheetOpen.value).toBe(true);
        player.cancelLogSheet();
        expect(player.logSheetOpen.value).toBe(false);
        expect(inertiaMocks().inertiaFormPost).not.toHaveBeenCalled();
    });

    it('ignores completeSet when the log sheet is closed', () => {
        const player = mountPlayer();
        player.completeSet();
        expect(inertiaMocks().inertiaFormPost).not.toHaveBeenCalled();
    });

    it('ignores completeSet when workout is not in progress', () => {
        const player = mountPlayer({ status: 'finished' });
        player.logSheetOpen.value = true;
        player.completeSet();
        expect(inertiaMocks().inertiaFormPost).not.toHaveBeenCalled();
    });
});

describe('useWorkoutPlayer', () => {
    it('throws when provider is missing', () => {
        const Wrapper = defineComponent({
            setup() {
                expect(() => useWorkoutPlayer()).toThrow('WorkoutPlayer not provided');
                return () => h('div');
            },
        });
        mount(Wrapper);
    });

    it('returns injected player', () => {
        const player = mountPlayer();
        let injected!: ReturnType<typeof createWorkoutPlayer>;
        const Wrapper = defineComponent({
            setup() {
                injected = useWorkoutPlayer();
                return () => h('div');
            },
        });
        mount(Wrapper, {
            global: { provide: { [workoutPlayerKey as symbol]: player } },
        });
        expect(injected).toBe(player);
    });
});
