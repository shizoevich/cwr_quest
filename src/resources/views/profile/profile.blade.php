@extends('layouts.app')

@section('content')

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <form class="profile-form" method="POST" action="{{route('profile.store')}}">
                    {{ csrf_field() }}
                    <input name="user_id" value="{{ $user->id }}" hidden/>
                    <div class="form-group">
                        <h3><label for="first_name">First Name</label></h3>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder=""
                               value="{{ old('first_name', isset($user->therapistSurvey) ? $user->therapistSurvey->first_name : '')}}"
                               required
                                {{ $edit ? "" : "disabled" }}
                        >
                    </div>

                    <div class="form-group">
                        <h3><label for="middle_name">Middle Name</label></h3>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder=""
                               value="{{ old('middle_name', isset($user->therapistSurvey) ? $user->therapistSurvey->middle_name : '')}}"
                                {{ $edit ? "" : "disabled" }}
                        >
                    </div>

                    <div class="form-group">
                        <h3><label for="last_name">Last Name</label></h3>
                        <input type="text" class="form-control" id="middle_name" name="last_name" placeholder=""
                               value="{{ old('last_name', isset($user->therapistSurvey) ? $user->therapistSurvey->last_name : '')}}"
                               required
                                {{ $edit ? "" : "disabled" }}
                        >
                    </div>

                    <div class="form-group">
                        <h3><label for="credentials">Your Credentials</label></h3>
                        <input type="text" class="form-control" id="credentials" name="credentials" placeholder=""
                               value="{{ old('credentials', isset($user->therapistSurvey) ? $user->therapistSurvey->credentials : '')}}"
                               required
                                {{ $edit ? "" : "disabled" }}
                        >
                    </div>

                    <div class="form-group">
                        <h3><label for="school">Which school did you attend and graduate from?</label></h3>
                        <textarea class="form-control" rows="3" id="school" name="school" placeholder=""
                                  required
                                {{ $edit ? "" : "disabled" }}
                        >{{ old('first_name', isset($user->therapistSurvey) ? $user->therapistSurvey->school : '')}}</textarea>
                    </div>

                    <div class="form-group">
                        <h3><label for="complete_education">What year did you complete your graduate education?</label>
                        </h3>
                        <input type="date" class="form-control" id="complete_education" name="complete_education"
                               value="{{ old('complete_education', isset($user->therapistSurvey) ? \Carbon\Carbon::parse($user->therapistSurvey->complete_education)->toDateString() : '')}}"
                               {{--placeholder="{{ old('complete_education', isset($user->therapistSurvey) ? \Carbon\Carbon::parse($user->therapistSurvey->complete_education)->toDateString() : '')}}"--}}
                               required
                                {{ $edit ? "" : "disabled" }}
                        >
                    </div>

                    <div class="form-group">
                        <h3><label for="years_of_practice" >How many years have you been practicing?</label></h3>
                        <input type="number" class="form-control {{ $errors->has('years_of_practice') ? "error" : "" }}" id="years_of_practice" name="years_of_practice"
                               value="{{ old('years_of_practice', isset($user->therapistSurvey) ? $user->therapistSurvey->years_of_practice : '')}}"
                               placeholder="" required
                                {{ $edit ? "" : "disabled" }}
                        >
                        <span>If you are just starting your career write 0 (zero)</span>
                        <span class="help-block with-errors error">
                            @if ($errors->has('years_of_practice'))
                                <strong>{{ $errors->first('years_of_practice') }}</strong>
                            @endif
                        </span>
                    </div>

                    <div class="form-group">
                        <h3><strong>Select Your Practice Focus</strong></h3>
                        @foreach($practice_focus->chunk(26) as $chunk)
                        <div class="col-xs-6 no-padding">
                            @foreach($chunk as $practice)
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="practice_focus[]"
                                               value="{{ $practice->id}}"
                                                @if($user->therapistSurvey)
                                                    @foreach($user->therapistSurvey->practiceFocus as $practiceFocus)
                                                        {{ $practice->id == $practiceFocus->id ? "checked" : "" }}
                                                    @endforeach
                                                @endif
                                                {{ $edit ? "" : "disabled" }}
                                        >{{$practice->label}}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @endforeach
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <h3><strong>What age groups do you work with?</strong></h3>

                        @foreach($age_groups as $group)
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="age_groups[]"
                                           value="{{ $group->id}}"
                                            @if($user->therapistSurvey)
                                                @foreach($user->therapistSurvey->ageGroups as $ageGroup)
                                                    {{ $group->id == $ageGroup->id ? "checked" : "" }}
                                                @endforeach
                                            @endif
                                            {{ $edit ? "" : "disabled" }}
                                    >{{$group->label}}
                                </label>
                            </div>
                        @endforeach

                    </div>

                    <div class="form-group">
                        <h3><strong>Please select types of clients you work with</strong></h3>

                        @foreach($types_of_clients as $type)
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="types_of_clients[]"
                                           value="{{ $type->id}}"
                                            @if($user->therapistSurvey)
                                                @foreach($user->therapistSurvey->typesOfClients as $typeOfClient)
                                                    {{ $type->id == $typeOfClient->id ? "checked" : "" }}
                                                @endforeach
                                            @endif
                                            {{ $edit ? "" : "disabled" }}
                                    >{{$type->label}}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-group">
                        <h3><label for="languages">What languages can you conduct therapy in?</label></h3>
                        <input type="text" class="form-control" id="languages" name="languages"
                               value="{{ old('languages', isset($user->therapistSurvey) ? $user->therapistSurvey->languages : '')}}"
                               placeholder=""
                               required
                                {{ $edit ? "" : "disabled" }}
                        >
                    </div>

                    <div class="form-group">
                        <h3><label for="help_description">How do you help your clients?</label></h3>
                        <span>Please describe what specifically do you do to help your clients;
                            what can they expect as a result of their work with you.
                            Please, be as specific as possible in your explanation.
                            Do not use professional jargon. The clearer the message is to the client,
                            the more attracted they will be to working with you.</span>
                        <textarea class="form-control" rows="5" id="help_description" name="help_description"
                                  placeholder=""
                                  required
                                {{ $edit ? "" : "disabled" }}
                        >{{ old('help_description', isset($user->therapistSurvey) ? $user->therapistSurvey->help_description : '')}}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>

@endsection