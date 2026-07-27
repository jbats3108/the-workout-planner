import { vi } from 'vitest';
import { reactive } from 'vue';

const inertia = vi.hoisted(() => {
    const inertiaFormPut = vi.fn();
    const inertiaFormPost = vi.fn();
    const pageProps = {
        flash: { success: null as string | null, error: null as string | null },
        errors: {} as Record<string, string>,
    };
    const routerMocks = {
        delete: vi.fn(),
        post: vi.fn(),
        visit: vi.fn(),
        on: vi.fn(() => vi.fn()),
    };

    function createForm(initial: object) {
        return reactive({
            ...initial,
            processing: false,
            errors: {},
            recentlySuccessful: false,
            transform(fn: (data: object) => unknown) {
                fn(initial);
                return { put: inertiaFormPut, post: inertiaFormPost };
            },
            put: inertiaFormPut,
            post: inertiaFormPost,
            reset: vi.fn(),
        });
    }

    return {
        inertiaFormPut,
        inertiaFormPost,
        pageProps,
        routerMocks,
        createForm,
    };
});

vi.mock('@inertiajs/vue3', () => ({
    useForm: (keyOrInitial: string | object, maybeInitial?: object) =>
        inertia.createForm(typeof keyOrInitial === 'string' ? (maybeInitial ?? {}) : keyOrInitial),
    usePage: () => ({ props: inertia.pageProps }),
    router: inertia.routerMocks,
}));

(globalThis as typeof globalThis & { __inertiaMocks: typeof inertia }).__inertiaMocks = inertia;

vi.stubGlobal(
    'route',
    vi.fn((name: string, _params?: unknown) => `/${String(name)}`),
);
