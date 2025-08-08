<script setup lang="ts">
import DarkModeToggle from '@/pages/DarkModeToggle.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="border-b border-border">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-primary">The Workout Planner</h1>
                    </div>
                    <div class="hidden items-center space-x-8 sm:flex">
                        <Link href="/login" class="rounded-md bg-accent px-4 py-2 text-accent-foreground transition-opacity hover:opacity-90"
                            >Get Started</Link
                        >
                        <DarkModeToggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative z-10 py-8 sm:py-16 md:py-20 lg:max-w-2xl lg:py-28">
                    <h2 class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl">Lorem Ipsum Dolor Sit Amet</h2>
                    <p class="mt-6 text-xl text-muted-foreground">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                    <div class="mt-10 flex gap-4">
                        <button class="rounded-md bg-primary px-6 py-3 text-primary-foreground transition-opacity hover:opacity-90">
                            Lorem Ipsum
                        </button>
                        <button
                            class="rounded-md border border-border px-6 py-3 text-foreground transition-colors hover:bg-secondary hover:text-secondary-foreground"
                        >
                            Dolor Sit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-card-foreground">Lorem Ipsum</h2>
                    <p class="mt-4 text-muted-foreground">Lorem ipsum dolor sit amet consectetur adipiscing elit</p>
                </div>
                <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-border p-6 transition-colors hover:border-primary">
                        <h3 class="text-xl font-semibold text-card-foreground">Lorem Ipsum</h3>
                        <p class="mt-2 text-muted-foreground">Sed do eiusmod tempor incididunt ut labore</p>
                    </div>
                    <div class="rounded-lg border border-border p-6 transition-colors hover:border-primary">
                        <h3 class="text-xl font-semibold text-card-foreground">Dolor Sit</h3>
                        <p class="mt-2 text-muted-foreground">Ut enim ad minim veniam quis nostrud</p>
                    </div>
                    <div class="rounded-lg border border-border p-6 transition-colors hover:border-primary">
                        <h3 class="text-xl font-semibold text-card-foreground">Amet Consectetur</h3>
                        <p class="mt-2 text-muted-foreground">Duis aute irure dolor in reprehenderit</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-accent py-16 text-accent-foreground">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold">Lorem Ipsum Dolor Sit?</h2>
                <p class="mt-4 text-lg opacity-90">Consectetur adipiscing elit sed do eiusmod tempor</p>
                <button class="mt-8 rounded-md bg-primary px-8 py-3 text-primary-foreground transition-opacity hover:opacity-90">Lorem Ipsum</button>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-border">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Lorem</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Ipsum</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Dolor</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Sit</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Amet</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Consectetur</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Adipiscing</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Elit</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Sed</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Eiusmod</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Tempor</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary">Incididunt</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
