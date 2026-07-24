<script setup lang="ts">
import DarkModeToggle from '@/components/DarkModeToggle.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="OVRLOAD" />

    <div class="home relative flex min-h-dvh flex-col overflow-hidden bg-background text-foreground">
        <div class="home-atmosphere pointer-events-none absolute inset-0" aria-hidden="true" />

        <header class="relative z-10 flex items-center justify-end px-6 py-5 sm:px-10">
            <DarkModeToggle />
        </header>

        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 pb-16 text-center sm:px-10">
            <h1 class="home-brand text-6xl font-bold tracking-wide sm:text-7xl md:text-8xl lg:text-9xl">
                <span class="text-primary">OVR</span><span>LOAD</span>
            </h1>

            <p class="home-line mt-6 max-w-md text-base text-muted-foreground sm:text-lg">
                Progressive strength. Planned, logged, overloaded.
            </p>

            <div class="home-cta mt-10 flex w-full max-w-sm flex-col gap-3 sm:max-w-none sm:flex-row sm:justify-center">
                <Link
                    :href="route('register')"
                    class="rounded-md bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground transition-opacity hover:opacity-90"
                >
                    Create account
                </Link>
                <Link
                    :href="route('login')"
                    class="rounded-md px-8 py-3 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    Log in
                </Link>
            </div>
        </main>
    </div>
</template>

<style scoped>
.home-atmosphere {
    background:
        radial-gradient(ellipse 80% 55% at 50% -10%, color-mix(in oklab, var(--primary) 28%, transparent), transparent 70%),
        radial-gradient(ellipse 50% 40% at 85% 80%, color-mix(in oklab, var(--accent) 14%, transparent), transparent 65%),
        radial-gradient(ellipse 45% 35% at 10% 70%, color-mix(in oklab, var(--primary) 8%, transparent), transparent 60%);
    animation: home-atmosphere-drift 18s ease-in-out infinite alternate;
}

.home-brand {
    animation: home-rise 0.7s ease-out both;
}

.home-line {
    animation: home-rise 0.7s ease-out 0.12s both;
}

.home-cta {
    animation: home-rise 0.7s ease-out 0.24s both;
}

@keyframes home-rise {
    from {
        opacity: 0;
        transform: translateY(0.75rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes home-atmosphere-drift {
    from {
        transform: scale(1) translate(0, 0);
    }
    to {
        transform: scale(1.06) translate(-1.5%, 1%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .home-atmosphere,
    .home-brand,
    .home-line,
    .home-cta {
        animation: none;
    }
}
</style>
