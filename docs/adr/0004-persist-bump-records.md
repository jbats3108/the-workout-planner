# Persist Bump Records on confirmed bumps

Finished-workout history can edit working-set weight/reps and, when that workout is still the latest non-deload finish for its routine, re-evaluate progression (including offering to undo a bump that no longer applies). Inferring “a bump came from this workout” from current routine weights is brittle after manual routine edits or later changes. Confirmed bumps are therefore stored as **Bump Records** (workout ↔ routine exercise ↔ from/to) at confirm time; undo uses that record, not inference.
