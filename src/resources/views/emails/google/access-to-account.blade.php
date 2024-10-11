@component('mail::message')
#Hello

CWR Admin has created your corporate email account in Google:

Login: {{ $email }}

Password: {{ $password }}

To authorize in Google, please access this [link](https://accounts.google.com/signin/v2/identifier?flowName=GlifWebSignIn&flowEntry=ServiceLogin).

To authorize in EHR system, please access this [link]({{ route('login') }}) and click "Login with Google" button.
@endcomponent
