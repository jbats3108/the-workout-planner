OVRLOAD

{{ $inviterName }} invited you to OVRLOAD.

Create your account:
{{ $registrationUrl }}

@if ($expiresAt)
Expires {{ $expiresAt->timezone(config('app.timezone'))->toDayDateTimeString() }}
@endif

Reply to this email to reach {{ $inviterName }}.
