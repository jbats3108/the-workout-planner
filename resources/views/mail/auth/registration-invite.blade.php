<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>You're invited to OVRLOAD</title>
</head>
<body style="margin:0;padding:0;background-color:#0a0a0a;color:#ebebe0;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0a0a0a;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:520px;background-color:#111111;border:1px solid #2a2a2a;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 28px 8px 28px;">
                            <p style="margin:0;font-size:28px;font-weight:700;letter-spacing:0.04em;line-height:1.1;">
                                <span style="color:#d9ff00;">OVR</span><span style="color:#ebebe0;">LOAD</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 8px 28px;">
                            <p style="margin:0;font-size:18px;font-weight:600;color:#ebebe0;line-height:1.4;">
                                {{ $inviterName }} invited you to OVRLOAD
                            </p>
                            <p style="margin:12px 0 0 0;font-size:14px;line-height:1.55;color:#b8b8ae;">
                                Use the link below to create your account. It’s a one-time invite — open it when you’re ready to register.
                            </p>
                            @if ($expiresAt)
                                <p style="margin:12px 0 0 0;font-size:13px;line-height:1.5;color:#8a8a82;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;">
                                    Expires {{ $expiresAt->timezone(config('app.timezone'))->toDayDateTimeString() }}
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 32px 28px;">
                            <a href="{{ $registrationUrl }}"
                               style="display:inline-block;background-color:#d9ff00;color:#0a0a0a;text-decoration:none;font-weight:700;font-size:14px;letter-spacing:0.02em;padding:12px 20px;border-radius:10px;">
                                Create account
                            </a>
                            <p style="margin:20px 0 0 0;font-size:12px;line-height:1.5;color:#8a8a82;word-break:break-all;">
                                Or paste this URL:<br>
                                <a href="{{ $registrationUrl }}" style="color:#00e5ff;text-decoration:underline;">{{ $registrationUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0 0;font-size:11px;color:#5c5c56;max-width:520px;line-height:1.4;">
                    Progressive strength tracking. Reply to this email to reach {{ $inviterName }}.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
