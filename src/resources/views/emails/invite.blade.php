@component('mail::message')
# Hello

Change Within Reach, Inc invites you to register on the site <a href="{{route('register')}}">{{route('register')}}</a>

Thanks,<br>
 {{ config('app.name') }}
@endcomponent
