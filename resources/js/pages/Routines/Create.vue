<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { RoutineType } from '@/types/workouts';
import { useForm, Head, router } from '@inertiajs/vue3';

const props = defineProps<{
    routine_types: RoutineType[];
}>();

const form = useForm({
    routineName: {
        value: '',
        required: true,
        message: 'Routine name is required',
        trigger: 'blur',
        type: 'text',
        label: 'Routine Name',
        placeholder: 'Routine Name',
    },
    routineType: {
        value: '',
        required: true,
        message: 'Routine type is required',
        trigger: 'blur',
        type: 'select',
        label: 'Routine Type',
        placeholder: 'Routine Type',
        options: props.routine_types.map((type: RoutineType) => ({
            label: type.name,
            value: type.slug,
        })),
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const submitForm = () => {
    const payload = {
        name: form.routineName.value,
        routine_type: form.routineType.value,
    };

    router.post('/routines/create', payload);

    form.reset()
};
</script>

<template>
    <Head title="Create a Routine" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <h1 class="m-3 rounded-2xl border-2 p-2 text-xl">Create a new Routine</h1>
        <div class="flex flex-row gap-4">
            <form @submit.prevent="submitForm" class="flex flex-col gap-4">
                <input type="text" v-model="form.routineName.value" class="border-2 p-2" :placeholder="form.routineName.placeholder" />
                <select v-model="form.routineType.value" class="border-2 p-2">
                    <option value="">Select a type</option>
                    <option v-for="type in routine_types" :key="type.slug" :value="type.slug">{{ type.name }}</option>
                </select>
                <button type="submit" class="border-2 p-2" :disabled="form.processing">Create</button>
            </form>
        </div>
    </AppLayout>
</template>
