@component('mail::message')
{{-- # Introduction --}}

{{-- {{ $body}} --}}
<div>{!! $body !!}</div>

{{-- {!! htmlspecialchars($body) !!} --}}
{{-- The body of your message. --}}

{{-- @component('mail::button', ['url' => ''])

@endcomponent --}}

{{-- <p>Cheers!</p> --}}
{{-- <p>Thank you to choosing us as your trusted digital Partner --}}
{{-- Virce Business Support<br/> --}}
{{-- For any further details, contact us on 0778773698 or email us on customerservice@virce.co.ug --}}
{{-- </p> --}}
<p>Copyright © {{date("Y")}} All rights Reserved. Smart Collect is a product of Smart Collect Corporation</p>


Thanks,<br>
{{ config('app.name') }}
@endcomponent
