<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $submission->type->label() }}</title>
</head>
<body style="margin:0;padding:0;background:#0a0a0a;color:#fafafa;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0a0a0a;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#111;border:1px solid #27272a;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 28px;border-bottom:1px solid #27272a;">
                        <p style="margin:0;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:#d4ff00;">OVRLOAD</p>
                        <h1 style="margin:8px 0 0;font-size:20px;font-weight:700;color:#fafafa;">{{ $submission->type->label() }}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 28px;font-size:15px;line-height:1.55;color:#a1a1aa;">
                        <p style="margin:0 0 12px;"><strong style="color:#fafafa;">From:</strong> {{ $submission->name }} &lt;{{ $submission->email }}&gt;</p>
                        @if ($submission->category)
                            <p style="margin:0 0 12px;"><strong style="color:#fafafa;">Category:</strong> {{ $submission->category->label() }}</p>
                        @endif
                        <p style="margin:0 0 8px;"><strong style="color:#fafafa;">Message</strong></p>
                        <p style="margin:0;white-space:pre-wrap;color:#e4e4e7;">{{ $submission->message }}</p>
                        <p style="margin:20px 0 0;font-size:12px;color:#71717a;">Submission #{{ $submission->id }} · {{ $submission->created_at?->toIso8601String() }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
