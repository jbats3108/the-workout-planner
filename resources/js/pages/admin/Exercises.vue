<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Deferred, Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

type MuscleGroupOption = { name: string; slug: string };
type EquipmentOption = { value: string; label: string };
type AdminExercise = {
    id: number;
    name: string;
    slug: string;
    equipment: string | null;
    primary_muscle_group: string;
    primary_muscle_group_slug: string;
    secondary_muscle_group: string | null;
    secondary_muscle_group_slug: string | null;
};

const props = defineProps<{
    exercises?: AdminExercise[];
    muscle_groups: MuscleGroupOption[];
    equipment_options: EquipmentOption[];
}>();

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);
const query = ref('');
const catalog = computed(() => props.exercises ?? []);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Exercises', href: '/admin/exercises' },
];

const slugify = (value: string) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');

const form = useForm({
    name: '',
    slug: '',
    primary_muscle_group: props.muscle_groups[0]?.slug ?? '',
    secondary_muscle_group: null as string | null,
    equipment: null as string | null,
});

watch(
    () => form.name,
    (name) => {
        form.slug = slugify(name);
    },
);

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return catalog.value;
    return catalog.value.filter(
        (e) =>
            e.name.toLowerCase().includes(q) ||
            e.slug.includes(q) ||
            e.primary_muscle_group.toLowerCase().includes(q),
    );
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        secondary_muscle_group: data.secondary_muscle_group || null,
        equipment: data.equipment || null,
    })).post(route('exercises.store'), {
        onSuccess: () => form.reset('name', 'slug', 'equipment'),
    });
};

const remove = (exercise: AdminExercise) => {
    if (!confirm(`Delete “${exercise.name}” from the shared catalog?`)) return;
    router.delete(route('exercises.delete', exercise.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin · Exercises" />
        <AdminLayout>
            <HeadingSmall title="Shared exercises" description="Catalog lifts available to every user." />

            <div
                v-if="successMessage"
                class="rounded-xl border border-primary/40 bg-primary/10 px-4 py-3 text-sm text-primary"
                role="status"
            >
                {{ successMessage }}
            </div>

            <form class="space-y-3 rounded-xl border border-border bg-card p-4" @submit.prevent="submit">
                <p class="text-sm font-medium">Add exercise</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                        Name
                        <input
                            v-model="form.name"
                            class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground"
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </label>
                    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                        Slug
                        <input
                            v-model="form.slug"
                            class="rounded border border-border bg-background px-3 py-2 font-mono text-sm text-foreground"
                            required
                        />
                        <InputError :message="form.errors.slug" />
                    </label>
                    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                        Primary muscle group
                        <select
                            v-model="form.primary_muscle_group"
                            class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground"
                            required
                        >
                            <option v-for="g in muscle_groups" :key="g.slug" :value="g.slug">{{ g.name }}</option>
                        </select>
                        <InputError :message="form.errors.primary_muscle_group" />
                    </label>
                    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                        Secondary (optional)
                        <select
                            v-model="form.secondary_muscle_group"
                            class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground"
                        >
                            <option :value="null">None</option>
                            <option v-for="g in muscle_groups" :key="g.slug" :value="g.slug">{{ g.name }}</option>
                        </select>
                        <InputError :message="form.errors.secondary_muscle_group" />
                    </label>
                    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                        Equipment (optional)
                        <select
                            v-model="form.equipment"
                            class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground"
                        >
                            <option :value="null">Unspecified</option>
                            <option v-for="opt in equipment_options" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.equipment" />
                    </label>
                </div>
                <button
                    type="submit"
                    class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Create
                </button>
            </form>

            <div>
                <Deferred data="exercises">
                    <template #fallback>
                        <div class="animate-pulse space-y-3">
                            <div class="h-10 max-w-md rounded-xl bg-secondary" />
                            <div class="h-40 rounded-xl bg-secondary" />
                        </div>
                    </template>
                    <input
                        v-model="query"
                        type="search"
                        placeholder="Filter catalog…"
                        class="mb-3 w-full max-w-md rounded-xl border border-border bg-card px-3 py-2 text-sm"
                    />
                    <p class="mb-2 text-xs text-muted-foreground">{{ filtered.length }} of {{ catalog.length }}</p>
                    <ul class="divide-y divide-border rounded-xl border border-border">
                        <li
                            v-for="exercise in filtered"
                            :key="exercise.id"
                            class="flex flex-wrap items-center justify-between gap-2 px-4 py-3"
                        >
                        <div>
                            <p class="font-medium">{{ exercise.name }}</p>
                            <p class="font-mono text-xs text-muted-foreground">
                                {{ exercise.slug }} · {{ exercise.primary_muscle_group }}
                                <span v-if="exercise.secondary_muscle_group"> / {{ exercise.secondary_muscle_group }}</span>
                                <span v-if="exercise.equipment"> · {{ exercise.equipment }}</span>
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-sm text-destructive hover:underline"
                            @click="remove(exercise)"
                        >
                            Delete
                        </button>
                    </li>
                </ul>
                </Deferred>
            </div>
        </AdminLayout>
    </AppLayout>
</template>
