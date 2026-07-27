import { defineComponent, h, provide } from 'vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import ExerciseFinder from '@/routines/components/ExerciseFinder.vue';
import { createRoutineEditor, routineEditorKey } from '@/routines/composables/useRoutineEditor';
import { exerciseOption, routinePayload } from '@/test/factories';
import { inertiaMocks } from '@/test/inertiaMocks';

function mountFinder() {
    let editor!: ReturnType<typeof createRoutineEditor>;
    const Host = defineComponent({
        setup() {
            editor = createRoutineEditor({
                routine: routinePayload({
                    blocks: [
                        {
                            is_superset: false,
                            has_setup_after: false,
                            has_setup_after_warm_up: false,
                            exercises: [
                                {
                                    exercise_id: 1,
                                    working_weight_kg: 60,
                                    prescribed_reps: 6,
                                    achievement_floor: null,
                                    progression_target: null,
                                },
                            ],
                            working: { set_count: 3, rest_seconds: 120, dropsets: [] },
                            warm_up: { set_count: 0, rest_seconds: 60, steps: [] },
                        },
                    ],
                }),
                exercises: [exerciseOption(), exerciseOption({ id: 2, name: 'Row' })],
                weight_unit: 'kg',
                warm_up_defaults: [],
            });
            provide(routineEditorKey, editor);
            return () => h(ExerciseFinder);
        },
    });
    const wrapper = mount(Host);
    return { wrapper, editor };
}

describe('ExerciseFinder', () => {
    beforeEach(() => {
        inertiaMocks().inertiaFormPut.mockClear();
    });

    it('lists matching exercises and applies pick', async () => {
        const { wrapper, editor } = mountFinder();
        editor.exerciseQuery.value = 'row';
        await wrapper.vm.$nextTick();
        expect(wrapper.text()).toContain('Row');
        const button = wrapper.find('button');
        await button.trigger('click');
        expect(editor.form.blocks[0].exercises[0].exercise_id).toBe(2);
    });
});
