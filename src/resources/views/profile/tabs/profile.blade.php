<form class="profile-form" method="POST" action="{{route('profile.store')}}" novalidate enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="redirect" value="{{ $redirect }}">
    <input name="user_id" value="{{ $user->id }}" hidden/>

    <div class="alert alert-success" role="alert" id="success-alert" style="display: none">
        Profile saved successfully
    </div>

    <div class="alert alert-danger" role="alert" id="error-alert" style="display: none">
        Failed to save profile
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group{{ $errors->has('photo') ? ' has-error' : '' }}">
                <div>
                    <strong>External-Facing Image on Your Profile</strong>
                    <span style="float: right">Ideal Format: Square (e.g. 400 x 400)</span>
                </div>

                <label for="photo" style="height: 200px; width: 100%; padding: 4px; background: white; border: 1px solid rgb(160, 170,184); border-radius: 4px; margin-bottom: 0">
                    <div id="upload-button" class="w-100 d-flex align-items-center justify-content-center" style="height: 100%; {{ (isset($user->therapistSurvey) && $user->therapistSurvey->aws_photo_name) ? 'display: none' : ''}}">Upload file +</div>
                    <div id="preview" class="w-100 d-flex" style="height: 100%; justify-content: space-between; {{ (isset($user->therapistSurvey) && $user->therapistSurvey->aws_photo_name) ? '' : 'display: none' }}">
                        <img id="preview-image" src="{{ (isset($user->therapistSurvey) && $user->therapistSurvey->aws_photo_name) ? $user->therapistSurvey->getPhotoTemporaryUrl() : '#'}}" alt="Selected image" style="max-width: 500px; height: auto; max-height: 100%">
                        <div class="d-flex flex-column justify-content-center" style="gap: 10px">
                            <button id="upload-new-button" type="button" class="btn btn-primary">Upload new</button>
                            <button id="delete-button" type="button" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                </label>
                <input type="file" id="photo" name="photo" class="form-control-file" style="display: none" accept="image/*">
                <input type="text" id="photo_name" name="photo_name" hidden value="{{ (isset($user->therapistSurvey) && $user->therapistSurvey->aws_photo_name) ? $user->therapistSurvey->aws_photo_name : ''}}"/>

                <span class="help-block with-errors">
                    @if ($errors->has('photo'))
                        <strong>{{ $errors->first('photo') }}</strong>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="modal fade" id="canvas-modal" tabindex="-1" role="dialog" aria-labelledby="canvas-modal-label" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document" >
          <div class="modal-content">
            <div class="modal-header" style="padding: 10px 15px">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="cross">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body d-flex align-items-center" id="modal-body" style="min-height: 500px">
                <div class="loader" style="display: block; margin: auto;"></div>
                <div id="photo-editor">
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 15px">
              <button id='crope-button' type="button" class="btn btn-primary" data-dismiss="modal" style="width: 70px">Ok</button>
              <button id='cancel-button' type="button" class="btn btn-secondary" data-dismiss="modal" style="width: 70px">Cancel</button>
            </div>
          </div>
        </div>
      </div>	

    <div class="row">
        <div class="col-md-4">
            <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
                <label for="first_name" class="control-label required">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder=""
                       value="{{ old('first_name', isset($user->therapistSurvey) ? $user->therapistSurvey->first_name : '')}}"
                       required
                        {{ $edit ? '' : 'disabled' }}
                >
                <span class="help-block with-errors">
                    @if ($errors->has('first_name'))
                        <strong>{{ $errors->first('first_name') }}</strong>
                    @endif
                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group{{ $errors->has('middle_name') ? ' has-error' : '' }}">
                <label for="middle_name" class="control-label">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder=""
                       value="{{ old('middle_name', isset($user->therapistSurvey) ? $user->therapistSurvey->middle_name : '')}}"
                        {{ $edit ? '' : 'disabled' }}
                >
                <span class="help-block with-errors">
                    @if ($errors->has('middle_name'))
                        <strong>{{ $errors->first('middle_name') }}</strong>
                    @endif
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
                <label for="last_name" class="control-label required">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder=""
                       value="{{ old('last_name', isset($user->therapistSurvey) ? $user->therapistSurvey->last_name : '')}}"
                       required
                        {{ $edit ? '' : 'disabled' }}
                >
                <span class="help-block with-errors">
                    @if ($errors->has('last_name'))
                        <strong>{{ $errors->first('last_name') }}</strong>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group{{ $errors->has('personal_email') ? ' has-error' : '' }}">
                <label for="personal_email" class="control-label required">Email</label>
                <input type="email" class="form-control" id="personal_email" name="personal_email"
                       value="{{ old('personal_email', isset($user->therapistSurvey) ? $user->therapistSurvey->personal_email : '') }}"
                       required
                        {{ $edit ? "" : "disabled" }}
                >
                <span class="help-block with-errors">
                    @if ($errors->has('personal_email'))
                        <strong>{{ $errors->first('personal_email') }}</strong>
                    @endif
                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                <label for="phone" class="control-label required">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder=""
                       value="{{ old('phone', isset($user->provider) ? $user->provider->phone : '') }}"
                       required
                        {{ $edit ? '' : 'disabled' }}
                >
                <span class="help-block with-errors">
                    @if ($errors->has('phone'))
                        <strong>{{ $errors->first('phone') }}</strong>
                    @endif
                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group{{ $errors->has('credentials') ? ' has-error' : '' }}">
                <label for="credentials" class="control-label required">Credentials</label>
                <input type="text" class="form-control" id="credentials" name="credentials" placeholder=""
                       value="{{ old('credentials', isset($user->therapistSurvey) ? $user->therapistSurvey->credentials : '')}}"
                       required
                        {{ $edit ? '' : 'disabled' }}
                >
                <span class="help-block with-errors">
                    @if ($errors->has('credentials'))
                        <strong>{{ $errors->first('credentials') }}</strong>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="form-group{{ $errors->has('bio') ? ' has-error' : '' }}">
        <label for="bio" class="control-label">Bio</label>
        <textarea class="form-control" rows="5" id="bio" name="bio"
                  placeholder="Enter here" required
                {{ $edit ? '' : 'disabled' }}
        >{{ old('bio', isset($user->therapistSurvey) ? $user->therapistSurvey->bio : '')}}</textarea>
        <span class="help-block with-errors">
            @if ($errors->has('bio'))
                <strong>{{ $errors->first('bio') }}</strong>
            @endif
        </span>
    </div>

    

    <div class="form-group{{ $errors->has('is_accept_video_appointments') ? ' has-error' : '' }}">
        <h5><strong>I accept video appointments</strong></h5>
        <div class="radio-group">
            <div class="radio-group">
                <input type="radio" name="is_accept_video_appointments" value="1" id="is_accept_video_appointments-yes"
                        {{ (isset($user->therapistSurvey) && $user->therapistSurvey->is_accept_video_appointments) ? 'checked' : '' }}
                >
                <label for="is_accept_video_appointments-yes" class="normal-weight">Yes</label>
            </div>

            <div class="radio-group">
                <input type="radio" name="is_accept_video_appointments" value="0" id="is_accept_video_appointments-no"
                        {{ (isset($user->therapistSurvey) && ! $user->therapistSurvey->is_accept_video_appointments) ? 'checked' : '' }}
                >
                <label for="is_accept_video_appointments-no" class="normal-weight">No</label>
            </div>
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('is_accept_video_appointments'))
                <strong>{{ $errors->first('is_accept_video_appointments') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('years_of_practice') ? ' has-error' : '' }}">
        <label for="years_of_practice" class="control-label required">Years in Practice</label>
        <input type="number" class="form-control required"
               id="years_of_practice" name="years_of_practice"
               value="{{ old('years_of_practice', isset($user->therapistSurvey) ? $user->therapistSurvey->years_of_practice : '')}}"
               placeholder="" required
                {{ $edit ? '' : 'disabled' }}
        >
        <span>If you are just starting your career write 0 (zero)</span>
        <span class="help-block with-errors">
            @if ($errors->has('years_of_practice'))
                <strong>{{ $errors->first('years_of_practice') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('insurances') ? ' has-error' : '' }}">
        <label class="control-label">Insurances</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="insurances[]">
                @foreach($insurances as $insurance)
                    <option value="{{ $insurance->id }}"
                            @if(old('insurances') && in_array($insurance->id, old('insurances'))
                                || !old('insurances') && isset($user->therapistSurvey) && $user->therapistSurvey->insurances->contains('id', $insurance->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $insurance->insurance }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('specialties'))
                <strong>{{ $errors->first('specialties') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('specialties') ? ' has-error' : '' }}">
        <label class="control-label">Specialties</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="specialties[]">
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}"
                            @if(old('specialties') && in_array($specialty->id, old('specialties'))
                                || !old('specialties') && isset($user->therapistSurvey) && $user->therapistSurvey->specialties->contains('id', $specialty->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $specialty->label }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('specialties'))
                <strong>{{ $errors->first('specialties') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('age_groups') ? ' has-error' : '' }}">
        <label class="control-label">Client Focus</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="age_groups[]">
                @foreach($age_groups as $ageGroup)
                    <option value="{{ $ageGroup->id }}"
                            @if(old('age_groups') && in_array($ageGroup->id, old('age_groups'))
                                || !old('age_groups') && isset($user->therapistSurvey) && $user->therapistSurvey->ageGroups->contains('id', $ageGroup->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $ageGroup->label }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('age_groups'))
                <strong>{{ $errors->first('age_groups') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('treatment_types') ? ' has-error' : '' }}">
        <label class="control-label">Type of Therapy</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="treatment_types[]">
                @foreach($treatment_types as $treatmentType)
                    <option value="{{ $treatmentType->id }}"
                            @if(old('treatment_types') && in_array($treatmentType->id, old('treatment_types'))
                                || !old('treatment_types') && isset($user->therapistSurvey) && $user->therapistSurvey->treatmentTypes->contains('id', $treatmentType->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $treatmentType->label }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('treatment_types'))
                <strong>{{ $errors->first('treatment_types') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('types_of_clients') ? ' has-error' : '' }}">
        <label class="control-label">Modality</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="types_of_clients[]">
                @foreach($types_of_clients as $typeOfClient)
                    <option value="{{ $typeOfClient->id }}"
                            @if(old('types_of_clients') && in_array($typeOfClient->id, old('types_of_clients'))
                                || !old('types_of_clients') && isset($user->therapistSurvey) && $user->therapistSurvey->typesOfClients->contains('id', $typeOfClient->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $typeOfClient->label }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('types_of_clients'))
                <strong>{{ $errors->first('types_of_clients') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('languages_triduum') ? ' has-error' : '' }}">
        <label class="control-label">Language(s)</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="languages_tridiuum[]">
                @foreach($languages as $language)
                    <option value="{{ $language->id }}"
                            @if(old('languages_tridiuum') && in_array($language->id, old('languages_tridiuum'))
                                || !old('languages_tridiuum') && isset($user->therapistSurvey) && $user->therapistSurvey->languagesTridiuum->contains('id', $language->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $language->label }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
           @if ($errors->has('languages_triduum'))
                <strong>{{ $errors->first('languages_triduum') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('school') ? ' has-error' : '' }}">
        <label for="school" class="control-label required">Education</label>
        <textarea class="form-control" rows="3" id="school" name="school" placeholder=""
                  required
                {{ $edit ? '' : 'disabled' }}
        >{{ old('school', isset($user->therapistSurvey) ? $user->therapistSurvey->school : '')}}</textarea>
        <span class="help-block with-errors">
            @if ($errors->has('school'))
                <strong>{{ $errors->first('school') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('complete_education') ? ' has-error' : '' }}">
        <label for="complete_education" class="control-label required">Year of graduating</label>
        <input type="date" class="form-control" id="complete_education" name="complete_education"
               value="{{ old('complete_education', isset($user->therapistSurvey) ? \Carbon\Carbon::parse($user->therapistSurvey->complete_education)->toDateString() : '')}}"
               {{--placeholder="{{ old('complete_education', isset($user->therapistSurvey) ? \Carbon\Carbon::parse($user->therapistSurvey->complete_education)->toDateString() : '')}}"--}}
               required
                {{ $edit ? "" : "disabled" }}
        >
        <span class="help-block with-errors">
            @if ($errors->has('complete_education'))
                <strong>{{ $errors->first('complete_education') }}</strong>
            @endif
        </span>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group{{ $errors->has('races') ? ' has-error' : '' }}">
                <label class="control-label">Race</label>
                <div class="fastSelect-wrap">
                    <select class="multipleSelect fastSelect hidden" multiple
                            name="races[]">
                        @foreach($races as $race)
                            <option value="{{ $race->id }}"
                                    @if(old('races') && in_array($race->id, old('races'))
                                        || !old('races') && isset($user->therapistSurvey) && $user->therapistSurvey->races->contains('id', $race->id)
                                    )
                                        selected
                                    @endif
                                    {{ $edit ? '' : 'disabled' }}
                            >{{ $race->label }}</option>
                        @endforeach
                    </select>
                    <input class="form-control fastSelect-fake">
                </div>
                <span class="help-block with-errors">
            @if ($errors->has('races'))
                        <strong>{{ $errors->first('races') }}</strong>
                    @endif
        </span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group{{ $errors->has('ethnicities') ? ' has-error' : '' }}">
                <label class="control-label">Ethnicity</label>
                <div class="fastSelect-wrap">
                    <select class="multipleSelect fastSelect hidden" multiple
                            name="ethnicities[]">
                        @foreach($ethnicities as $ethnicity)
                            <option value="{{ $ethnicity->id }}"
                                    @if(old('ethnicities') && in_array($ethnicity->id, old('ethnicities'))
                                        || !old('ethnicities') && isset($user->therapistSurvey) && $user->therapistSurvey->ethnicities->contains('id', $ethnicity->id)
                                    )
                                        selected
                                    @endif
                                    {{ $edit ? '' : 'disabled' }}
                            >{{ $ethnicity->label }}</option>
                        @endforeach
                    </select>
                    <input class="form-control fastSelect-fake">
                </div>
                <span class="help-block with-errors">
            @if ($errors->has('ethnicities'))
                        <strong>{{ $errors->first('ethnicities') }}</strong>
                    @endif
        </span>
            </div>
        </div>
    </div>

    <div class="form-group{{ $errors->has('patient_categories') ? ' has-error' : '' }}">
        <label class="control-label">Categories</label>
        <div class="fastSelect-wrap">
            <select class="multipleSelect fastSelect hidden" multiple
                    name="patient_categories[]">
                @foreach($patient_categories as $category)
                    <option value="{{ $category->id }}"
                            @if(old('patient_categories') && in_array($category->id, old('patient_categories'))
                                || !old('patient_categories') && isset($user->therapistSurvey) && $user->therapistSurvey->patientCategories->contains('id', $category->id)
                            )
                                selected
                            @endif
                            {{ $edit ? '' : 'disabled' }}
                    >{{ $category->label }}</option>
                @endforeach
            </select>
            <input class="form-control fastSelect-fake">
        </div>
        <span class="help-block with-errors">
            @if ($errors->has('patient_categories'))
                <strong>{{ $errors->first('patient_categories') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group">
        <label for="npi" class="control-label">NPI</label>
        <input type="text" class="form-control" id="npi" name="npi"
               value="{{ isset($user->provider) ? $user->provider->individual_npi : '' }}"
               disabled
        >
    </div>

    <div class="form-group{{ $errors->has('group_npi') ? ' has-error' : '' }}">
        <label for="group_npi" class="control-label">Group NPI</label>
        <input type="text" class="form-control"
               id="group_npi" name="group_npi"
               value="{{ old('group_npi', isset($user->therapistSurvey) ? $user->therapistSurvey->group_npi : '')}}"
               placeholder="" required
                {{ $edit ? '' : 'disabled' }}
        >
        <span class="help-block with-errors">
            @if ($errors->has('group_npi'))
                <strong>{{ $errors->first('group_npi') }}</strong>
            @endif
        </span>
    </div>

    <div class="form-group{{ $errors->has('tridiuum_external_url') ? ' has-error' : '' }}">
        <div>
            <label for="tridiuum_external_url" class="control-label">
                URL to tridiuum account
            </label>
            <i class="button-question" id="button-question_tridiuum-url" tabindex="0"></i>
        </div>

        <input type="text" class="form-control"
               id="tridiuum_external_url" name="tridiuum_external_url"
               value="{{ old('tridiuum_external_url', isset($user->therapistSurvey) ? $user->therapistSurvey->tridiuum_external_url : '')}}"
               placeholder="" required
                {{ $edit ? "" : "disabled" }}
        >
        <span class="help-block with-errors">
            @if ($errors->has('tridiuum_external_url'))
                <strong>{{ $errors->first('tridiuum_external_url') }}</strong>
            @endif
        </span>
    </div>

    <div class="modal fade" id="tridiuum-url-modal" tabindex="-1" role="dialog" aria-labelledby="tridiuum-url-modal-label" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document" >
            <div class="modal-content">
                <div class="modal-header" style="padding: 10px 15px">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center justify-content-center position-relative" style="padding: 20px">
                    <video
                        id="tridiuum-url-guide"
                        class="video-js vjs-big-play-centered"
                        controls
                        preload="auto"
                        height="520"
                        data-setup="{}"
                    >
                        <source src="{{ $tridiuum_url_guide }}" type="video/mp4" />
                    </video>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group{{ $errors->has('help_description') ? ' has-error' : '' }}">
        <label for="help_description" class="control-label">How do you help your clients?</label>
        <br>
        <span>Please describe what specifically do you do to help your clients;
                            what can they expect as a result of their work with you.
                            Please, be as specific as possible in your explanation.
                            Do not use professional jargon. The clearer the message is to the client,
                            the more attracted they will be to working with you.</span>
        <textarea class="form-control" rows="5" id="help_description" name="help_description"
                  placeholder=""
                  required
                {{ $edit ? '' : 'disabled' }}
        >{{ old('help_description', isset($user->therapistSurvey) ? $user->therapistSurvey->help_description : '')}}</textarea>
        <span class="help-block with-errors">
            @if ($errors->has('help_description'))
                <strong>{{ $errors->first('help_description') }}</strong>
            @endif
        </span>
    </div>

    <el-button type='primary' id='save-button' native-type="submit">Save</el-button>

</form>

@section('scripts')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.3/cropper.js"></script>
    <script src="{{ asset('js/tabs-doctor-profile.js') }}"></script>
@endsection