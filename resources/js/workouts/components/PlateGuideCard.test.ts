import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import PlateGuideCard from '@/workouts/components/PlateGuideCard.vue';

describe('PlateGuideCard', () => {
    it('emits applyNearest when the target weight is not loadable', async () => {
        const wrapper = mount(PlateGuideCard, {
            props: {
                plateLoad: {
                    bar_g: 20_000,
                    per_side: [{ denomination_g: 20_000, count: 1 }],
                    total_g: 60_000,
                    exact: false,
                    delta_g: -2500,
                },
                formatPlateStack: '20 bar + 1×20 / side',
                weightUnit: 'kg',
            },
        });

        await wrapper.get('button').trigger('click');

        expect(wrapper.emitted('applyNearest')).toHaveLength(1);
    });

    it('hides the nearest action when the weight is already loadable', () => {
        const wrapper = mount(PlateGuideCard, {
            props: {
                plateLoad: {
                    bar_g: 20_000,
                    per_side: [{ denomination_g: 20_000, count: 1 }],
                    total_g: 60_000,
                    exact: true,
                    delta_g: 0,
                },
                formatPlateStack: '20 bar + 1×20 / side',
                weightUnit: 'kg',
            },
        });

        expect(wrapper.find('button').exists()).toBe(false);
    });
});
