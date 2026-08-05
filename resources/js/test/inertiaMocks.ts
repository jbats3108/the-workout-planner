type InertiaMocks = {
    inertiaFormPut: ReturnType<typeof import('vitest').vi.fn>;
    inertiaFormPost: ReturnType<typeof import('vitest').vi.fn>;
    lastTransformed: unknown;
    clearLastTransformed: () => void;
    pageProps: {
        flash: { success: string | null; error: string | null };
        errors: Record<string, string>;
    };
    routerMocks: {
        delete: ReturnType<typeof import('vitest').vi.fn>;
        post: ReturnType<typeof import('vitest').vi.fn>;
        visit: ReturnType<typeof import('vitest').vi.fn>;
        on: ReturnType<typeof import('vitest').vi.fn>;
    };
};

export function inertiaMocks(): InertiaMocks {
    return (globalThis as typeof globalThis & { __inertiaMocks: InertiaMocks }).__inertiaMocks;
}
