<script setup lang="ts">
import { slugify } from '@/admin/lib/slugify';
import type { MuscleGroupRow } from '@/admin/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { confirmDialog } from '@/shared/lib/confirmDialog';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

defineProps<{
    muscle_groups: MuscleGroupRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Muscle groups', href: '/admin/muscle-groups' },
];

const mutating = ref(false);

const form = useForm({
    name: '',
    slug: '',
});

watch(
    () => form.name,
    (name) => {
        form.slug = slugify(name);
    },
);

const submit = () => {
    form.post(route('muscle-groups.store'), {
        onSuccess: () => form.reset(),
    });
};

const deleteForm = useForm({});

const remove = async (group: MuscleGroupRow) => {
    if (deleteForm.processing) {
        return;
    }
    const ok = await confirmDialog({
        title: `Delete muscle group “${group.name}”?`,
        confirmLabel: 'Delete',
        variant: 'destructive',
    });
    if (!ok) return;
    deleteForm.delete(route('muscle-groups.delete', group.id));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin · Muscle groups" />
        <AdminLayout>
            <HeadingSmall title="Muscle groups" description="Labels used when tagging shared exercises." />

            <form class="space-y-3 rounded-xl border border-border bg-card p-4" @submit.prevent="submit">
                <p class="text-sm font-medium">Add muscle group</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                        Name
                        <input v-model="form.name" class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground" required />
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
                </div>
                <button
                    type="submit"
                    class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Create
                </button>
            </form>

            <ul class="divide-y divide-border rounded-xl border border-border">
                <li v-for="group in muscle_groups" :key="group.id" class="flex items-center justify-between gap-2 px-4 py-3">
                    <div>
                        <p class="font-medium">{{ group.name }}</p>
                        <p class="font-mono text-xs text-muted-foreground">{{ group.slug }}</p>
                    </div>
                    <button
                        type="button"
                        class="text-sm text-destructive hover:underline disabled:opacity-50"
                        :disabled="deleteForm.processing"
                        @click="remove(group)"
                    >
                        Delete
                    </button>
                </li>
            </ul>
        </AdminLayout>
    </AppLayout>
</template>
