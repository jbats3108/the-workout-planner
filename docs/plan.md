# Plan

Working backlog for OVRLOAD v2. Update this as items ship or get deferred. Domain language stays in `CONTEXT.md`; hard decisions stay in `docs/adr/`.

**Grill cleanup:** when a grilled feature ships, delete its `## Grill: …` section. Move any still-open deferred bullets into **Backlog**; do not keep decided implementation notes here.

**Notion inbox:** after pulling bullets from Notion [Ovrload](https://app.notion.com/p/3aae5dd99f0c80ad928ade1a5c6b0749) into this file, clear **only** the list items under `## Backlog:` — leave that header and a single empty bullet (`-`). Do not replace the whole page or delete child pages / other sections.

## Now

- **FAQ page** — public/help FAQ; draft bullets on Notion [FAQ Page](https://app.notion.com/p/3aae5dd99f0c8006a6cbf6df379661a8) (early-adopter forever-free, Ko-fi, what app is/isn't, beta, no AI/ad data sale, not a training app, backlog link)

## Recently shipped (foundation)

- [x] v2 schema + domain models (grams, set-group `type`, mirror workout snapshots)
- [x] Domain-oriented `App\{Domain}\...` layout + mirrored tests
- [x] Routine editor (desktop dense list / mobile stage, zinc + lime)
- [x] Workout player MVP: start / resume / log sets / rest / setup / finish
- [x] One in-progress workout enforcement; deload start mode
- [x] Dashboard start/resume + empty-routine guards
- [x] Editor save: allow empty warm-up percents

## Shipped (recent)

Gym-test 2026-07-28 + 2026-07-26 + remaining product history. Newest first within each batch where noted.

1. **Exercise catalog curation** — curated 174 shared lifts (original short list + selective free-exercise-db); `exercises:import` soft-deletes extras unless `--no-prune`
2. **Nicer confirms** — ~~replace browser `confirm`/`alert` with in-app dialogs~~ done (`confirmDialog` + `ConfirmDialogHost`; RestStage inline skip unchanged)
3. **"Block" naming** — ~~UI/copy only: Play/History drop “Block N” (show `Superset` when needed); Up next drops Block N; setup hints use exercise names; editor/settings leftover noun = Exercise; domain `Block` unchanged~~ done
4. **Set x/y in exercise header (Play)** — ~~set progress in the big exercise header (and log sheet), labeled Warm-up / Working~~ done
5. **Bump when mode** — ~~Settings “Bump when”: Any set / Last set at top weight; snapshotted on workout start; Floor kept for carry-forward; Bump = prescribed Target (no separate Progression Target / editor Bump); log sheet `Floor X. Bump @ Y`; confirm on finish + history re-eval~~ done
6. **PWA app shell** — ~~tabbed app shell; haptics~~ done (mobile bottom tabs: Dashboard · History · Training · Settings; desktop sidebar unchanged; player haptics on Done / Log set; rest-end vibrate already shipped)
7. **Duplicate routine** — ~~clone an existing routine as a starting point~~ done (POST duplicate; deep-copies blocks / set groups / warm-ups / dropsets / deload; opens editor as `{name} (copy)`)
8. **Superset setup preview** — ~~show both exercises during setup~~ done (Setup Up Next lists A + B for the upcoming round)
9. **History: group block sets** — ~~group sets by block in history UI~~ done (one section per block; working sets grouped by exercise)
10. **PWA installable (phase 1)** — ~~manifest, Apple meta tags, service worker at `/sw.js` (root scope), iOS install banner~~ done (#23)
11. **Progression defaults UI** — ~~Settings Achievement Floor / Progression Target + editor Floor / Bump overrides~~ done (empty override inherits user default; placeholders from Settings)
12. **Slugs / ULIDs in routes** — ~~investigate slugs instead of IDs~~ done: [ADR-0006](adr/0006-slugs-and-ulids-in-routes.md) (routine slugs + workout ULIDs)
13. **Rest skip confirm** — ~~inline Skip rest confirm in player~~ done (`RestStage.vue`)
14. **Type-ahead exercise picker** — ~~separate find bar + native `<select>` (no search in dropdown)~~ done (one control: tap exercise → bottom sheet with focused search + filtered list; mobile + desktop)
15. **Setup between warm-ups** — ~~per-step setup after warm-up steps; setup then warm-up rest; block Setup→work unchanged~~ done
16. **Add/remove set player bugs** — ~~− Set advanced focus / left `set N of 1`; last-round − Set skipped the block~~ done (reindex after remove; keep focus on add; hide − Set on last working round)
17. **Player layout tweaks** — ~~centralise text, clearer section separation, bigger elements; stronger set-of-x highlight~~ done
18. **Set x/n on setup** — ~~show set progress (x of n) during setup~~ done (Up next on setup/rest: `Set x/n` from planned group count)
19. **Complete screen full-page** — ~~log-set complete UI should cover the whole page~~ done (full-screen log sheet; keyboard overlays content so Log/Cancel stay put; finish Complete stage hides player header)
20. **Countdown beeps** — ~~rest/timer countdown audio cues~~ done (ticks at 5…1 + long end tone; vibrate mirrors when available)
21. **Admin nav order** — ~~push Admin to the bottom of the top-nav items~~ done (after Training in primary rail/drawer)
22. **Remove clickable titles** — ~~titles should not navigate; use explicit buttons only~~ done (dashboard routine name is plain text; edit via icon)
23. **Bigger edit/delete icons** — ~~increase affordance size for edit/delete controls in the UI~~ done (dashboard routine cards: `size-5` + larger hit target)
24. **Soft-fail not-found / errors** — ~~no raw error pages for expected GET misses/forbids; redirect + flash/toast~~ done (authenticated web; guests/admin/mutations stay hard)
25. **Finished workout history** — browse/edit finished workouts at `/history`; dashboard recent strip + nav; warm-ups read-only; working weight + reps editable; re-eval progression on latest non-deload finish (carry-forward, bumps, undo via Bump Records; ADR-0004)
26. **Complete-then-log UX** — ~~Done on main stage opens full-page log sheet; Log set commits; Cancel aborts without server write; main stage is display-only with plate guide~~ done
27. **Rest-end alert + leave-during-rest** — sound/vibration when rest hits zero in foreground; notification permission on first rest + background notification when tab is hidden; clock-based rest sync on visibility return
28. **Prev set weight → next** — ~~pending-rest blocks focus race; client `lastWorkingWeightKg` + prior logged weight~~ done
29. **Keep screen awake in Play** — ~~Screen Wake Lock while player mounted; re-request on visibility~~ done
30. **Preview next during rest / setup** — ~~Up next card: exercise, set, weight/reps, plate stack when barbell~~ done
31. **Plate guide visibility in Play** — ~~works for barbell/EZ; missing equipment on pre-import orphans~~ done (audit + merge original short-name catalog)
32. **Progression on finish** — ~~carry-forward highest achieved top weight; confirm bumps when progression target hit; skip both for deload workouts~~ done
33. **Mid-session structure edits** — ~~mutate the in-progress workout snapshot (not the routine) from the player~~ done (add/remove incomplete working sets; reindex + last-round − Set guard)
34. **More app-like mobile behaviour** — ~~chrome polish: safe areas, player full-bleed (no AppLayout), leave confirm, overscroll off on player+editor~~ done (PWA install #5; bottom nav in #1)
35. **User default warm-up %s and reps** — ~~prefs on the user; per-step %×reps on warm-up steps; seed into new blocks; Settings → Training~~ done
36. **Restyle whole app to match Overload branding** — ~~zinc + lime~~ done: dark-first near-black + neon yellow primary + cyan accent (`docs/branding.md`, `resources/css/app.css`)
37. **Find and import exercises** — ~~shared catalog JSON + `exercises:import` + seeder; editor find filter; index scoped to `forUser`~~ done (~80 lifts)
38. **Admin panel** — ~~thin Inertia admin: exercises, muscle groups, read-only users; sidebar link for admins~~ done
39. **Dead code audit (v1 leftovers)** — ~~JSON catalog APIs, unused MG update, starter UI packages, unused permission seeders~~ done
40. **Strip Laravel starter-kit UI** — ~~remove obvious Breeze/starter chrome and behaviours that still read as the stock kit~~ done (branded OVRLOAD home; dead search/footer/auth variants removed)
41. **Rebrand to OVRLOAD** — ~~rename product surfaces to **OVRLOAD**; mark/icon and related chrome around **OVR** / \*\*OVRLD~~\*\* done (`docs/branding.md`; logos, home, auth, `APP_NAME`)
42. **Plate calculator UI** — ~~Settings for bars/plates; player shows nearest loadable stack~~ done

- Equipment classification: catalog `equipment` on exercises; snapshot into workouts; plate guide only for barbell / E-Z curl bar

42. **Player / editor UX polish** — ~~finish/abandon in-progress; edit affordance; exercise find results; mobile editor scroll, compact warm-up, in-card search, add-block placement~~ done
43. **Warm-up weight prefill in Play** — ~~incomplete warm-up sets should fill the weight field from `% × working` (`target_weight_kg`), not the previous logged warm-up; fix Target label `v-else` on reps~~ done
44. **Warm-up setup steps** — ~~plan setup (press-when-done) pauses inside the warm-up flow, not only setup-after-block~~ done (`has_setup_after_warm_up`: once between last warm-up and first working)
45. **Rest after warm-ups** — ~~make warm-up group rest first-class in editor + Play (rest after warm-up sets / before working)~~ done (editor exposes WU rest; Play already used group rest)
46. **Clear block warm-up** — ~~one-tap remove all warm-up steps from a block in the editor~~ done
47. **Warm-up defaults scope** — ~~Settings: seed warm-ups into every new block vs first block only~~ done
48. **Dropsets** — ~~per working-set-slot multi-segment sets in editor + Play; update `CONTEXT.md` when shipping~~ done
49. **Login screen on every open** — ~~authenticated users hitting `/` saw the guest home/login UI; bfcache could restore stale guest pages after sign-in~~ done (home redirect + guest-page bfcache reload)

## Backlog

Single triage list — reprioritize across buckets as needed. **Features (FAQ)** are listed on the public help/FAQ page for beta testers. **Polish & mobile integration** is shipped-flow UX, not net-new capability. Notion [inbox](https://app.notion.com/p/3aae5dd99f0c80ad928ade1a5c6b0749) → pull new bullets into the right bucket below.

-

### Features (FAQ)

- **Tutorial / welcome guide** — help new users find Settings, progression defaults, editor overrides, and bump confirmation; inbox: start with FAQ, then create/play walkthrough
- **Post-hoc workout record** — log a full finished workout in one pass (no Play); pick routine or ad-hoc structure; assign `finished_at` to a past date; grill scope (progression re-eval, warm-ups, deload flag)
- **Switch exercise for deload** — swap the exercise used when starting a deload (same structure, different lift)
- **Strava integration** — connect account; export finished workouts (and/or sync activity metadata); grill scope (OAuth, fields, privacy)
- **Garmin sync** — long-term; watch functionality too; prefer after Strava
- **Per-exercise strength-over-time** — charts / PR timeline; needs its own grill
- **History extensions** — warm-up edits; discarded workouts in list; structure edits on finished workouts
- **Demote dropset → normal in Play**
- **Ad-hoc setup from player** — beyond planned `has_setup_after`
- **Transition duration preference** — stored user pref (today client-side for supersets)
- **lb display/conversion** — end-to-end (API still kg-centric like the editor)
- **Dropsets on supersets**
- **Gym dumbbell / rack inventory** — min, max, step for run-the-rack helper
- **Flaky-network drafts** — best-effort offline/queue for player logging

### Polish & mobile integration

- 

### Bugfixes

-

### Code quality & security

- **Check test function name conventions** — audit PHPUnit / Vitest names for consistency (`it_…` / `test_…` / describe+it wording) and align or document the house style
- **GDPR compliance?** — clarify whether/what is required (privacy policy, data export/delete, retention, cookies); grill before building
