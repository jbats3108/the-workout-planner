# OVRLOAD

Personal strength training for serious lifters: plan routines, log sessions, track progressive overload. Not coaching. Product branding (name, mark, visual direction) lives in `docs/branding.md`.

## Language

**User**:
The account holder who owns routines, workouts, customs, and a plate profile.
_Avoid_: Athlete, lifter, member

**Routine**:
A named plan the user owns and can start as a workout. Ordered list of blocks. Flat — no calendar or “today’s workout” scheduling.
_Avoid_: Program, split, schedule, workout plan

**Block**:
One ordered unit inside a routine. Either a single exercise or a **superset** (exactly two exercises). Owns one warm-up Set Group and one working Set Group shared across the block’s exercises. Optional setup after the block.
_Avoid_: Slot, group, row, exercise entry

**Superset**:
A block with exactly two exercises played as matched rounds: A → transition → B → then working rest. Not a giant set or circuit.
_Avoid_: Circuit, giant set, pairing (as a noun for the block)

**Set Group**:
A bag of like sets on a block: either warm-up or working. One set count and one rest for the whole group (for a superset, rest is after the pair; transition sits between A and B). Warm-up weights are % of that exercise’s working weight; each exercise on the block has its own working weight and prescribed rep target.
_Avoid_: Set scheme, wave, drop set

**Working Weight**:
The absolute load prescribed for an exercise on a block’s working sets. The number progression updates. Warm-ups are derived from it.
_Avoid_: Top set, training max, TM

**Exercise**:
A named movement in the library. Either shared (master catalog) or owned by a user (custom). No demo media required.
_Avoid_: Movement, lift, catalog exercise (as a separate type)

**Workout**:
One started instance of a routine (normal or deload mode). Snapshots the routine’s blocks at start so mid-session and later routine edits don’t rewrite history. At most one in-progress workout per user.
_Avoid_: Session, log, activity

## Progression

**Achievement Floor**:
Minimum reps for a logged set’s weight to count as achieved. Optional; user default with per-exercise override.
_Avoid_: Count reps, achieved-at, valid set threshold

**Progression Target**:
Minimum reps at the working weight that triggers a bump suggestion. Optional; user default with per-exercise override.
_Avoid_: Bump reps, increase-at, progression threshold

**Carry-forward**:
On finishing (or when applying progression), set the routine’s working weight for an exercise to the highest achieved top weight from the relevant workout — without asking. Does not apply from deload workouts.
_Avoid_: Sync, catch-up

**Bump**:
A confirmed increase to an exercise’s working weight on the routine, offered when the progression target was hit. Never silent.
_Avoid_: Increase, PR jump, auto-load

**Deload Recipe**:
Per-routine uniform factors (weight and reps) applied when starting in deload mode. Same factors for every exercise on the routine.
_Avoid_: Recovery recipe, easy recipe

**Deload Mode**:
A way to start a workout that applies the routine’s deload recipe to the snapshot. Deload workouts do not carry-forward or bump the routine’s normal working weights.
_Avoid_: Easy mode, recovery mode

## Pauses

**Rest**:
Timed pause after a set (or after the pair in a superset), configured once per Set Group.
_Avoid_: Break, cooldown

**Transition**:
Short pause between the two exercises in a superset round (A then B), before working rest.
_Avoid_: Gap, changeover

**Setup**:
Press-when-done pause between blocks for equipment changes. Planned on the routine and/or started ad-hoc. Not rest and not working time.
_Avoid_: Transition (for between-block), intermission

## Loading

**Plate Profile**:
A user’s bar-loading setup: bar presets, plate denominations with counts, and colour coding. Used by the plate calculator; if a target isn’t loadable, offer the nearest loadable weight.
_Avoid_: Gym inventory, plate set
