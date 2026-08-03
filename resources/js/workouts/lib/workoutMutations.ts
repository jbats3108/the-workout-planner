import { router } from '@inertiajs/vue3';
import type { Ref } from 'vue';

type MutatingRef = Ref<boolean>;

export type WorkoutMutationOptions = {
    mutating: MutatingRef;
    leaveConfirmed?: MutatingRef;
    confirm?: () => Promise<boolean>;
};

export async function finishWorkout(workoutId: string, options: WorkoutMutationOptions): Promise<void> {
    if (options.mutating.value) {
        return;
    }

    if (options.confirm) {
        const ok = await options.confirm();
        if (!ok) {
            return;
        }
    }

    options.mutating.value = true;
    if (options.leaveConfirmed) {
        options.leaveConfirmed.value = true;
    }

    router.post(
        route('workouts.finish', workoutId),
        {},
        {
            onFinish: () => {
                options.mutating.value = false;
            },
            onError: () => {
                if (options.leaveConfirmed) {
                    options.leaveConfirmed.value = false;
                }
            },
        },
    );
}

export async function abandonWorkout(workoutId: string, options: WorkoutMutationOptions): Promise<void> {
    if (options.mutating.value) {
        return;
    }

    if (options.confirm) {
        const ok = await options.confirm();
        if (!ok) {
            return;
        }
    }

    options.mutating.value = true;
    if (options.leaveConfirmed) {
        options.leaveConfirmed.value = true;
    }

    router.post(
        route('workouts.discard', workoutId),
        {},
        {
            onFinish: () => {
                options.mutating.value = false;
            },
            onError: () => {
                if (options.leaveConfirmed) {
                    options.leaveConfirmed.value = false;
                }
            },
        },
    );
}
