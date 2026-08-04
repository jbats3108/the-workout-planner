import BrandCopy from '@/components/BrandCopy.vue';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

describe('BrandCopy', () => {
    it('inserts branded OVRLOAD marks into plain text', () => {
        const wrapper = mount(BrandCopy, {
            props: {
                text: 'Use OVRLOAD to log lifts. OVRLOAD is invite-only.',
            },
        });

        expect(wrapper.text()).toBe('Use OVRLOAD to log lifts. OVRLOAD is invite-only.');
        expect(wrapper.findAll('.text-primary')).toHaveLength(2);
        expect(wrapper.findAll('.text-primary').every((node) => node.text() === 'OVR')).toBe(true);
    });

    it('leaves text without the brand name unchanged', () => {
        const wrapper = mount(BrandCopy, {
            props: { text: 'No brand here.' },
        });

        expect(wrapper.text()).toBe('No brand here.');
        expect(wrapper.findAll('.text-primary')).toHaveLength(0);
    });
});
