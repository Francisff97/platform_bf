{{-- resources/views/emails/contact/customer.blade.php --}}
@component('mail::message')
# Thanks, {{ $data['name'] }}!

We received your message:

@component('mail::panel')
{{ $data['message'] }}
@endcomponent

We’ll get back to you at **{{ $data['email'] }}**.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
