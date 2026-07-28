import { plateProfile, playerBlock, playerSet, workoutPayload } from '@/test/factories';
import { inertiaMocks } from '@/test/inertiaMocks';
import CompleteStage from '@/workouts/components/CompleteStage.vue';
import { createWorkoutPlayer, workoutPlayerKey } from '@/workouts/composables/useWorkoutPlayer';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, provide } from 'vue';

function mountCompleteStage() {
    let player!: ReturnType<typeof createWorkoutPlayer>;
    const Host = defineComponent({
        setup() {
            player = createWorkoutPlayer({
                workout: workoutPayload({
                    routine_name: 'Barbell Strength',
                    blocks: [
                        playerBlock({
                            sets: [playerSet({ id: 1, completed: true, logged_weight_kg: 100, logged_reps: 5 })],
                        }),
                    ],
                }),
                plate_profile: plateProfile(),
            });
            provide(workoutPlayerKey, player);
            return () => h(CompleteStage);
        },
    });

    const wrapper = mount(Host, {
        attachTo: document.body,
    });

    return { wrapper, player };
}

describe('CompleteStage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.stubGlobal(
            'route',
            vi.fn((name: string) => `/${String(name)}`),
        );
        vi.stubGlobal(
            'confirm',
            vi.fn(() => true),
        );
    });

    it('renders a full-page complete state with finish and escape actions', () => {
        const { wrapper } = mountCompleteStage();
        const root = wrapper.findComponent(CompleteStage).element as HTMLElement;
        const finish = wrapper.findAll('button').find((b) => b.text() === 'Finish workout');

        expect(root.className).toContain('flex-1');
        expect(wrapper.text()).toContain('Complete');
        expect(wrapper.text()).toContain('All sets logged');
        expect(wrapper.text()).toContain('Barbell Strength');
        expect(finish?.exists()).toBe(true);
        expect(wrapper.text()).toContain('Abandon');
        expect(wrapper.text()).toContain('Leave');
    });

    it('finishes the workout from the primary CTA', () => {
        const { wrapper } = mountCompleteStage();
        const button = wrapper.findAll('button').find((b) => b.text() === 'Finish workout');

        expect(button).toBeDefined();
        button!.trigger('click');

        expect(inertiaMocks().routerMocks.post).toHaveBeenCalledWith('/workouts.finish', {}, expect.any(Object));
    });
});
