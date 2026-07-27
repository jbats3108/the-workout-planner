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

Active queue ordered **easiest → hardest** (gym-test 2026-07-26 + remaining product). Shipped items stay below for history.

1. **Finished workout history** — browse/edit finished workouts; re-eval progression when eligible (grill notes below; ADR-0004)

### Shipped (recent)

1. **Complete-then-log UX** — Done on main stage opens bottom sheet; Log set commits; Cancel aborts without server write; main stage is display-only with plate guide
2. **Rest-end alert + leave-during-rest** — sound/vibration when rest hits zero in foreground; notification permission on first rest + background notification when tab is hidden; clock-based rest sync on visibility return
2. **Prev set weight → next** — ~~pending-rest blocks focus race; client `lastWorkingWeightKg` + prior logged weight~~ done
3. **Keep screen awake in Play** — ~~Screen Wake Lock while player mounted; re-request on visibility~~ done
4. **Preview next during rest / setup** — ~~Up next card: exercise, set, weight/reps, plate stack when barbell~~ done
5. **Plate guide visibility in Play** — ~~works for barbell/EZ; missing equipment on pre-import orphans~~ done (audit + merge original short-name catalog)
6. **Progression on finish** — ~~carry-forward highest achieved top weight; confirm bumps when progression target hit; skip both for deload workouts~~ done
5. **Mid-session structure edits** — ~~mutate the in-progress workout snapshot (not the routine) from the player~~ done (add/remove incomplete working sets)
6. **More app-like mobile behaviour** — ~~chrome polish: safe areas, player full-bleed (no AppLayout), leave confirm, overscroll off on player+editor~~ done (grill notes below; no PWA / bottom nav)
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
21. **Dropsets** — ~~per working-set-slot multi-segment sets in editor + Play (grill notes below); update `CONTEXT.md` when shipping~~ done

## Grill: finished workout history

Decisions (2026-07-25):

- **Job (v1):** browse finished **Workouts** (list → compact detail). Not per-exercise strength-over-time.
- **Entry:** nav **History** + dashboard recent strip
- **List:** finished only (no discarded); chronological; filter by routine
- **Detail:** compact log; warm-ups read-only; edit working weight + reps only; do not surface `completed_at`
- **Re-eval eligibility:** latest **non-deload** finished workout for that routine; a deload finished after it does **not** block. Older finished workouts: log edit only, no routine change
- **On eligible save:** silent upward **Carry-forward**; then same **Progression** page for new **Bump**s and **undo bump**s
- **Undo:** use persisted **Bump Record**s (ADR-0004), not inference
- **Language:** UI label History = list of finished Workouts; not a new domain type. Avoid session/log/activity

Deferred: exercise strength-over-time charts; warm-up edits on history; discarded in list; structure edits on finished workouts; step-by-step impl plan (separate pass)

## Grill: dropsets

Decisions (2026-07-25):

- **Shape:** one working-set *slot* with ≥2 absolute-weight segments; one shared reps target; no rest timer between segments; working-group rest after the whole slot
- **Scope:** per-set kind inside the working group (mix normal + dropset); **not on supersets** in v1
- **Standard vs run-the-rack:** same stored segment list; “Run the rack” is an editor helper only (start / end / typed step → fill list); no separate persisted type; no gym DB inventory settings in v1
- **Working weight:** first segment may default from working weight, then editable absolutes
- **Progression:** dropsets ignored for achievement floor / bump / carry-forward
- **Deload:** scale every segment weight + the shared reps by the deload recipe
- **Play:** finish early or add extra drops (still ≥2 to count as a dropset); can **promote** a normal set to dropset mid-workout
- **Editor:** ≥2 segments required to save a dropset; shrinking `set_count` trims high-index recipes; new indexes default to normal
- **Language:** term **Dropset**; Set Group must still not be called a dropset

Deferred: dropsets on supersets; user gym DB inventory (min/max/step); demote dropset → normal in Play.

## Grill: app-like mobile behaviour

Decisions (2026-07-24), chrome-polish pass only:

- **Scope:** A — safe areas / full-bleed / leave guard / overscroll; not bottom nav, not PWA
- **Surfaces:** player + editor
- **Leave:** soft confirm on player (`beforeunload` + Inertia `before` when leaving `/workouts/{id}/*`); no history trap
- **Overscroll:** disabled on player + editor
- **Motion/haptics:** none this pass
- **Top chrome:** player chrome-minimal (no AppLayout); editor keeps AppLayout

Deferred: installable PWA, tabbed app shell, haptics.

## Grill: complete-then-log UX

Decisions (2026-07-27):

- **Happy path:** **Done** on main stage → bottom-sheet confirm → **Log set** (prefilled weight/reps; editable). Not one-tap commit without the sheet.
- **Main stage:** display-only target (+ plate guide on barbell / E-Z only). No weight/reps inputs before **Done**.
- **Sheet:** ~half-screen bottom sheet; primary **Log set**; dismiss via **Cancel** only (no swipe / backdrop). No auto-keyboard on open — user taps a field to edit.
- **Cancel:** abort only — set stays incomplete; no server write; no Rest started.
- **After Log set:** auto Rest when the Set Group has rest (same as today); **Skip rest** unchanged.
- **Re-log:** completed sets use the same flow; **Log set** overwrites the logged set.
- **Warm-ups:** same Done → sheet → Log set (prefill from `% × working`).
- **Dropsets:** same flow; sheet holds shared reps + segment weights (+/− drops).
- **Supersets:** one sheet per exercise — A → Log set → transition → B → Log set → working Rest.
- **Promote to dropset:** stays on main stage (below **Done**), before lifting.
- **v1 scope:** logging flow only; **+ Set** / **− Set** unchanged.

Deferred: sheet swipe/backdrop dismiss; auto-focus weight/reps; redesign **+ Set** / **− Set** placement.

## Parking lot

- **Ko-Fi on landing page** — optional tip link when beta-launching at the gym; target ~$25/mo to cover Laravel Cloud at ~200 active users (no mandatory subs yet)
- Per-exercise strength-over-time (charts / PR timeline) — needs its own grill
- Ad-hoc setup from player (beyond planned `has_setup_after`)
- Transition duration as a stored preference (today client-side for supersets)
- lb display/conversion end-to-end (API still kg-centric like the editor)
- Dropsets on supersets
- Gym dumbbell / rack inventory (min, max, step) for run-the-rack helper
- Flaky-network drafts — best-effort offline/queue for player logging
