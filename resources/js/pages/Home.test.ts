import Home from '@/pages/Home.vue';
import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';

describe('Home', () => {
    it('links to beta tester FAQs and privacy in the site header', () => {
        const route = vi.fn((name: string) => `/${name}`);
        vi.stubGlobal('route', route);

        const wrapper = mount(Home, {
            global: {
                stubs: {
                    DarkModeToggle: true,
                    Link: {
                        props: ['href'],
                        template: '<a :href="href"><slot /></a>',
                    },
                },
                mocks: {
                    route,
                },
            },
        });

        const hrefs = wrapper.findAll('a').map((a) => a.attributes('href'));
        expect(hrefs).toContain('/login');
        expect(hrefs).toContain('/beta-tester-faqs');
        expect(hrefs).toContain('/privacy');
        expect(wrapper.text()).toContain('Beta testers');
        expect(wrapper.text()).toContain('Privacy');
        expect(route).toHaveBeenCalledWith('beta-tester-faqs');
        expect(route).toHaveBeenCalledWith('privacy');
    });
});
