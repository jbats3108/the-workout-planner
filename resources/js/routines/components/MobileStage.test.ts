import MobileStage from '@/routines/components/MobileStage.vue';
import { createRoutineEditor, routineEditorKey } from '@/routines/composables/useRoutineEditor';
import { exerciseOption, routinePayload } from '@/test/factories';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { defineComponent, h, provide } from 'vue';

function mountStage() {
    const Host = defineComponent({
        setup() {
            const editor = createRoutineEditor({
                routine: routinePayload(),
                exercises: [exerciseOption()],
                weight_unit: 'kg',
                warm_up_defaults: [],
            });
            provide(routineEditorKey, editor);

            return () => h(MobileStage);
        },
    });

    return mount(Host, {
        attachTo: document.body,
        global: {
            mocks: {
                route: (name: string) => `/${name}`,
            },
        },
    });
}

describe('MobileStage', () => {
    it('keeps Cancel/Save in document flow under the stage content', () => {
        const wrapper = mountStage();
        const save = Array.from(document.body.querySelectorAll('button')).find((b) => b.textContent?.trim() === 'Save');
        expect(save).toBeTruthy();

        const bar = save!.parentElement;
        expect(bar?.className).not.toContain('fixed');
        expect(bar?.className).toContain('max-w-lg');

        wrapper.unmount();
    });
});
