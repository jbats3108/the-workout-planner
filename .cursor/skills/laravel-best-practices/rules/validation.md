# Validation & Forms Best Practices

## Spatie Laravel Data (this app)

HTTP boundaries use Spatie Data DTOs (`App\{Domain}\Data\...`), not Form Requests. See `docs/adr/0001-spatie-laravel-data-http-boundaries.md`.

- Constructor properties: always `public readonly`.
- Prefer PHP types over redundant attributes — do not add `#[Present]` / `#[ArrayType]` just because a property is an `array`. Optional empty lists: default `= []` and omit the key when empty (inferred `required` rejects `[]`).
- Use validation attributes only for constraints the type system cannot express (`#[Min]`, `#[Max]`, `#[Exists]`, `#[RequiredWithout]`, …). Prefer attributes over `rules()`; reserve `rules()` for nested/`*` cases.

## Use Form Request Classes

Extract validation from controllers into dedicated Form Request classes when Form Requests are used (rare here — prefer Spatie Data above).

Incorrect:
```php
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|max:255',
        'body' => 'required',
    ]);
}
```

Correct:
```php
public function store(StorePostRequest $request)
{
    Post::create($request->validated());
}
```

## Array vs. String Notation for Rules

Array syntax is more readable and composes cleanly with `Rule::` objects. Prefer it in new code, but check existing Form Requests first and match whatever notation the project already uses.

```php
// Preferred for new code
'email' => ['required', 'email', Rule::unique('users')],

// Follow existing convention if the project uses string notation
'email' => 'required|email|unique:users',
```

## Always Use `validated()`

Get only validated data. Never use `$request->all()` for mass operations.

Incorrect:
```php
Post::create($request->all());
```

Correct:
```php
Post::create($request->validated());
```

## Use `Rule::when()` for Conditional Validation

```php
'company_name' => [
    Rule::when($this->account_type === 'business', ['required', 'string', 'max:255']),
],
```

## Use the `after()` Method for Custom Validation

Use `after()` instead of `withValidator()` for custom validation logic that depends on multiple fields.

```php
public function after(): array
{
    return [
        function (Validator $validator) {
            if ($this->quantity > Product::find($this->product_id)?->stock) {
                $validator->errors()->add('quantity', 'Not enough stock.');
            }
        },
    ];
}
```
