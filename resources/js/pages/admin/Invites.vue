<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type InviteRow = {
    id: number;
    note: string | null;
    role: string;
    url: string;
    created_by: string | null;
    created_at: string | null;
    expires_at: string | null;
    used_at: string | null;
    used_by: string | null;
    revoked_at: string | null;
    usable: boolean;
};

const props = defineProps<{
    invites: InviteRow[];
    master_enabled: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Invites', href: '/admin/invites' },
];

const page = usePage();
const flashUrl = computed(() => (page.props.flash as { invite_url?: string | null })?.invite_url ?? null);
const copiedId = ref<number | 'flash' | null>(null);

const form = useForm({
    note: '',
    role: 'user',
    expires_in_days: 7 as number | null,
});

const submit = () => {
    form.post(route('admin.invites.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('note'),
    });
};

const copyUrl = async (url: string, id: number | 'flash') => {
    await navigator.clipboard.writeText(url);
    copiedId.value = id;
    window.setTimeout(() => {
        if (copiedId.value === id) copiedId.value = null;
    }, 2000);
};

const mailtoHref = (url: string) => {
    const subject = encodeURIComponent('Your OVRLOAD registration link');
    const body = encodeURIComponent(`Use this link to create your account:\n\n${url}\n`);
    return `mailto:?subject=${subject}&body=${body}`;
};

const revoke = (id: number) => {
    if (!confirm('Revoke this invite?')) return;
    router.post(route('admin.invites.revoke', id), {}, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin · Invites" />
        <AdminLayout>
            <HeadingSmall
                title="Registration invites"
                description="Create one-time links to send. Local leave REGISTRATION_INVITE empty so only these links work."
            />

            <p v-if="master_enabled" class="rounded-xl border border-border bg-card px-4 py-3 text-sm text-muted-foreground">
                Master <code class="font-mono text-xs">REGISTRATION_INVITE</code> is set. Prefer one-time invites below for sharing.
            </p>
            <p v-else class="text-sm text-muted-foreground">
                Master env invite is off (good for local). Only admin-created links below can register.
            </p>

            <div
                v-if="flashUrl"
                class="rounded-xl border border-primary/40 bg-primary/5 px-4 py-3"
            >
                <p class="text-sm font-medium">New invite link</p>
                <p class="mt-1 break-all font-mono text-xs text-muted-foreground">{{ flashUrl }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Button type="button" size="sm" @click="copyUrl(flashUrl, 'flash')">
                        {{ copiedId === 'flash' ? 'Copied' : 'Copy' }}
                    </Button>
                    <Button type="button" size="sm" variant="outline" as-child>
                        <a :href="mailtoHref(flashUrl)">Email</a>
                    </Button>
                </div>
            </div>

            <form class="space-y-3 rounded-xl border border-border p-4" @submit.prevent="submit">
                <p class="text-sm font-semibold">Create invite</p>
                <label class="block text-xs text-muted-foreground">
                    Note (optional)
                    <input
                        v-model="form.note"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        placeholder="For Jamie’s gym buddy"
                    />
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs text-muted-foreground">
                        Role
                        <select
                            v-model="form.role"
                            class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        >
                            <option value="user">user</option>
                            <option value="admin">admin</option>
                        </select>
                    </label>
                    <label class="block text-xs text-muted-foreground">
                        Expires (days)
                        <input
                            v-model.number="form.expires_in_days"
                            type="number"
                            min="1"
                            max="365"
                            class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        />
                    </label>
                </div>
                <Button type="submit" :disabled="form.processing">Create link</Button>
            </form>

            <ul class="divide-y divide-border rounded-xl border border-border">
                <li v-for="invite in props.invites" :key="invite.id" class="space-y-2 px-4 py-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium">
                                {{ invite.note || 'Invite' }}
                                <span class="font-mono text-xs text-muted-foreground">· {{ invite.role }}</span>
                            </p>
                            <p class="text-xs text-muted-foreground">
                                by {{ invite.created_by ?? '?' }}
                                <span v-if="invite.created_at"> · {{ invite.created_at }}</span>
                                <span v-if="invite.expires_at"> · expires {{ invite.expires_at }}</span>
                            </p>
                            <p v-if="invite.used_at" class="text-xs text-muted-foreground">
                                used {{ invite.used_at }}
                                <span v-if="invite.used_by"> by {{ invite.used_by }}</span>
                            </p>
                            <p v-else-if="invite.revoked_at" class="text-xs text-destructive">
                                revoked {{ invite.revoked_at }}
                            </p>
                            <p v-else-if="invite.usable" class="text-xs text-primary">usable</p>
                            <p v-else class="text-xs text-muted-foreground">expired</p>
                        </div>
                        <div v-if="invite.usable" class="flex flex-wrap gap-2">
                            <Button type="button" size="sm" variant="outline" @click="copyUrl(invite.url, invite.id)">
                                {{ copiedId === invite.id ? 'Copied' : 'Copy' }}
                            </Button>
                            <Button type="button" size="sm" variant="outline" as-child>
                                <a :href="mailtoHref(invite.url)">Email</a>
                            </Button>
                            <Button type="button" size="sm" variant="ghost" @click="revoke(invite.id)">Revoke</Button>
                        </div>
                    </div>
                </li>
                <li v-if="!props.invites.length" class="px-4 py-8 text-center text-sm text-muted-foreground">
                    No invites yet.
                </li>
            </ul>
        </AdminLayout>
    </AppLayout>
</template>
