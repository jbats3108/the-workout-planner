<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref, type HTMLAttributes } from 'vue';

defineProps<{
    id?: string;
    placeholder?: string;
    tabindex?: number | string;
    autocomplete?: string;
    required?: boolean;
    autofocus?: boolean;
    class?: HTMLAttributes['class'];
}>();

const model = defineModel<string>({ required: true });
const showPassword = ref(false);
const root = ref<HTMLElement | null>(null);

defineExpose({
    focus: () => root.value?.querySelector('input')?.focus(),
});
</script>

<template>
    <div ref="root" class="relative">
        <Input
            :id="id"
            v-model="model"
            :type="showPassword ? 'text' : 'password'"
            :required="required"
            :tabindex="tabindex"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :autofocus="autofocus"
            :class="['pr-10', $props.class]"
        />
        <button
            type="button"
            class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
            :aria-pressed="showPassword"
            tabindex="-1"
            @click="showPassword = !showPassword"
        >
            <EyeOff v-if="showPassword" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
        </button>
    </div>
</template>
