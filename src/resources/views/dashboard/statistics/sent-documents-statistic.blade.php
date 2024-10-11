@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="vue-wrapper">

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#emails-tab">Emails</a></li>
                    <li><a data-toggle="tab" href="#faxes-tab">Faxes</a></li>
                </ul>

                <div class="tab-content">
                    <div id="emails-tab" class="tab-pane fade in active">
                        <sent-documents-emails-tab
                                :statistic="{{json_encode($documentSharedEmails)}}"
                                :doctors="{{json_encode($doctors)}}"
                                :sent-statuses="{{json_encode($sentStatuses)}}">
                        </sent-documents-emails-tab>
                    </div>

                    <div id="faxes-tab" class="tab-pane fade">
                        <sent-documents-faxes-tab
                                :statistic="{{json_encode($documentSharedFaxes)}}"
                                :doctors="{{json_encode($doctors)}}">
                        </sent-documents-faxes-tab>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection