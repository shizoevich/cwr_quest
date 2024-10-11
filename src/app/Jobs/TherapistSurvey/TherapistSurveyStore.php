<?php

namespace App\Jobs\TherapistSurvey;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TherapistSurvey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TherapistSurveyStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    protected $photoFile;

    /**
     * TherapistSurveyStore constructor.
     *
     * @param $data
     */
    public function __construct($data, $photoFile)
    {
        $this->data = $data;
        $this->photoFile = $photoFile;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        $ageGroups = null;
        $typesOfClients = null;
        $patientCategories = null;
        $ethnicities = null;
        $languages = null;
        $races = null;
        $specialties = null;
        $treatmentTypes = null;
        $insurances = null;

        $therapistSurvey = TherapistSurvey::where('user_id', $this->data['user_id'])->first();

        if(key_exists('phone', $this->data)) {
            if(key_exists('user_id', $this->data) && !empty($this->data['user_id'])) {
                $user = User::findOrFail($this->data['user_id']);
            } else {
                $user = Auth::user();
            }
            $provider = $user->provider;
            if(!is_null($provider)) {
                $provider->phone = str_replace('-', '', $this->data['phone']);
                $provider->save();
            }
        }

        if(key_exists('age_groups', $this->data)) {
            $ageGroups = $this->data['age_groups'];
        }

        if(key_exists('types_of_clients', $this->data)) {
            $typesOfClients = $this->data['types_of_clients'];
        }

        if(key_exists('patient_categories', $this->data)) {
            $patientCategories = $this->data['patient_categories'];
        }

        if(key_exists('ethnicities', $this->data)) {
            $ethnicities = $this->data['ethnicities'];
        }

        if(key_exists('languages_tridiuum', $this->data)) {
            $languages = $this->data['languages_tridiuum'];
        }

        if(key_exists('races', $this->data)) {
            $races = $this->data['races'];
        }

        if(key_exists('specialties', $this->data)) {
            $specialties = $this->data['specialties'];
        }

        if(key_exists('treatment_types', $this->data)) {
            $treatmentTypes = $this->data['treatment_types'];
        }

        if (key_exists('insurances', $this->data)) {
            $insurances = $this->data['insurances'];
        }
        
        if ($this->photoFile) {
            $extension = $this->photoFile->getClientOriginalExtension();
            $newFileName = md5(uniqid(time())) . '.' . $extension;

            Storage::disk('therapists_photos')->put($newFileName, file_get_contents($this->photoFile));

            $this->data['original_photo_name'] = $this->photoFile->getClientOriginalName();
            $this->data['aws_photo_name'] = $newFileName;

            if ($therapistSurvey && $therapistSurvey->aws_photo_name) {
                Storage::disk('therapists_photos')->delete($therapistSurvey->aws_photo_name);
            }
        } else if (!$this->data['photo_name'] && $therapistSurvey && $therapistSurvey->aws_photo_name) {
            Storage::disk('therapists_photos')->delete($therapistSurvey->aws_photo_name);

            $this->data['original_photo_name'] = null;
            $this->data['aws_photo_name'] = null;
        }

        $this->data['complete_education'] = Carbon::parse(trim($this->data['complete_education']));

        $this->data['late_cancelation_fee'] = $this->data['late_cancelation_fee'] ?? 0;

        $this->data['is_accept_video_appointments'] = $this->data['is_accept_video_appointments'] ?? 0;

        $this->data['group_npi'] = $this->data['group_npi'] !== '' ? $this->data['group_npi'] : null;

        unset(
            $this->data['age_groups'],
            $this->data['types_of_clients'],
            $this->data['_token'],
            $this->data['patient_categories'],
            $this->data['ethnicities'],
            $this->data['languages_tridiuum'],
            $this->data['races'],
            $this->data['specialties'],
            $this->data['treatment_types'],
            $this->data['photo_name']
        );

        if ($therapistSurvey) {
            $therapistSurvey->update($this->data);
        } else {
            $therapistSurvey = TherapistSurvey::create($this->data);
        }
        $therapistSurvey->user_id = $this->data['user_id'];

        if(!is_null($ageGroups)) {
            $therapistSurvey->ageGroups()->sync($ageGroups);
        } else {
            $therapistSurvey->ageGroups()->detach();
        }

        if(!is_null($typesOfClients)) {
            $therapistSurvey->typesOfClients()->sync($typesOfClients);
        } else {
            $therapistSurvey->typesOfClients()->detach();
        }

        if(!is_null($patientCategories)) {
            $therapistSurvey->patientCategories()->sync($patientCategories);
        } else {
            $therapistSurvey->patientCategories()->detach();
        }

        if(!is_null($ethnicities)) {
            $therapistSurvey->ethnicities()->sync($ethnicities);
        } else {
            $therapistSurvey->ethnicities()->detach();
        }

        if(!is_null($languages)) {
            $therapistSurvey->languagesTridiuum()->sync($languages);
        } else {
            $therapistSurvey->languagesTridiuum()->detach();
        }

        if(!is_null($races)) {
            $therapistSurvey->races()->sync($races);
        } else {
            $therapistSurvey->races()->detach();
        }

        if(!is_null($specialties)) {
            $therapistSurvey->specialties()->sync($specialties);
        } else {
            $therapistSurvey->specialties()->detach();
        }

        if(!is_null($treatmentTypes)) {
            $therapistSurvey->treatmentTypes()->sync($treatmentTypes);
        } else {
            $therapistSurvey->treatmentTypes()->detach();
        }

        if (!is_null($insurances)) {
            $therapistSurvey->insurances()->sync($insurances);
        } else {
            $therapistSurvey->insurances()->detach();
        }

        $therapistSurvey->save();

        return $therapistSurvey;
    }
}
