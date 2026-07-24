# Plan

Working backlog for OVRLOAD v2. Update this as items ship or get deferred. Domain language stays in `CONTEXT.md`; hard decisions stay in `docs/adr/`.

## Now (recently shipped)

- [x] v2 schema + domain models (grams, set-group `type`, mirror workout snapshots)
- [x] Domain-oriented `App\{Domain}\...` layout + mirrored tests
- [x] Routine editor (desktop dense list / mobile stage, zinc + lime)
- [x] Workout player MVP: start / resume / log sets / rest / setup / finish
- [x] One in-progress workout enforcement; deload start mode
- [x] Dashboard start/resume + empty-routine guards
- [x] Editor save: allow empty warm-up percents

## Next

Ordered for the core loop; reshuffle if product priority changes.

1. **Progression on finish** — ~~carry-forward highest achieved top weight; confirm bumps when progression target hit; skip both for deload workouts~~ done
2. **Mid-session structure edits** — ~~mutate the in-progress workout snapshot (not the routine) from the player~~ done (add/remove incomplete working sets)
3. **More app-like mobile behaviour** — ~~chrome polish: safe areas, player full-bleed (no AppLayout), leave confirm, overscroll off on player+editor~~ done (grill notes below; no PWA / bottom nav)
4. **User default warm-up %s and reps** — ~~prefs on the user; per-step %×reps on warm-up steps; seed into new blocks; Settings → Training~~ done
5. **Restyle whole app to match Overload branding** — ~~zinc + lime~~ done: dark-first near-black + neon yellow primary + cyan accent (`docs/branding.md`, `resources/css/app.css`)
6. **Find and import exercises** — ~~shared catalog JSON + `exercises:import` + seeder; editor find filter; index scoped to `forUser`~~ done (~80 lifts)
7. **Admin panel** — ~~thin Inertia admin: exercises, muscle groups, read-only users; sidebar link for admins~~ done
8. **Dead code audit (v1 leftovers)** — ~~JSON catalog APIs, unused MG update, starter UI packages, unused permission seeders~~ done
9. **Strip Laravel starter-kit UI** — ~~remove obvious Breeze/starter chrome and behaviours that still read as the stock kit~~ done (branded OVRLOAD home; dead search/footer/auth variants removed)
10. **Rebrand to OVRLOAD** — ~~rename product surfaces to **OVRLOAD**; mark/icon and related chrome around **OVR** / **OVRLD**~~ done (`docs/branding.md`; logos, home, auth, `APP_NAME`)
11. **Plate calculator UI** — plate profile + nearest loadable (schema exists; UI deferred)
12. **Flaky-network drafts** — best-effort offline/queue for player logging (later)

## Grill: app-like mobile behaviour

Decisions (2026-07-24), chrome-polish pass only:

- **Scope:** A — safe areas / full-bleed / leave guard / overscroll; not bottom nav, not PWA
- **Surfaces:** player + editor
- **Leave:** soft confirm on player (`beforeunload` + Inertia `before` when leaving `/workouts/{id}/*`); no history trap
- **Overscroll:** disabled on player + editor
- **Motion/haptics:** none this pass
- **Top chrome:** player chrome-minimal (no AppLayout); editor keeps AppLayout

Deferred: installable PWA, tabbed app shell, haptics.

## Parking lot

- Editable history of finished workouts
- Ad-hoc setup from player (beyond planned `has_setup_after`)
- Transition duration as a stored preference (today client-side for supersets)
- lb display/conversion end-to-end (API still kg-centric like the editor)
