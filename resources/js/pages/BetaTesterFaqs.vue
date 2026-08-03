<script setup lang="ts">
import DarkModeToggle from '@/components/DarkModeToggle.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    interestFormUrl: string | null;
    feedbackFormUrl: string | null;
}>();

const faqs = [
    {
        id: 'pricing',
        question: '[Placeholder: pricing / early adopters]',
        answer: '[Placeholder answer]',
    },
    {
        id: 'what-it-is',
        question: "[Placeholder: what OVRLOAD is and isn't]",
        answer: '[Placeholder answer]',
    },
    {
        id: 'beta',
        question: '[Placeholder: beta status and missing features]',
        answer: '[Placeholder answer]',
    },
    {
        id: 'support',
        question: '[Placeholder: Ko-fi / supporting the project]',
        answer: '[Placeholder answer]',
    },
    {
        id: 'privacy',
        question: '[Placeholder: data, AI training, advertisers]',
        answer: '[Placeholder answer]',
    },
    {
        id: 'not-training',
        question: '[Placeholder: not a training / coaching app]',
        answer: '[Placeholder answer]',
    },
] as const;

const backlogItems = [
    '[Placeholder: Tutorial / welcome guide]',
    '[Placeholder: Post-hoc workout record]',
    '[Placeholder: Switch exercise for deload]',
    '[Placeholder: Strava integration]',
    '[Placeholder: Garmin sync]',
    '[Placeholder: Per-exercise strength-over-time]',
    '[Placeholder: History extensions]',
    '[Placeholder: Demote dropset → single in Play]',
    '[Placeholder: Ad-hoc setup from player]',
    '[Placeholder: Transition duration preference]',
    '[Placeholder: lb display/conversion]',
    '[Placeholder: Dropsets on supersets]',
    '[Placeholder: Gym dumbbell / rack inventory]',
    '[Placeholder: Flaky-network drafts]',
] as const;
</script>

<template>
    <Head title="Beta Tester FAQs">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="faq relative min-h-dvh bg-background text-foreground">
        <div class="faq-atmosphere pointer-events-none absolute inset-0" aria-hidden="true" />

        <header class="relative z-10 flex items-center justify-between px-6 py-5 sm:px-10">
            <p class="font-semibold tracking-wide"><span class="text-primary">OVR</span><span>LOAD</span></p>
            <DarkModeToggle />
        </header>

        <main class="relative z-10 mx-auto w-full max-w-2xl px-6 pb-20 sm:px-10">
            <p class="text-sm font-medium tracking-widest text-primary uppercase">Beta Testers</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">FAQs</h1>
            <p class="mt-3 text-muted-foreground">[Placeholder: short intro — this page is shared with people who may want a beta invite]</p>

            <section class="mt-12 space-y-8" aria-labelledby="faq-heading">
                <h2 id="faq-heading" class="sr-only">Frequently asked questions</h2>

                <article v-for="faq in faqs" :key="faq.id" class="border-t border-border pt-6">
                    <h3 class="text-lg font-semibold">{{ faq.question }}</h3>
                    <p class="mt-2 text-muted-foreground">{{ faq.answer }}</p>
                </article>
            </section>

            <section class="mt-12 border border-border bg-card/40 px-5 py-6 sm:px-6" aria-labelledby="interest-heading">
                <h2 id="interest-heading" class="text-xl font-bold tracking-tight">Register your interest</h2>
                <p class="mt-2 text-sm text-muted-foreground">[Placeholder: ask for an invite via the form — no email thread to track]</p>
                <p class="mt-4">
                    <a
                        v-if="interestFormUrl"
                        :href="interestFormUrl"
                        class="inline-flex rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition-opacity hover:opacity-90"
                        rel="noopener noreferrer"
                        target="_blank"
                    >
                        Request an invite
                    </a>
                    <span v-else class="text-sm text-muted-foreground">[Interest form link TBD]</span>
                </p>
            </section>

            <section class="mt-14 border-t border-border pt-10" aria-labelledby="backlog-heading">
                <h2 id="backlog-heading" class="text-2xl font-bold tracking-tight">Current backlog</h2>
                <p class="mt-3 text-muted-foreground">[Placeholder: brief note that this is what's on the radar, not a commitment]</p>

                <ul class="mt-6 list-disc space-y-2 pl-5 text-muted-foreground">
                    <li v-for="item in backlogItems" :key="item">{{ item }}</li>
                </ul>

                <p class="mt-8 text-foreground">
                    [Placeholder: invite feedback and suggestions.]
                    <template v-if="feedbackFormUrl">
                        <a
                            :href="feedbackFormUrl"
                            class="ml-1 font-medium text-primary underline-offset-4 hover:underline"
                            rel="noopener noreferrer"
                            target="_blank"
                        >
                            Submit feedback
                        </a>
                    </template>
                    <span v-else class="ml-1 text-muted-foreground">[Feedback form link TBD]</span>
                </p>
            </section>
        </main>
    </div>
</template>

<style scoped>
.faq-atmosphere {
    background:
        radial-gradient(ellipse 70% 40% at 50% -5%, color-mix(in oklab, var(--primary) 18%, transparent), transparent 70%),
        radial-gradient(ellipse 40% 30% at 90% 60%, color-mix(in oklab, var(--accent) 10%, transparent), transparent 65%);
}
</style>
