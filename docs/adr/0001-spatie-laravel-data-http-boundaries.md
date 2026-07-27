# Prefer Spatie Laravel Data for HTTP boundaries

HTTP request payloads, validation, and response bags use Spatie Laravel Data DTOs (not ad-hoc `$request->validate()` arrays or unstructured Inertia prop maps). Controllers type-hint Data objects for writes and build page/JSON bags from Data classes for reads.

Live under domain folders: `App\{Domain}\Data\...` (e.g. `App\Routines\Data\StoreRoutineData`). Shared casts live in `App\Shared\Data\Casts`.

## Constructor properties

- Always `public readonly` — immutability is part of the DTO contract.
- Prefer PHP types (and PHPDoc list/array shapes) over redundant Spatie validation attributes. `array` already implies array typing; do **not** add `#[ArrayType]` or `#[Present]` for that.
- For optional lists that may be empty: default to `= []` and **omit the key** from the request when empty (Laravel’s `required`, which Spatie infers, treats `[]` as empty). Prefer omit + default over `#[Present]`.
- Use attributes only for constraints the type system cannot express (`#[Min]`, `#[Max]`, `#[Exists]`, `#[RequiredWithout]`, etc.). Prefer attributes over a `rules()` method; reserve `rules()` for nested/`*` cases attributes cannot cover cleanly.
