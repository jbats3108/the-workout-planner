<script setup lang="ts">
import AdminCreateCard from '@/admin/components/AdminCreateCard.vue';
import AdminNameSlugFields from '@/admin/components/AdminNameSlugFields.vue';
import AdminNamedList from '@/admin/components/AdminNamedList.vue';
import { useAdminDelete } from '@/admin/composables/useAdminDelete';
import { useSlugNamedForm } from '@/admin/composables/useSlugNamedForm';
import type { MuscleGroupRow } from '@/admin/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    muscle_groups: MuscleGroupRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Muscle groups', href: '/admin/muscle-groups' },
];

const form = useSlugNamedForm({
    name: '',
    slug: '',
});

const { deleteForm, destroy } = useAdminDelete();

const submit = () => {
    form.post(route('muscle-groups.store'), {
        onSuccess: () => form.reset(),
    });
};

const remove = (group: MuscleGroupRow) => destroy(route('muscle-groups.delete', group.id), `Delete muscle group “${group.name}”?`);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin · Muscle groups" />
        <AdminLayout>
            <HeadingSmall title="Muscle groups" description="Labels used when tagging shared exercises." />

            <AdminCreateCard title="Add muscle group" :processing="form.processing" @submit="submit">
                <AdminNameSlugFields
                    v-model:name="form.name"
                    v-model:slug="form.slug"
                    :name-error="form.errors.name"
                    :slug-error="form.errors.slug"
                />
            </AdminCreateCard>

            <AdminNamedList :items="muscle_groups" :processing="deleteForm.processing" @delete="remove" />
        </AdminLayout>
    </AppLayout>
</template>
