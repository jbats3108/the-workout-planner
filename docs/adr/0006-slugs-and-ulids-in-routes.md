# Slugs for routines, ULIDs for workouts in routes

Route model binding for **Routine** uses a per-user **slug**; binding for **Workout** uses a **ULID**. Numeric `id` stays the primary key and foreign-key target everywhere else. Nested resources (`WorkoutSet`, `WorkoutBlock`, etc.) keep integer ids in URLs.

## Context

All authenticated resource routes today bind by auto-increment `id` (e.g. `/routines/3/edit`, `/workouts/42/play`). Exercises and muscle groups already have slugs for catalog identity but routes still bind by id.

Goals:

- Readable, stable routine URLs (`/routines/barbell-strength/edit`).
- Non-sequential, non-guessable workout URLs (`/workouts/01J…/play`).
- No churn to foreign keys, bump records, or nested route segments.

Non-goals:

- Slugs for workouts (no meaningful name to derive from).
- Replacing integer primary keys.
- Public share links or SEO (app is auth-gated).
- Redirects from legacy numeric URLs (private app; bookmarks are rare).

## Decision

| Resource | Route key | Column | Uniqueness | Rename behaviour |
|----------|-----------|--------|------------|------------------|
| **Routine** | `slug` | `routines.slug` | `unique(user_id, slug)` | **Immutable** after create (name edits do not change slug) |
| **Workout** | `ulid` | `workouts.ulid` | `unique` globally | N/A (opaque, never edited) |
| Sets, blocks, invites, etc. | `id` | unchanged | unchanged | unchanged |

### Routine slugs

- Generated once on create from `Str::slug($name)` with numeric suffix on collision (`barbell-strength`, `barbell-strength-2`, …) within the owning user.
- `Routine` uses `HasSlug` and overrides `getRouteKeyName(): 'slug'`.
- **Scoped route binding:** non-admin users resolve only their own routines (`where user_id = auth id`). Admins resolve globally (existing policy already lets admins view/delete any routine).
- History filter query `?routine=` switches from numeric id to slug.

### Workout ULIDs

- New nullable-then-backfilled `ulid` column (`char(26)`), unique index.
- Assigned in `creating` observer: `(string) Str::ulid()`.
- `Workout` overrides `getRouteKeyName(): 'ulid'`.
- **Scoped route binding:** always `where user_id = auth id` (no admin bypass on workouts today).
- Integer `id` remains on DTOs only where needed internally; the value exposed to the frontend for `route()` calls becomes the ULID string (see DTO changes below).

### Out of scope (this ADR)

- Exercise / muscle-group delete routes (admin-only; slugs exist but low payoff).
- ULIDs or slugs for nested `{set}` / `{block}` segments.

## DTO / frontend contract

Keep integer ids for database relations in PHP. Change **page props used in `route()`** to route keys:

| DTO | Add / change | Used for |
|-----|--------------|----------|
| `RoutineData` | `slug: string` | dashboard cards, delete, start workout |
| `RoutineEditorPageData` | `slug: string` | update, delete |
| `HistoryWorkoutItemData` | `id` becomes `string` (ULID) | history index/show/destroy |
| `WorkoutPlayerPageData` | `id` becomes `string` (ULID) | all player actions |
| `ProgressionPageData` (or equivalent) | `workout_id` → ULID string | progression apply/skip |
| `HistoryIndexPageData` routine filter | `id` → `slug` in filter list | `?routine=` query |

Frontend: every `route('routines.*', …)` passes `slug`; every `route('workouts.*', …)` and `route('history.*', …)` passes ULID. No mixed id/ulid in the same route helper call.

TypeScript types under `resources/js/{domain}/types/` updated to match.

## Implementation plan

Estimated total: **~5–6 hours** (migrations, binding, DTOs, frontend, tests).

### Phase 1 — Schema & backfill (~1 hr)

1. **Migration: `routines.slug`**
   - `string`, not null after backfill.
   - Unique index on `(user_id, slug)`.
   - Data migration: for each routine, `Str::slug($name)` with `-2`, `-3`, … suffix per user on collision.

2. **Migration: `workouts.ulid`**
   - `char(26)` or `string(26)`, unique, not null after backfill.
   - Data migration: assign `Str::ulid()` to every existing row.

3. **Factories**
   - `RoutineFactory`: auto-generate unique slug per user.
   - `WorkoutFactory`: set `ulid` on create.

### Phase 2 — Models & binding (~1 hr)

4. **`Routine` model**
   - `use HasSlug`; add `slug` to `$fillable`.
   - `getRouteKeyName(): 'slug'`.
   - `resolveRouteBinding()`: scope to `user_id` unless `auth()->user()?->isAdmin()`.
   - Slug generation service or static helper: `RoutineSlugGenerator::forUser(User, string $name): string`.

5. **`StoreRoutineController` / `RoutineEditorService`**
   - On create only: set slug via generator (not on name update).

6. **`Workout` model**
   - `booted()` creating hook: assign `ulid` if empty.
   - `getRouteKeyName(): 'ulid'`.
   - `resolveRouteBinding()`: scope to `user_id`.

7. **`IndexWorkoutHistoryController`**
   - Resolve `?routine=` by slug (scoped to user) instead of integer id.

### Phase 3 — DTOs (~45 min)

8. Update DTOs listed in **DTO / frontend contract** — expose `slug` on routines; expose ULID as the `id` string on workout page props (or add explicit `ulid` and migrate frontend in one pass; prefer single field used in routes to avoid duplicate identifiers in templates).

9. **Redirects after write actions** — controllers already `route('routines.edit', $routine)` etc.; implicit route key resolution picks up slug/ulid automatically once models are updated.

### Phase 4 — Frontend (~45 min)

10. **`resources/js/`** — update `route()` call sites (known files):
    - `pages/Dashboard.vue`
    - `routines/components/RoutineCard.vue`
    - `routines/composables/useRoutineEditor.ts`
    - `workouts/composables/useWorkoutPlayer.ts`
    - `pages/workouts/Progression.vue`
    - `pages/history/Index.vue`
    - `pages/history/Show.vue`

11. **TypeScript types** — routine `slug`, workout `id: string` (ULID).

12. Regenerate Ziggy (`php artisan ziggy:generate`) if needed locally.

### Phase 5 — Tests (~1.5 hr)

13. **New unit tests**
    - `RoutineSlugGeneratorTest`: collision suffix, empty-name edge, unicode/transliteration.
    - `Routine` / `Workout` route binding: owner resolves, other user 404, admin resolves any routine.

14. **Update feature tests** — all `route('routines.*', $routine)` and workout/history routes still pass model instances (Laravel resolves route key); add explicit assertions that generated URLs contain slug/ulid not numeric id where useful.

15. **Soft-fail tests** (`SoftFailTest`) — use invalid slug / invalid ULID instead of `999_999`.

16. Run targeted suites:
    ```bash
    php artisan test --compact tests/Feature/Routines/
    php artisan test --compact tests/Feature/Workouts/
    php artisan test --compact tests/Feature/Shared/Http/SoftFailTest.php
    ```

### Phase 6 — Finish (~15 min)

17. `vendor/bin/pint --dirty --format agent`
18. Update `docs/plan.md` backlog item to point at this ADR.
19. Manual smoke: create routine → URL has slug; start workout → play URL has ULID; history filter by routine slug.

## Risks & mitigations

| Risk | Mitigation |
|------|------------|
| Slug collision on backfill | Suffix loop in migration (same as create-time generator) |
| Admin cannot open another user’s routine by slug | Admin-unscoped `resolveRouteBinding` on `Routine` |
| Broken bookmarks to `/routines/3` | Accepted (non-goal); document in PR |
| Frontend still passes numeric id | Typecheck + feature tests assert URL shape |
| SQLite vs Postgres ULID column length | Use `string(26)`; both drivers fine |

## Alternatives considered

- **Slug for workouts** — rejected; no stable human meaning.
- **ULID as workout primary key** — rejected; touches every `workout_id` FK and bump-record relation.
- **Slug regenerates on routine rename** — rejected; breaks bookmarks and history filter links; immutable slug is simpler.
- **Do nothing (keep ids)** — valid for a private app; chose readability + opaque workout ids for polish and enumeration hygiene.
