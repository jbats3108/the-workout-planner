# Prefer Spatie Laravel Data for HTTP boundaries

HTTP request payloads, validation, and response bags use Spatie Laravel Data DTOs (not ad-hoc `$request->validate()` arrays or unstructured Inertia prop maps). Controllers type-hint Data objects for writes and build page/JSON bags from Data classes for reads.

Live under domain folders: `App\{Domain}\Data\...` (e.g. `App\Routines\Data\StoreRoutineData`). Shared casts live in `App\Shared\Data\Casts`.
