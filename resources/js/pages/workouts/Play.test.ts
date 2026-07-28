import Play from '@/pages/workouts/Play.vue';
import { plateProfile, playerBlock, playerSet, workoutPayload } from '@/test/factories';
import CompleteStage from '@/workouts/components/CompleteStage.vue';
import PlayerHeader from '@/workouts/components/PlayerHeader.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

describe('Play complete stage', () => {
    beforeEach(() => {
        vi.stubGlobal(
            'route',
            vi.fn((name: string) => `/${String(name)}`),
        );
        Object.defineProperty(navigator, 'wakeLock', {
            configurable: true,
            value: { request: vi.fn().mockRejectedValue(new Error('unsupported')) },
        });
    });

    it('hides the player header so complete covers the full page', () => {
        const wrapper = mount(Play, {
            props: {
                workout: workoutPayload({
                    blocks: [
                        playerBlock({
                            sets: [playerSet({ id: 1, completed: true, logged_weight_kg: 100, logged_reps: 5 })],
                        }),
                    ],
                }),
                plate_profile: plateProfile(),
            },
        });

        expect(wrapper.findComponent(PlayerHeader).exists()).toBe(false);
        expect(wrapper.findComponent(CompleteStage).exists()).toBe(true);
        expect(wrapper.text()).toContain('All sets logged');
    });
});
