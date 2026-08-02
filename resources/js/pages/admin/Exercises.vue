<script setup lang="ts">
import AdminCreateCard from '@/admin/components/AdminCreateCard.vue';
import AdminNameSlugFields from '@/admin/components/AdminNameSlugFields.vue';
import AdminNamedList from '@/admin/components/AdminNamedList.vue';
import { useAdminDelete } from '@/admin/composables/useAdminDelete';
import { useSlugNamedForm } from '@/admin/composables/useSlugNamedForm';
import type { AdminExercise, EquipmentOption, MuscleGroupOption } from '@/admin/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { useCatalogFilter } from '@/shared/composables/useCatalogFilter';
import { type BreadcrumbItem } from '@/types';
import { Deferred, Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    exercises?: AdminExercise[];
    muscle_groups: MuscleGroupOption[];
    equipment_options: EquipmentOption[];
}>();

const catalog = computed(() => props.exercises ?? []);
const { query, filtered } = useCatalogFilter(catalog, (e) => [
    e.name,
    e.slug,
    e.primary_muscle_group,
    e.secondary_muscle_group ?? '',
    e.equipment ?? '',
]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Exercises', href: '/admin/exercises' },
];

const form = useSlugNamedForm({
    name: '',
    slug: '',
    primary_muscle_group: props.muscle_groups[0]?.slug ?? '',
    secondary_muscle_group: null as string | null,
    equipment: null as string | null,
});

const { deleteForm, destroy } = useAdminDelete();

const submit = () => {
    form.transform((data) => ({
        ...data,
        secondary_muscle_group: data.secondary_muscle_group || null,
        equipment: data.equipment || null,
    })).post(route('exercises.store'), {
        onSuccess: () => form.reset('name', 'slug', 'equipment'),
    });
};

const remove = (exercise: AdminExercise) => destroy(route('exercises.delete', exercise.id), `Delete “${exercise.name}” from the shared catalog?`);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin · Exercises" />
        <AdminLayout>
            <HeadingSmall title="Shared exercises" description="Catalog lifts available to every user." />

            <AdminCreateCard title="Add exercise" :processing="form.processing" @submit="submit">
                <AdminNameSlugFields
                    v-model:name="form.name"
                    v-model:slug="form.slug"
                    :name-error="form.errors.name"
                    :slug-error="form.errors.slug"
                />
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
                    <select v-model="form.equipment" class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground">
                        <option :value="null">Unspecified</option>
                        <option v-for="opt in equipment_options" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.equipment" />
                </label>
            </AdminCreateCard>

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
                    <AdminNamedList :items="filtered" :processing="deleteForm.processing" @delete="remove">
                        <template #meta="{ item: exercise }">
                            {{ exercise.slug }} · {{ exercise.primary_muscle_group }}
                            <span v-if="exercise.secondary_muscle_group"> / {{ exercise.secondary_muscle_group }}</span>
                            <span v-if="exercise.equipment"> · {{ exercise.equipment }}</span>
                        </template>
                    </AdminNamedList>
                </Deferred>
            </div>
        </AdminLayout>
    </AppLayout>
</template>
