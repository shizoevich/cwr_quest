@extends('layouts.documentDownload')

@section('content')
    <div class="document-download-container">
        <div class="panel panel-default document-download-panel">
            <div class="panel-heading">
                <h3 class="panel-title">Change Within Reach, Inc. - Secure Document Download</h3>
            </div>
            <div class="panel-body">
                    <p class="text-center"><strong>{{ __('download.download_success') }}</strong></p>
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