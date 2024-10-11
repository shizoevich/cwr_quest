<div class="container-fluid" style="padding-top: 15px;">
    <div class="training-index">
        <div class="row">
            <div class="table">
                <div class="table-row">
                    <div class="col-md-4 well table-cell">
                        <h3 class="well-title">Last training</h3>
                        <table style="width: 100%;">
                            <tr>
                                <td>Exam Date:</td>
                                <td>{{ $lastTraining ? $lastTraining->updated_at->toFormattedDateString() : 'n/a' }}</td>
                            </tr>
                            <tr>
                                <td>Exam Score:</td>
                                <td>{{ $lastTraining ? ('Over 80%') : 'n/a' }}</td>
                            </tr>
                            <tr>
                                <td>Correct Answers:</td>
                                <td>{{ $lastTraining ? ($lastTraining->score .' out of 14') : 'n/a' }}</td>
                            </tr>
                            <tr>
                                <td>Number of Attempts:</td>
                                <td>{{ $attempts or 'n/a' }}</td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td>{{ $lastTraining ? ('Expires on '. $lastTraining->end_date->toFormattedDateString()) : 'n/a' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-8 well table-cell">
                        <h3 class="well-title">HIPAA Compliance Training & Certification Exam</h3>
                        <p>few things we want you to know before you begin:

                            Exam passing score is 80%, which means that you need to answer at least 11
                            questions
                            out
                            of
                            14 correctly in order to get your Certificate of Completion.

                            You can take the exam as many times as necessary. So if you will not pass this
                            time,
                            don't
                            worry. You can always review the educational course and take the exam again.

                            We will show you the number of questions you have answered correctly, however,
                            we
                            will
                            not
                            provide details. It is an exam with unlimited attempts after all, and we change
                            our
                            questions from time to time to keep things fair.
                        </p>
                        <div class="text-center">
                            @if($lastTraining)
                            <form method="post" action="{{route('training.certificate')}}" class="inline-block">
                                {{csrf_field()}}
                                <input type="hidden" name="training_id" value="{{$lastTraining->id}}" />
                                <button class="btn btn-success">Download Certificate</button>
                            </form>
                            @endif
                            <a class="btn btn-primary" href="{{route('exams')}}">
                                Take the exam
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="well">
                    <div class="training-frame">
                        <iframe src="{{url('hippa/story_html5.html')}}" frameborder="0" id="training-frame">
                            Your browser doesn't support iFrames.
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>