@component('mail::message')
# Reset Your Password

Hello {{ $userName }},

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $actionUrl])
Reset Password
@endcomponent

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@slot('subcopy')
If you're having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser: [{{ $displayableActionUrl }}]({{ $actionUrl }})
@endslot
@endcomponent
@component('mail::message')
# Reset Your Password

Hello {{ $userName }},

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $actionUrl])
Reset Password
@endcomponent

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@slot('subcopy')
If you're having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser: [{{ $displayableActionUrl }}]({{ $actionUrl }})
@endslot
@endcomponent
