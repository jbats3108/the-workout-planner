<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const form = useForm({
    name: {
        required: true,
        value: '',
        message: 'Routine Type Name is Required',
        trigger: 'change',
        type: 'text',
        label: 'Routine Type Name',
        placeholder: 'Routine Type Name',
    },
});

const submitForm = () => {
    const name = form.name.value.trim();
    const slug = name.replace(/\s+/g, '-').toLowerCase();

    const payload = {
        name,
        slug,
    };

    router.post('/routine-types/create', payload);

    form.reset();
};
</script>

<template>
    <Head title="Create a Routine Type" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <h1 class="m-3 rounded-2xl border-2 p-2 text-xl">Add a new Routine Type</h1>
        <div class="flex flex-row gap-4">
            <form @submit.prevent="submitForm" class="flex flex-col gap-4">
                <input type="text" v-model="form.name.value" class="border-2 p-2" :placeholder="form.name.placeholder" />
                <button type="submit" class="border-2 p-2" :disabled="form.processing">Create</button>
            </form>
        </div>
    </AppLayout>
</template>
