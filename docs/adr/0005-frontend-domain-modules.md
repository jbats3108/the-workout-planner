# Frontend domain modules

Inertia pages stay thin entrypoints under `resources/js/pages/`. Domain TypeScript types, pure helpers, composable stores, and presentational SFCs live under `resources/js/{domain}/` (e.g. `routines/`, `workouts/`, `settings/`), mirroring PHP `App\{Domain}`. App chrome types stay in `resources/js/types/`; UI kit and shell stay in `resources/js/components/`. Cross-domain pieces go under `resources/js/shared/` when introduced.

Page session state (editor form, player focus/rest) uses create + provide/inject composable stores — not Pinia and not module-level singletons — so child SFCs call `useRoutineEditor` / `useWorkoutPlayer` instead of prop-drilling shared state. Pure transforms live in `{domain}/lib/`.
