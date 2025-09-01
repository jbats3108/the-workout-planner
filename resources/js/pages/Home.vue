<script setup lang="ts">
import DarkModeToggle from '@/components/DarkModeToggle.vue';
import HomepageFeatureCallout from '@/components/HomepageFeatureCallout.vue';
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
                        <Link href="login" class="rounded-md bg-accent px-4 py-2 text-accent-foreground transition-opacity hover:opacity-90"
                            >Get Started
                        </Link>
                        <DarkModeToggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative z-10 py-8 sm:py-16 md:py-20 lg:max-w-2xl lg:py-28">
                    <h2 class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl">Ready to plan your workouts?</h2>
                    <p class="mt-6 text-xl text-muted-foreground">
                        The Workout Planner is the premier platform to create, plan and track your workouts online.
                    </p>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-card-foreground">Features</h2>
                    <p class="mt-4 text-muted-foreground">Explore The Workout Planner</p>
                </div>
                <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <HomepageFeatureCallout>
                        <template v-slot:header>Custom Routine Creation</template>
                        <div>Create Full Body routines, or customise your split</div>
                    </HomepageFeatureCallout>
                    <HomepageFeatureCallout>
                        <template v-slot:header>Workout Tracking</template>
                        <div>Log your workouts and track your progress</div>
                    </HomepageFeatureCallout>
                    <HomepageFeatureCallout>
                        <template v-slot:header>Something else</template>
                        <div>When I think of another feature, I'll put a blurb here</div>
                    </HomepageFeatureCallout>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-primary py-16 text-primary-foreground">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold">Time to start planning?</h2>
                <p class="mt-4 text-lg opacity-90">Click here to sign up</p>
                <button class="mt-8 rounded-md bg-accent px-8 py-3 text-accent-foreground transition-opacity hover:opacity-90">
                    <Link href="login">Get Planning!</Link>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-border">
            <div v-show="false" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
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
