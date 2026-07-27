import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it } from 'vitest';
import { useFlashSuccess } from '@/shared/composables/useFlashSuccess';
import { inertiaMocks } from '@/test/inertiaMocks';

describe('useFlashSuccess', () => {
    beforeEach(() => {
        inertiaMocks().pageProps.flash.success = 'Saved routine.';
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
});
