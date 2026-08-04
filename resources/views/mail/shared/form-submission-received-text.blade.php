OVRLOAD — {{ $submission->type->label() }}

From: {{ $submission->name }} <{{ $submission->email }}>
@if ($submission->category)
Category: {{ $submission->category->label() }}
@endif

Message:
{{ $submission->message }}

Submission #{{ $submission->id }} · {{ $submission->created_at?->toIso8601String() }}
