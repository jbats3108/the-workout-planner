# Plan

Working backlog for OVRLOAD v2. Update this as items ship or get deferred. Domain language stays in `CONTEXT.md`; hard decisions stay in `docs/adr/`.

**Grill cleanup:** when a grilled feature ships, delete its `## Grill: …` section. Move any still-open deferred bullets into **Parking lot** (or **Next**); do not keep decided implementation notes here.

**Notion inbox:** after pulling bullets from Notion [Ovrload](https://app.notion.com/p/3aae5dd99f0c80ad928ade1a5c6b0749) into this file, clear **only** the list items under `## Backlog:` — leave that header and a single empty bullet (`-`). Do not replace the whole page or delete child pages / other sections.

## Now (recently shipped)

- [x] v2 schema + domain models (grams, set-group `type`, mirror workout snapshots)
- [x] Domain-oriented `App\{Domain}\...` layout + mirrored tests
- [x] Routine editor (desktop dense list / mobile stage, zinc + lime)
- [x] Workout player MVP: start / resume / log sets / rest / setup / finish
- [x] One in-progress workout enforcement; deload start mode
- [x] Dashboard start/resume + empty-routine guards
- [x] Editor save: allow empty warm-up percents

## Next

Active queue ordered **easiest → hardest** (gym-test 2026-07-26 + remaining product). Shipped items stay below for history.

1. **Audit agent guidance for duplication** — Spatie Data DTO rules (and likely other conventions) are repeated across `AGENTS.md`, ADRs, and `.cursor/skills/...`; consolidate so agents load one source of truth and keep context light.
2. **Bigger edit/delete icons** — increase affordance size for edit/delete controls in the UI.
3. **Remove clickable titles** — titles should not navigate; use explicit buttons only.
4. **Admin nav order** — push Admin to the bottom of the top-nav items.
5. **Login screen on every open?** — investigate why the app often shows login on open (session, PWA/tab restore, cookie, redirect).
6. **Bump confirmation timing** — grill: when (if at all) the bump confirmation screen should appear (finish only? history re-eval? skip entirely?).

### Shipped (recent)

1. **Soft-fail not-found / errors** — ~~no raw error pages for expected GET misses/forbids; redirect + flash/toast~~ done (authenticated web; guests/admin/mutations stay hard)
1. **Finished workout history** — browse/edit finished workouts at `/history`; dashboard recent strip + nav; warm-ups read-only; working weight + reps editable; re-eval progression on latest non-deload finish (carry-forward, bumps, undo via Bump Records; ADR-0004)
2. **Complete-then-log UX** — Done on main stage opens bottom sheet; Log set commits; Cancel aborts without server write; main stage is display-only with plate guide
2. **Rest-end alert + leave-during-rest** — sound/vibration when rest hits zero in foreground; notification permission on first rest + background notification when tab is hidden; clock-based rest sync on visibility return
2. **Prev set weight → next** — ~~pending-rest blocks focus race; client `lastWorkingWeightKg` + prior logged weight~~ done
3. **Keep screen awake in Play** — ~~Screen Wake Lock while player mounted; re-request on visibility~~ done
4. **Preview next during rest / setup** — ~~Up next card: exercise, set, weight/reps, plate stack when barbell~~ done
5. **Plate guide visibility in Play** — ~~works for barbell/EZ; missing equipment on pre-import orphans~~ done (audit + merge original short-name catalog)
6. **Progression on finish** — ~~carry-forward highest achieved top weight; confirm bumps when progression target hit; skip both for deload workouts~~ done
5. **Mid-session structure edits** — ~~mutate the in-progress workout snapshot (not the routine) from the player~~ done (add/remove incomplete working sets)
6. **More app-like mobile behaviour** — ~~chrome polish: safe areas, player full-bleed (no AppLayout), leave confirm, overscroll off on player+editor~~ done (no PWA / bottom nav)
7. **User default warm-up %s and reps** — ~~prefs on the user; per-step %×reps on warm-up steps; seed into new blocks; Settings → Training~~ done
8. **Restyle whole app to match Overload branding** — ~~zinc + lime~~ done: dark-first near-black + neon yellow primary + cyan accent (`docs/branding.md`, `resources/css/app.css`)
9. **Find and import exercises** — ~~shared catalog JSON + `exercises:import` + seeder; editor find filter; index scoped to `forUser`~~ done (~80 lifts)
10. **Admin panel** — ~~thin Inertia admin: exercises, muscle groups, read-only users; sidebar link for admins~~ done
11. **Dead code audit (v1 leftovers)** — ~~JSON catalog APIs, unused MG update, starter UI packages, unused permission seeders~~ done
12. **Strip Laravel starter-kit UI** — ~~remove obvious Breeze/starter chrome and behaviours that still read as the stock kit~~ done (branded OVRLOAD home; dead search/footer/auth variants removed)
13. **Rebrand to OVRLOAD** — ~~rename product surfaces to **OVRLOAD**; mark/icon and related chrome around **OVR** / **OVRLD**~~ done (`docs/branding.md`; logos, home, auth, `APP_NAME`)
14. **Plate calculator UI** — ~~Settings for bars/plates; player shows nearest loadable stack~~ done
    - Equipment classification: catalog `equipment` on exercises; snapshot into workouts; plate guide only for barbell / E-Z curl bar
15. **Player / editor UX polish** — ~~finish/abandon in-progress; edit affordance; exercise find results; mobile editor scroll, compact warm-up, in-card search, add-block placement~~ done
16. **Warm-up weight prefill in Play** — ~~incomplete warm-up sets should fill the weight field from `% × working` (`target_weight_kg`), not the previous logged warm-up; fix Target label `v-else` on reps~~ done
17. **Warm-up setup steps** — ~~plan setup (press-when-done) pauses inside the warm-up flow, not only setup-after-block~~ done (`has_setup_after_warm_up`: once between last warm-up and first working)
18. **Rest after warm-ups** — ~~make warm-up group rest first-class in editor + Play (rest after warm-up sets / before working)~~ done (editor exposes WU rest; Play already used group rest)
19. **Clear block warm-up** — ~~one-tap remove all warm-up steps from a block in the editor~~ done
20. **Warm-up defaults scope** — ~~Settings: seed warm-ups into every new block vs first block only~~ done
21. **Dropsets** — ~~per working-set-slot multi-segment sets in editor + Play; update `CONTEXT.md` when shipping~~ done

## Parking lot

- **FAQ page** — public/help FAQ; draft bullets on Notion [FAQ Page](https://app.notion.com/p/3aae5dd99f0c8006a6cbf6df379661a8) (early-adopter forever-free, Ko-fi, what app is/isn't, beta, no AI/ad data sale, not a training app, backlog link)
- Investigate **slugs instead of IDs** in routes (routines, workouts, exercises, etc.)
- **Policy audit** — verify every route/action has the right ability and policies stay complete as surfaces grow
- **Facilitate full code review** — make a thorough review of the app tractable (scope, tooling, or staged passes)
- **Security sweep** — hunt for authz holes, mass-assignment, IDOR, CSRF/session gaps, and similar
- **GDPR compliance?** — clarify whether/what is required (privacy policy, data export/delete, retention, cookies); grill before building
- Per-exercise strength-over-time (charts / PR timeline) — needs its own grill
- History: warm-up edits; discarded workouts in list; structure edits on finished workouts
- Demote dropset → normal in Play
- Installable PWA; tabbed app shell; haptics
- Complete-then-log follow-ups: sheet swipe/backdrop dismiss; auto-focus weight/reps; redesign **+ Set** / **− Set** placement
- Ad-hoc setup from player (beyond planned `has_setup_after`)
- Transition duration as a stored preference (today client-side for supersets)
- lb display/conversion end-to-end (API still kg-centric like the editor)
- Dropsets on supersets
- Gym dumbbell / rack inventory (min, max, step) for run-the-rack helper
- Flaky-network drafts — best-effort offline/queue for player logging
