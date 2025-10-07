{{-- resources/views/emails/contact/admin.blade.php --}}
@component('mail::message')
 New contact request

**From:** {{ $data['name'] }} ({{ $data['email'] }})  
**Subject:** {{ $data['subject'] ?? '—' }}

@component('mail::panel')
{{ $data['message'] }}
@endcomponent

@endcomponent
