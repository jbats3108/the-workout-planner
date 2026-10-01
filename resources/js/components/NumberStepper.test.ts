import NumberStepper from '@/components/NumberStepper.vue';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

describe('NumberStepper', () => {
    it('renders the current value and emits on type, clear, and step', async () => {
        const wrapper = mount(NumberStepper, {
            props: {
                modelValue: 5,
                min: 1,
                max: 10,
                ariaLabel: 'Target reps',
                'onUpdate:modelValue': (value: number | null) => wrapper.setProps({ modelValue: value }),
            },
        });

        const input = wrapper.get('[data-number-stepper-input]');
        expect((input.element as HTMLInputElement).value).toBe('5');

        await input.setValue('8');
        expect(wrapper.props('modelValue')).toBe(8);

        await input.setValue('');
        expect(wrapper.props('modelValue')).toBeNull();

        await wrapper.get('button[aria-label="Increase Target reps"]').trigger('click');
        expect(wrapper.props('modelValue')).toBe(1);

        await wrapper.get('button[aria-label="Increase Target reps"]').trigger('click');
        expect(wrapper.props('modelValue')).toBe(2);

        await wrapper.get('button[aria-label="Decrease Target reps"]').trigger('click');
        expect(wrapper.props('modelValue')).toBe(1);
    });

    it('disables decrement at min and increment at max', async () => {
        const wrapper = mount(NumberStepper, {
            props: {
                modelValue: 1,
                min: 1,
                max: 3,
                ariaLabel: 'Sets',
            },
        });

        expect(wrapper.get('button[aria-label="Decrease Sets"]').attributes('disabled')).toBeDefined();

        await wrapper.setProps({ modelValue: 3 });
        expect(wrapper.get('button[aria-label="Increase Sets"]').attributes('disabled')).toBeDefined();
    });
});
