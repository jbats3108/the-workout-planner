<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Minus, Plus } from 'lucide-vue-next';
import { computed, useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });

const {
    modelValue,
    min = 1,
    max = 100,
    step = 1,
    disabled = false,
    placeholder = '',
    size = 'default',
    class: className,
    inputClass,
    ariaLabel = 'Value',
} = defineProps<{
    modelValue: number | null;
    min?: number;
    max?: number;
    step?: number;
    disabled?: boolean;
    placeholder?: string;
    size?: 'default' | 'compact' | 'mobile';
    class?: string;
    inputClass?: string;
    ariaLabel?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const attrs = useAttrs();

const display = computed(() => {
    if (modelValue === null || Number.isNaN(modelValue)) {
        return '';
    }

    return String(modelValue);
});

const atMin = computed(() => modelValue !== null && !Number.isNaN(modelValue) && modelValue <= min);
const atMax = computed(() => modelValue !== null && !Number.isNaN(modelValue) && modelValue >= max);

const shellClass = computed(() =>
    cn(
        'inline-flex max-w-full items-stretch overflow-hidden rounded-md border border-border bg-background',
        size === 'mobile' && 'w-full rounded-xl',
        size === 'compact' && 'rounded',
        className,
    ),
);

const fieldClass = computed(() =>
    cn(
        'min-w-0 flex-1 border-0 bg-transparent text-center font-mono tabular-nums outline-none focus-visible:ring-0',
        size === 'mobile' && 'px-2 py-2.5 text-lg',
        size === 'default' && 'h-8 w-10 px-1 text-sm',
        size === 'compact' && 'h-7 w-8 px-0.5 text-xs',
        inputClass,
    ),
);

const buttonSizeClass = computed(() =>
    cn(
        'shrink-0 rounded-none border-0 shadow-none',
        size === 'mobile' && 'h-auto px-3',
        size === 'default' && 'size-8',
        size === 'compact' && 'size-7',
    ),
);

const parseRaw = (raw: string): number | null => {
    const trimmed = raw.trim();
    if (trimmed === '') {
        return null;
    }

    const parsed = Number(trimmed);
    if (!Number.isFinite(parsed)) {
        return null;
    }

    return parsed;
};

const onInput = (event: Event): void => {
    emit('update:modelValue', parseRaw((event.target as HTMLInputElement).value));
};

const increment = (): void => {
    if (disabled) {
        return;
    }

    const base = modelValue === null || Number.isNaN(modelValue) ? min - step : modelValue;
    emit('update:modelValue', Math.min(max, base + step));
};

const decrement = (): void => {
    if (disabled) {
        return;
    }

    const base = modelValue === null || Number.isNaN(modelValue) ? min + step : modelValue;
    emit('update:modelValue', Math.max(min, base - step));
};
</script>

<template>
    <div :class="shellClass" data-number-stepper>
        <Button
            type="button"
            variant="ghost"
            :size="size === 'compact' ? 'icon-sm' : 'icon'"
            :class="buttonSizeClass"
            :disabled="disabled || atMin"
            :aria-label="`Decrease ${ariaLabel}`"
            @click="decrement"
        >
            <Minus :class="size === 'compact' ? 'size-3' : 'size-3.5'" />
        </Button>
        <input
            v-bind="attrs"
            :value="display"
            type="text"
            inputmode="numeric"
            :disabled="disabled"
            :placeholder="placeholder"
            :aria-label="ariaLabel"
            :class="fieldClass"
            data-number-stepper-input
            @input="onInput"
        />
        <Button
            type="button"
            variant="ghost"
            :size="size === 'compact' ? 'icon-sm' : 'icon'"
            :class="buttonSizeClass"
            :disabled="disabled || atMax"
            :aria-label="`Increase ${ariaLabel}`"
            @click="increment"
        >
            <Plus :class="size === 'compact' ? 'size-3' : 'size-3.5'" />
        </Button>
    </div>
</template>
