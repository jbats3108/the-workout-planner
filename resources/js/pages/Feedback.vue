<script setup lang="ts">
import BrandName from '@/components/BrandName.vue';
import InputError from '@/components/InputError.vue';
import PublicSiteHeader from '@/components/PublicSiteHeader.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useFlashSuccess } from '@/shared/composables/useFlashSuccess';
import { Form, Head, Link } from '@inertiajs/vue3';

const successMessage = useFlashSuccess();

const controlClass =
    'border-border bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] md:text-sm';

const textareaClass =
    'border-border bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 flex min-h-24 w-full rounded-md border px-3 py-2 text-base shadow-xs outline-none focus-visible:ring-[3px] md:text-sm';

const categories = [
    { value: 'bug_report', label: 'Bug Report' },
    { value: 'feature_request', label: 'Feature Request' },
    { value: 'general_feedback', label: 'General Feedback' },
] as const;
</script>

<template>
    <Head title="Submit feedback">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="public-form relative min-h-dvh bg-background text-foreground">
        <div class="public-form-atmosphere pointer-events-none absolute inset-0" aria-hidden="true" />

        <PublicSiteHeader current="beta-tester-faqs" />

        <main class="relative z-10 mx-auto w-full max-w-lg px-6 pb-20 sm:px-10">
            <p class="text-sm font-medium tracking-widest text-primary uppercase">Beta</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Submit feedback</h1>
            <p class="mt-3 text-muted-foreground">
                What’s working, what’s missing, or anything else about <BrandName />. See the
                <Link :href="route('privacy')" class="font-medium text-primary underline-offset-2 hover:underline">privacy policy</Link>
                for how this is stored.
            </p>

            <div v-if="successMessage" class="mt-6 rounded-md border border-primary/40 bg-primary/10 px-4 py-3 text-sm text-foreground" role="status">
                {{ successMessage }}
            </div>

            <Form :action="route('feedback.store')" method="post" class="mt-8 flex flex-col gap-5" #default="{ errors, processing }" reset-on-success>
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" name="name" type="text" required maxlength="255" autocomplete="name" autofocus />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" name="email" type="email" required maxlength="255" autocomplete="email" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="category">Category</Label>
                    <select id="category" name="category" required :class="controlClass">
                        <option value="" disabled selected>Select a category</option>
                        <option v-for="item in categories" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                    <InputError :message="errors.category" />
                </div>

                <div class="grid gap-2">
                    <Label for="message">Your feedback</Label>
                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        required
                        maxlength="5000"
                        :class="textareaClass"
                        placeholder="What worked, what didn’t, ideas…"
                    />
                    <InputError :message="errors.message" />
                </div>

                <!-- Honeypot: leave empty -->
                <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off" />
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground transition-opacity hover:opacity-90 disabled:opacity-50"
                    :disabled="processing"
                >
                    {{ processing ? 'Sending…' : 'Send feedback' }}
                </button>
            </Form>

            <p class="mt-8 text-sm text-muted-foreground">
                <Link :href="route('beta-tester-faqs')" class="underline-offset-2 hover:text-foreground hover:underline">← Back to Beta FAQs</Link>
            </p>
        </main>
    </div>
</template>

<style scoped>
.public-form-atmosphere {
    background:
        radial-gradient(ellipse 70% 40% at 50% -5%, color-mix(in oklab, var(--primary) 18%, transparent), transparent 70%),
        radial-gradient(ellipse 40% 30% at 90% 60%, color-mix(in oklab, var(--accent) 10%, transparent), transparent 65%);
}
</style>
