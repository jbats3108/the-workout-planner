<template>
    <div class="rounded-lg border border-border p-6 transition-colors hover:border-primary">
        <h3 class="text-xl font-semibold text-card-foreground"><slot name="header"></slot></h3>
        <p class="mt-2 text-muted-foreground"><slot/></p>
    </div>
</template>
