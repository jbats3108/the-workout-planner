<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('routines.store'));
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'New routine', href: '#' },
        ]"
    >
        <Head title="New routine" />

        <div class="mx-auto flex w-full max-w-lg flex-1 flex-col px-4 py-10 text-foreground">
            <p class="text-xs tracking-[0.2em] text-muted-foreground uppercase">Routine</p>
            <h1 class="mt-2 text-2xl font-bold">Name your routine</h1>
            <p class="mt-2 text-sm text-muted-foreground">You can add blocks and exercises on the next screen.</p>

            <form class="mt-8 flex flex-col gap-6" @submit.prevent="submit">
                <label class="flex flex-col gap-2 text-sm text-muted-foreground">
                    Name
                    <input
                        v-model="form.name"
                        type="text"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground outline-none focus:border-primary"
                        placeholder="e.g. Push day"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.name" />
                </label>

                <div class="flex gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="rounded-full border border-border px-5 py-3 text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                        :disabled="form.processing || !form.name.trim()"
                    >
                        Continue
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
