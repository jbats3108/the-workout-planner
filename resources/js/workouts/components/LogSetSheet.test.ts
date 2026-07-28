import LogSetSheet from '@/workouts/components/LogSetSheet.vue';
import { mount } from '@vue/test-utils';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { describe, expect, it } from 'vitest';
import { nextTick } from 'vue';

describe('LogSetSheet', () => {
    it('covers the full screen without a half-height sheet', async () => {
        const wrapper = mount(LogSetSheet, {
            props: {
                open: true,
                'onUpdate:open': () => {},
            },
            slots: {
                default: '<p>fields</p>',
            },
            attachTo: document.body,
        });

        await nextTick();

        const panel = document.querySelector('[role="dialog"]') as HTMLElement | null;
        expect(panel).not.toBeNull();
        expect(panel!.className).toContain('fixed');
        expect(panel!.className).toContain('inset-0');
        expect(panel!.className).not.toContain('max-h-[55dvh]');
        expect(panel!.className).not.toContain('h-dvh');
        expect(panel!.textContent).toContain('fields');

        wrapper.unmount();
    });
});

describe('viewport keyboard policy', () => {
    it('overlays the virtual keyboard instead of resizing content', () => {
        const blade = readFileSync(resolve(process.cwd(), 'resources/views/app.blade.php'), 'utf8');
        expect(blade).toContain('interactive-widget=overlays-content');
        expect(blade).not.toContain('interactive-widget=resizes-content');
    });
});
