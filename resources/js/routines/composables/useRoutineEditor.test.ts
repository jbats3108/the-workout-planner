import { createRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { exerciseOption, routinePayload } from '@/test/factories';
import { inertiaMocks } from '@/test/inertiaMocks';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';

function mountEditor(props = {}) {
    let editor!: ReturnType<typeof createRoutineEditor>;
    const Wrapper = defineComponent({
        setup() {
            editor = createRoutineEditor({
                routine: routinePayload({ blocks: [] }),
                exercises: [exerciseOption(), exerciseOption({ id: 2, name: 'Row' })],
                weight_unit: 'kg',
                warm_up_defaults: [{ percent: 40, reps: 5 }],
                warm_up_defaults_scope: 'all_blocks',
                ...props,
            });
            return () => h('div');
        },
    });
    mount(Wrapper);
    return editor;
}

describe('createRoutineEditor', () => {
    beforeEach(() => {
        inertiaMocks().inertiaFormPut.mockClear();
    });

    it('adds blocks and selects the new block', () => {
        const editor = mountEditor();
        editor.addBlock(false);
        expect(editor.form.blocks).toHaveLength(1);
        expect(editor.active.value).toBe(0);
        expect(editor.form.blocks[0].warm_up.steps).toHaveLength(1);
    });

    it('keeps dropsets collapsed by default and resets when changing blocks', async () => {
        const editor = mountEditor({
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
                        working: {
                            set_count: 3,
                            rest_seconds: 120,
                            dropsets: [{ set_index: 0, segments: [{ weight_kg: 60 }, { weight_kg: 50 }] }],
                        },
                        warm_up: { set_count: 0, rest_seconds: 60, steps: [] },
                    },
                    {
                        is_superset: false,
                        has_setup_after: false,
                        has_setup_after_warm_up: false,
                        exercises: [
                            {
                                exercise_id: 2,
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
        });
        expect(editor.dropsetsExpanded.value).toBe(false);
        editor.toggleDropsetsExpanded();
        expect(editor.dropsetsExpanded.value).toBe(true);
        editor.active.value = 1;
        await nextTick();
        expect(editor.dropsetsExpanded.value).toBe(false);
    });

    it('resolves exercise names from the catalog', () => {
        const editor = mountEditor();
        expect(editor.exerciseName(2)).toBe('Row');
        expect(editor.exerciseName(null)).toBe('Exercise');
    });

    it('submits routine update via inertia form', () => {
        const editor = mountEditor();
        editor.save();
        expect(inertiaMocks().inertiaFormPut).toHaveBeenCalled();
    });
});
