import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import UpcomingCard from '@/workouts/components/UpcomingCard.vue';

describe('UpcomingCard', () => {
    it('renders next set details', () => {
        const wrapper = mount(UpcomingCard, {
            props: {
                upcoming: {
                    exerciseName: 'Squat',
                    groupLabel: 'Working',
                    setNumber: 2,
                    blockPosition: 1,
                    weightLabel: '100',
                    reps: 5,
                    isDropset: false,
                    plateStack: '20 bar + 2×20 / side',
                },
                weightUnit: 'kg',
            },
        });
        expect(wrapper.text()).toContain('Squat');
        expect(wrapper.text()).toContain('100kg');
        expect(wrapper.text()).toContain('× 5');
        expect(wrapper.text()).toContain('20 bar');
    });
});
