<script setup lang="ts">
import BrandName from '@/components/BrandName.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="text-lg hover:opacity-90">
                        <BrandName />
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
                <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm text-muted-foreground" aria-label="Site">
                    <Link :href="route('home')" class="underline-offset-4 hover:text-foreground hover:underline">Home</Link>
                    <Link :href="route('beta-tester-faqs')" class="underline-offset-4 hover:text-foreground hover:underline">Beta testers</Link>
                    <Link :href="route('privacy')" class="underline-offset-4 hover:text-foreground hover:underline">Privacy</Link>
                </nav>
            </div>
        </div>
    </div>
</template>
