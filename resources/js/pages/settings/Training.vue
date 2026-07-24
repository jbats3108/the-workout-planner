<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type WarmUpStep = { percent: number; reps: number };

const props = defineProps<{
    warm_up_steps_default: WarmUpStep[];
    using_app_fallback: boolean;
}>();

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Training defaults',
        href: '/settings/training',
    },
];

const form = useForm({
    warm_up_steps_default: props.warm_up_steps_default.map((s) => ({ ...s })),
});

const addStep = () => {
    form.warm_up_steps_default.push({ percent: 50, reps: 5 });
};

const removeStep = (index: number) => {
    form.warm_up_steps_default.splice(index, 1);
};

const submit = () => {
    form.put(route('training.update'));
};

const resetToApp = () => {
    router.post(route('training.reset'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Training defaults" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Warm-up defaults"
                    description="Seeded into new routine blocks. Each step is a percent of working weight and its own reps."
                />

                <div
                    v-if="successMessage"
                    class="rounded-xl border border-primary/40 bg-primary/10 px-4 py-3 text-sm text-primary"
                    role="status"
                >
                    {{ successMessage }}
                </div>

                <p v-if="using_app_fallback" class="text-sm text-muted-foreground">
                    Using the app fallback ladder until you save your own.
                </p>

                <form class="space-y-4" @submit.prevent="submit">
                    <div
                        v-for="(step, index) in form.warm_up_steps_default"
                        :key="index"
                        class="flex flex-wrap items-end gap-3"
                    >
                        <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                            % of working
                            <input
                                v-model.number="step.percent"
                                type="number"
                                min="1"
                                max="100"
                                class="w-24 rounded border border-border bg-card px-3 py-2 font-mono text-foreground"
                                required
                            />
                        </label>
                        <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                            Reps
                            <input
                                v-model.number="step.reps"
                                type="number"
                                min="1"
                                max="100"
                                class="w-24 rounded border border-border bg-card px-3 py-2 font-mono text-foreground"
                                required
                            />
                        </label>
                        <button
                            type="button"
                            class="rounded border border-border px-3 py-2 text-sm text-muted-foreground hover:text-destructive"
                            @click="removeStep(index)"
                        >
                            Remove
                        </button>
                    </div>

                    <InputError :message="form.errors.warm_up_steps_default" />

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-full border border-border px-4 py-2 text-sm text-muted-foreground hover:text-foreground"
                            @click="addStep"
                        >
                            + Step
                        </button>
                        <button
                            type="submit"
                            class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            Save
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-border px-4 py-2 text-sm text-muted-foreground hover:text-foreground"
                            @click="resetToApp"
                        >
                            Reset to app default
                        </button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
