import UpcomingCard from '@/workouts/components/UpcomingCard.vue';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

describe('UpcomingCard', () => {
    it('renders next set details', () => {
        const wrapper = mount(UpcomingCard, {
            props: {
                upcoming: {
                    exerciseName: 'Squat',
                    groupLabel: 'Working',
                    setNumber: 2,
                    setCount: 4,
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
        expect(wrapper.text()).toContain('Set 2/4');
        expect(wrapper.text()).toContain('100kg');
        expect(wrapper.text()).toContain('× 5');
        expect(wrapper.text()).toContain('20 bar');
    });

    it('renders both exercises for a setup superset pair', () => {
        const wrapper = mount(UpcomingCard, {
            props: {
                upcoming: {
                    exerciseName: 'Press',
                    groupLabel: 'Working',
                    setNumber: 1,
                    setCount: 3,
                    blockPosition: 2,
                    weightLabel: '50',
                    reps: 8,
                    isDropset: false,
                    plateStack: null,
                },
                pair: [
                    {
                        exerciseName: 'Press',
                        groupLabel: 'Working',
                        setNumber: 1,
                        setCount: 3,
                        blockPosition: 2,
                        weightLabel: '50',
                        reps: 8,
                        isDropset: false,
                        plateStack: null,
                        letter: 'A',
                    },
                    {
                        exerciseName: 'Row',
                        groupLabel: 'Working',
                        setNumber: 1,
                        setCount: 3,
                        blockPosition: 2,
                        weightLabel: '60',
                        reps: 10,
                        isDropset: false,
                        plateStack: null,
                        letter: 'B',
                    },
                ],
                weightUnit: 'kg',
            },
        });
        expect(wrapper.text()).toContain('A ·');
        expect(wrapper.text()).toContain('Press');
        expect(wrapper.text()).toContain('B ·');
        expect(wrapper.text()).toContain('Row');
        expect(wrapper.text()).toContain('50kg');
        expect(wrapper.text()).toContain('60kg');
        expect(wrapper.text()).toContain('Superset');
    });
});
