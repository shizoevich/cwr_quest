@extends('layouts.documentDownload')

@section('content')
<div class="document-download-container">
    <div class="panel panel-default document-download-panel">
        <div class="panel-heading">
            <h3 class="panel-title">Change Within Reach, Inc. - Secure Document Download</h3>
        </div>
        <div class="panel-body">
            @if (!$errors->has('document'))
                <p>You have <strong>{{ $daysLeft }} day(s)</strong> left to download this document</p>
            <p>Total number of allowed downloads left: <strong>{{ $downloadAttemptsLeft }}</strong> </p>
            <p>Please enter password:</p>


            <form id='download' class="form-inline text-center" method="post" action="{{ route('document-download.download') }}" target="_self">
                {{csrf_field()}}
                <input class="form-control{{ $errors->has('password') ? ' error' : '' }}" type="password" name="password" required>
                <input type="text" name="shared_link"
                       value="{{ isset($shared_link) ? $shared_link : request()->input('shared_link') }}" hidden="hidden">
                <input type="text" name="shared_link_2"
                       value="{{ isset($shared_link) ? $shared_link : request()->input('shared_link') }}" hidden="hidden">
                <button class="btn btn-primary" type="submit" role="button">Download</button>
            </form>
            @endif
            <span class="help-block error">
                @if ($errors->has('password'))
                    <strong>{{ $errors->first('password') }}</strong>
                @endif
                @if ($errors->has('document'))
                    <strong>{{ $errors->first('document') }}</strong>
                @endif
            </span>

            <p>NOTE: The password to download this document was set by the sender.<br>
                If you do not know the
                password, please reach out to us to get assistance via email or phone listed below:</p>

            <ul>
                <li>Phone: <a href="tel:(213) 908-1234">(213) 908-1234</a></li>
                <li>Email: <a href="mailto:admin@cwr.care">admin@cwr.care</a></li>
            </ul>

            <p>Thank you,<br>
                Team @ Change Within Reach, Inc.</p>
        </div>
        <div class="panel-footer">
            <strong>Personal and Confidential Information</strong><br>
            <p>The information on this page is intended only for the use of the individual or entity to which it
                is
                addressed and may contain information that is privileged, confidential, and exempt from
                disclosure.
                If
                the visitor of this page is not the intended recipient or an employee or agent responsible for
                receiving
                this information and delivering it to the intended recipient, you are hereby notified that any
                dissemination, distribution, or copying of this information is strictly prohibited.</p>
        </div>
    </div>
</div>
@endsection