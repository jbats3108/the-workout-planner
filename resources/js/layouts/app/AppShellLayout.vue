<script setup lang="ts">
import AppBottomNav from '@/components/AppBottomNav.vue';
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import ConfirmDialogHost from '@/shared/components/ConfirmDialogHost.vue';
import FlashAlerts from '@/shared/components/FlashAlerts.vue';
import PwaInstallBanner from '@/shared/components/PwaInstallBanner.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header">
        <div class="flex min-h-svh w-full flex-col md:flex-row">
            <AppSidebar />
            <div class="flex min-w-0 flex-1 flex-col pb-[calc(3.5rem+env(safe-area-inset-bottom,0px))] md:pb-0">
                <AppHeader :breadcrumbs="breadcrumbs" />
                <AppContent>
                    <FlashAlerts class="px-4 pt-4 sm:px-6 lg:px-8" />
                    <slot />
                </AppContent>
            </div>
        </div>
        <AppBottomNav />
        <PwaInstallBanner />
        <ConfirmDialogHost />
    </AppShell>
</template>
