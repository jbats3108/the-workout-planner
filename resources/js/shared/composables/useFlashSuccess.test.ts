import { useFlashError, useFlashSuccess } from '@/shared/composables/useFlashSuccess';
import { inertiaMocks } from '@/test/inertiaMocks';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import { defineComponent, h } from 'vue';

describe('flash composables', () => {
    beforeEach(() => {
        inertiaMocks().pageProps.flash.success = 'Saved routine.';
        inertiaMocks().pageProps.flash.error = 'Workout not found. Check the URL and try again.';
    });

    it('reads flash success from page props', () => {
        let message: ReturnType<typeof useFlashSuccess> | undefined;
        const Wrapper = defineComponent({
            setup() {
                message = useFlashSuccess();

                return () => h('div');
            },
        });
        mount(Wrapper);
        expect(message?.value).toBe('Saved routine.');
    });

    it('reads flash error from page props', () => {
        let message: ReturnType<typeof useFlashError> | undefined;
        const Wrapper = defineComponent({
            setup() {
                message = useFlashError();

                return () => h('div');
            },
        });
        mount(Wrapper);
        expect(message?.value).toBe('Workout not found. Check the URL and try again.');
    });
});
