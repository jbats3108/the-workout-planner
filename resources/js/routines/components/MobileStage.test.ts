import MobileStage from '@/routines/components/MobileStage.vue';
import { createRoutineEditor, routineEditorKey } from '@/routines/composables/useRoutineEditor';
import { exerciseOption, routinePayload } from '@/test/factories';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { defineComponent, h, nextTick, provide } from 'vue';

function mountStage() {
    let editor!: ReturnType<typeof createRoutineEditor>;
    const Host = defineComponent({
        setup() {
            editor = createRoutineEditor({
                routine: routinePayload(),
                exercises: [exerciseOption()],
                weight_unit: 'kg',
                warm_up_defaults: [],
            });
            provide(routineEditorKey, editor);

            return () => h(MobileStage);
        },
    });

    const wrapper = mount(Host, {
        attachTo: document.body,
        global: {
            mocks: {
                route: (name: string) => `/${name}`,
            },
        },
    });

    return { wrapper, editor };
}

describe('MobileStage', () => {
    it('keeps Cancel/Save in document flow under the stage content', () => {
        const { wrapper } = mountStage();
        const save = Array.from(document.body.querySelectorAll('button')).find((b) => b.textContent?.trim() === 'Save');
        expect(save).toBeTruthy();

        const bar = save!.closest('.max-w-lg');
        expect(bar).toBeTruthy();
        expect(bar?.className).not.toContain('fixed');

        wrapper.unmount();
    });

    it('shows save validation errors above the mobile Save actions', async () => {
        const { wrapper, editor } = mountStage();
        Object.assign(editor.form.errors, {
            'blocks.0.working.rest_seconds': 'The rest seconds field is required.',
        });
        await nextTick();

        const alert = document.body.querySelector('[data-routine-save-errors]');
        expect(alert?.textContent).toContain("Couldn't save");
        expect(alert?.textContent).toContain('The rest seconds field is required.');

        const save = Array.from(document.body.querySelectorAll('button')).find((b) => b.textContent?.trim() === 'Save');
        expect(save?.closest('.max-w-lg')?.contains(alert)).toBe(true);

        wrapper.unmount();
    });
});
