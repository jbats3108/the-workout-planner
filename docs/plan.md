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

1. **Progression on finish** — carry-forward highest achieved top weight; confirm bumps when progression target hit; skip both for deload workouts (`CONTEXT.md`)
2. **Mid-session structure edits** — mutate the in-progress workout snapshot (not the routine) from the player
3. **More app-like mobile behaviour** — **grill before building** (see below)
4. **User default warm-up %s and reps** — prefs on the user (with per-exercise overrides already on blocks); seed sensible defaults into new routine blocks / editor empty state
5. **Restyle whole app to match Overload branding** — ~~zinc + lime~~ done: dark-first near-black + neon yellow primary + cyan accent (`docs/branding.md`, `resources/css/app.css`)
6. **Find and import exercises** — expand shared catalog (seed/import path); no fancy CMS in v1
7. **Admin panel** — admin-only surfaces for shared catalog / users as needed (keep thin)
8. **Dead code audit (v1 leftovers)** — remove unused controllers, pages, routes, ziggy ghosts, factories, and migrations residue from the pre-v2 gym schema
9. **Strip Laravel starter-kit UI** — remove obvious Breeze/starter chrome and behaviours that still read as the stock kit (generic nav/footer links, placeholder home copy, appearance leftovers, etc.)
10. **Rebrand to OVRLOAD** — ~~rename product surfaces to **OVRLOAD**; mark/icon and related chrome around **OVR** / **OVRLD**~~ done (`docs/branding.md`; logos, home, auth, `APP_NAME`)
11. **Plate calculator UI** — plate profile + nearest loadable (schema exists; UI deferred)
12. **Flaky-network drafts** — best-effort offline/queue for player logging (later)

## Grill: app-like mobile behaviour

Trigger a grilling session before implementing. Probe at least:

- What “app-like” means here vs a mobile website (installability, full-bleed chrome, gesture edges, bottom nav, safe areas)
- Player vs editor vs dashboard: one mobile shell or surface-specific chrome?
- Navigation: keep Laravel/Inertia URLs vs a tabbed app shell with fewer full page reloads
- System UI: status bar / notch, pull-to-refresh, prevent accidental back while in-progress
- Motion and haptics budget (intentional, not noisy)
- Whether PWA / home-screen install is in scope for this pass

Capture decisions in an ADR only if the outcome is hard to reverse (e.g. PWA vs not, dedicated mobile shell).

## Parking lot

- Editable history of finished workouts
- Ad-hoc setup from player (beyond planned `has_setup_after`)
- Transition duration as a stored preference (today client-side for supersets)
- lb display/conversion end-to-end (API still kg-centric like the editor)
