<template>
    <div>
        <div class="modal modal-vertical-center fade" id="note" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <div class="black-layer" v-if="statuses && statuses.saving">
                        <pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>
                    </div>
                    <div class="modal-header">
                        <button type="button" class="close" aria-label="Close" @click.prevent="closeNote">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="add-patient-progress-note-label" v-html="computed_modal_title"></h4>
                        <small>Date of Documentation: {{ formattedDateOfDocument }}</small>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note" id="form-note" novalidate>
                                <div class="row form-note-row">
                                    <div class="form-group col-md-3">
                                        <label for="first_name" class="control-label">Firstname</label>
                                        <input type="text" id="first_name" class="form-control fix-row"
                                               maxlength="100" disabled
                                               required :value="first_name" @change="setChangedStatus">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="last_name" class="control-label">Lastname</label>
                                        <input type="text" id="last_name" class="form-control fix-row"
                                               maxlength="100" disabled
                                               required :value="last_name" @change="setChangedStatus">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="date_of_birth" class="control-label">Date of Birth</label>
                                        <input type="text" id="date_of_birth" class="form-control fix-row"
                                               maxlength="100" disabled
                                               required :value="formattedDateOfBirth">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="control-label" style="z-index:150;">Date of Service</label>
                                        <select
                                            v-if="patient"
                                            id="date-of-service-select"
                                            class="form-control"
                                            v-model="dateOfServiceSelected"
                                            :disabled="!isDOSEditingAllowed"
                                            @change="handleDateOfServiceChange"
                                            style="padding-left:8px;padding-right:0px;appearance:none;"
                                        >
                                            <option 
                                                v-for="option in dateOfServiceOptions"
                                                :value="option.value" 
                                                :key="option.value"
                                            >
                                                {{ option.text }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-md-6">
                                        <label for="provider_name" class="control-label">Provider Name</label>
                                        <input type="text" id="provider_name" class="form-control fix-row"
                                               maxlength="100" required :value="provider_name" disabled
                                               @change="setChangedStatus">
                                    </div>
                                    <div class="form-group col-md-6 fix-row-2">
                                        <label for="provider_license_no" class="control-label">Provider License No.</label>
                                        <input type="text" id="provider_license_no" class="form-control"
                                               maxlength="100" :disabled="!isEditingAllowed"
                                               required v-model="provider_license_no" @change="setChangedStatus">
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-md-6">
                                        <label for="long_range_treatment_goal" class="control-label">
                                            Long range Treatment Goal
                                        </label>
                                        <textarea id="long_range_treatment_goal"
                                                  class="form-control vertical-resize fix-row large-bottom-padding"
                                                  data-autosize="true"
                                                  minlength="80"
                                                  maxlength="1000"
                                                  required
                                                  :disabled="!isEditingAllowed"
                                                  v-model="long_range_treatment_goal"
                                                  @change="setChangedStatus"></textarea>
                                        <span class="help-block with-errors">
                                            
                                        </span>
                                    </div>
                                    <div class="form-group col-md-6 fix-row-2">
                                        <label for="shortterm_behavioral_objective" class="control-label">
                                            Short term Behavioral Objective(s)
                                        </label>
                                        <textarea
                                            id="shortterm_behavioral_objective"
                                            class="form-control vertical-resize large-bottom-padding"
                                            data-autosize="true"
                                            minlength="80"
                                            maxlength="1000"
                                            required
                                            :disabled="!isEditingAllowed"
                                            v-model="shortterm_behavioral_objective"
                                            @change="setChangedStatus"
                                        ></textarea>
                                        <span class="help-block with-errors"></span>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div id="treatment-modality-container" class="form-group form-group-bordered col-md-3 fix-row-1">
                                        <label class="control-label">Treatment Modality</label>

                                        <div class="group fw-group">
                                            <label class="checkbox-inline wo-pl">
                                                <!--&#8242;-->
                                                <select
                                                    class="dropdown-form-control"
                                                    v-model="treatment_modality_id"
                                                    :disabled="true"
                                                    @change="setChangedStatus"
                                                >
                                                    <option value="" disabled></option>
                                                    <option 
                                                        v-for="option in treatmentModalities" 
                                                        :key="option.id"
                                                        :value="option.id"
                                                    >
                                                        {{ option.name }}
                                                    </option>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group form-group-bordered col-md-4">
                                        <label class="control-label">Session time</label>
                                        <div class="group">
                                            <label class="checkbox-inline wo-pl">
                                                Start time:
                                                <input readonly id="start_time_picker" type="text" :disabled="true"
                                                       v-model="start_time"
                                                       class="form-control form-control-xs text-sm with-checkbox"
                                                       maxlength="8" @change="setChangedStatus" required>
                                            </label>
                                            <label class="checkbox-inline wo-pl">
                                                End time:
                                                <input readonly id="end_time_picker" type="text" :disabled="true"
                                                       v-model="end_time"
                                                       class="form-control form-control-xs text-sm with-checkbox"
                                                       maxlength="8" @change="setChangedStatus" required>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label class="control-label">Diagnosis and ICD code</label>
                                        <div class="fastselect-disabled" v-if="!isDiagnosesEditingAllowed"></div>

                                        <diagnoses-multiselect
                                            v-if="selected_diagnoses"
                                            id="diagnoseMultipleSelect"
                                            :selectedDiagnoses="selected_diagnoses"
                                            customClass="multiselect-blue diagnoses-multiselect document-diagnoses-multiselect"
                                            @setDiagnoses="setDiagnoses"
                                        ></diagnoses-multiselect>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label">Current Status</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="depression" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Depression
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="anxiety" @change="setChangedStatus" :disabled="!isEditingAllowed">
                                                                Anxiety
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="anger_outbursts" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Anger outbursts
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="impaired_reality" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Impaired reality
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control disorientation"
                                                                       v-model="disorientation" :disabled="!isEditingAllowed"
                                                                       @change="disorientationChange">
                                                                Disorientation
                                                            </label>
                                                            <div class="radio-group disorientation-status" v-if="disorientation">
                                                                <label class="radio">
                                                                    <input type="radio" value="T"
                                                                           v-model="disorientation_status" :disabled="!isEditingAllowed"
                                                                           @change="disorientationStatusChange">
                                                                    Time
                                                                </label>
                                                                <label class="radio">
                                                                    <input type="radio" value="PL"
                                                                           v-model="disorientation_status" :disabled="!isEditingAllowed"
                                                                           @change="disorientationStatusChange">
                                                                    People
                                                                </label>
                                                                <label class="radio">
                                                                    <input type="radio" value="P"
                                                                           v-model="disorientation_status" :disabled="!isEditingAllowed"
                                                                           @change="disorientationStatusChange">
                                                                    Place
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="withdrawal" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Withdrawal
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="panic_prone" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Panic prone
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="verbally_abusive" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Verbally abusive
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="delusions" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Delusions
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="limited_self_expression" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Limited self expression
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="disturbed_sleep" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Disturbed sleep
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="worrisome_thinking" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Worrisome thinking
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="physically_abusive" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Physically abusive
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="hallucinations_vis" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Hallucinations, vls.
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="limited_memory" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Limited memory
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="disturbed_eating" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Disturbed eating
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="phobic_avoidance" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Phobic avoidance
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="irritable" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Irritable
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="hallucinations_aud" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Hallucinations, aud.
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="limited_concentration" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Limited concentration
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="tearfulness" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Tearfulness
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="agitated" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Agitated
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="disruptive_vocalizing" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Disruptive vocalizing
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="danger_to_self" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Danger to self
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="limited_judgment" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Limited judgment
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="hopelessness" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Hopelessness
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="restless_tension" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Restless tension
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="interpersonal_conflict" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Interpersonal
                                                                conflict
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-form-control"
                                                                       v-model="danger_to_others" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Danger to others
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="limited_attention" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Limited attention
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="flat_affect" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Flat affect
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="fearfulness" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Fearfulness
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="emotionally_labile" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Emotionally labile
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="disordered_thinking" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Disordered thinking
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="limited_info_processing" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Limited info processing
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox"
                                                                       class="checkbox-form-control"
                                                                       v-model="other_status" :disabled="!isEditingAllowed"
                                                                       @change="setChangedStatus">
                                                                Other
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-md-6 fix-row-1">
                                        <label for="additional_comments" class="control-label">
                                            Additional Comments
                                        </label>
                                        <textarea id="additional_comments"
                                                  class="form-control vertical-resize large-bottom-padding"
                                                  rows="5"
                                                  data-autosize="true"
                                                  :disabled="!isEditingAllowed"
                                                  v-model="additional_comments"
                                                  @change="setChangedStatus"></textarea>
                                    </div>
                                    <div class="form-group col-md-6 fix-row-1">
                                        <label for="plan" class="control-label">
                                            Plan
                                        </label>
                                        <textarea id="plan"
                                                  class="form-control vertical-resize large-bottom-padding"
                                                  rows="5"
                                                  minlength="50"
                                                  data-autosize="true"
                                                  :disabled="!isEditingAllowed"
                                                  required
                                                  v-model="plan"
                                                  @change="setChangedStatus"></textarea>
                                        <span class="help-block with-errors">
                                            
                                        </span>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-md-6">
                                        <label for="interventions" class="control-label">
                                            Interventions
                                        </label>
                                        <textarea id="interventions"
                                                  class="form-control vertical-resize fix-row large-bottom-padding"
                                                  rows="5"
                                                  minlength="80"
                                                  data-autosize="true"
                                                  :disabled="!isEditingAllowed"
                                                  required
                                                  v-model="interventions"
                                                  @change="setChangedStatus"></textarea>
                                        <span class="help-block with-errors">
                                            
                                        </span>
                                    </div>
                                    <div class="form-group col-md-6 fix-row-2">
                                        <label for="progress_and_outcome" class="control-label">
                                            Progress and Outcome
                                        </label>
                                        <textarea id="progress_and_outcome"
                                                  class="form-control vertical-resize large-bottom-padding"
                                                  rows="5"
                                                  minlength="150"
                                                  data-autosize="true"
                                                  :disabled="!isEditingAllowed"
                                                  required
                                                  v-model="progress_and_outcome" @change="setChangedStatus"></textarea>
                                        <span class="help-block with-errors">
                                            
                                        </span>
                                    </div>
                                </div>
                                <div class="row form-note-row" style="margin-top:15px;" id="pn-confirm-diagnoses">
                                    <p>IMPORTANT! Please make sure that correct ICD Code(s) has been selected. You can change this entry at a later time, but only until the billing has been submitted for this visit. After you will only be able to change ICD codes for future visits of this patient.</p>
                                    <label class="control-label" style="font-weight:normal;">
                                        <input type="checkbox" v-model="statuses.confirm_diagnoses"> I understand and confirm the ICD code(s) are correct
                                    </label>
                                </div>
                                <div class="form-note-button-block">
                                    <div class="row form-note-row">
                                        <div class="hidden-md hidden-lg col-xs-12 text-center" style="padding-right:0;margin-bottom:15px;">
                                            <span class="text-red validation-error-msg" v-if="statuses.noErrors === false">
                                                Please make sure you have filled all the required fields.
                                            </span>
                                        </div>
                                        <div class="col-xs-2 col-md-1" style="padding-left:0;">
                                            <button v-if="!isUserAdmin && currentNote && (!currentNote.is_finalized || currentNote.is_finalized == 0)"
                                                    type="button" class="btn btn-danger" @click="confirmClearingNote">
                                                Delete
                                            </button>
                                        </div>

                                        <div class="col-xs-10 col-md-11 text-right" style="padding-right:0;">
                                            <div class="hidden-xs hidden-sm col-md-6" style="padding-right:0;">
                                                <span class="text-red validation-error-msg" v-if="statuses.noErrors === false">
                                                    Please make sure you have filled all the required fields.
                                                </span>
                                                <span class="text-red validation-error-msg" v-if="validation_message">
                                                    {{ validation_message }}
                                                </span>
                                                <span class="autosave-notification" v-if="showTime">
                                                    Auto Saved At {{ throttle_save.last_save_time }}
                                                </span>
                                            </div>

                                            <div class="col-xs-12 col-md-6" style="padding-right:0;">
                                                <button v-if="!unlockRequestIsSent && !isEditingAllowed && !isUserAdmin && !isSupervisorMode" type="submit" class="btn btn-primary"
                                                        @click.prevent="showSendUnlockEditingRequestModal">
                                                    Request editing unlock
                                                </button>
                                                <button v-if="unlockRequestIsSent && !isEditingAllowed && !isUserAdmin && !isSupervisorMode" type="submit" class="btn btn-danger"
                                                        @click.prevent="showCancelUnlockEditingRequestModal">
                                                    Cancel progress note unlock request
                                                </button>
                                                <button v-if="currentNote && (!currentNote.is_finalized || currentNote.is_finalized == 0) && !isUserAdmin && !isSupervisorMode" type="submit" class="btn btn-primary"
                                                        @click.prevent="saveNote">Finalize this note
                                                </button>
                                                <button v-else-if="!isUserAdmin && !isSupervisorMode && isEditingAllowed" type="submit" class="btn btn-primary"
                                                        @click.prevent="updateNote">Update
                                                </button>
                                                <button v-if="currentNote && (!currentNote.is_finalized || currentNote.is_finalized == 0) && !isUserAdmin && !isSupervisorMode"
                                                        type="button" class="btn btn-default"
                                                        style="margin-left: 10px;"
                                                        @click.prevent="quickSaveNote(false)">
                                                    Save and finish later
                                                </button>
                                                <button type="button" class="btn btn-default"
                                                        style="margin-left: 10px;"
                                                        @click.prevent="closeNote"
                                                        v-else>
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!--/.modal-content-->
            </div>
        </div>
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-closing-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        Are you sure you want to close the window? All changes will be lost.
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="confirm-closing" class="btn btn-danger"
                                @click.prevent="closeNoteFromModal">Yes
                        </button>
                        <button type="button" class="btn btn-secondary" @click.prevent="closeNoteConfirmDialog">No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-clearing-note" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        If you proceed, all information in this progress note will be lost. Are you sure you want to delete this progress note?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger"
                                @click.prevent="deleteUnfinalizedNote">
                            Yes, delete this progress note
                        </button>
                        <button type="button" class="btn btn-secondary" @click.prevent="closeConfirmClearingDialog">
                            No, I want to keep this progress note
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="pn-quick-saved-info" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        All data entered in this progress note will be saved. You can return at a later time to finish entering data in all required fields in order to finalize this progress note. Please note: You have the option to delete this progress note until it has been finalized. In order to finalize a note all required fields must be entered. Once finalized, you will still have 72 hours to update the information, however, finalized progress notes can no longer be deleted.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <send-unlock-editing-request />
        <cancel-unlock-editing-request />
    </div>
</template>

<script>
    import TimePicker from "../mixins/pn-timepicker";
    import SendUnlockEditingRequest from "./chart/SendUnlockEditingRequest.vue";
    import CancelUnlockEditingRequest from "./chart/CancelUnlockEditingRequest.vue";

    export default {
        mixins: [TimePicker],

        components: {
            SendUnlockEditingRequest,
            CancelUnlockEditingRequest,
        },
        
        data() {
            return {
                "first_name": '',
                "last_name": '',
                "date_of_birth": '',
                "date_of_service": '',
                "formatted_dos": '',
                "provider_name": '',
                "provider_license_no": '',
                "long_range_treatment_goal": '',
                "shortterm_behavioral_objective": '',
                "treatment_modality_id": '',
                "depression": '',
                "withdrawal": '',
                "disturbed_sleep": '',
                "disturbed_eating": '',
                "tearfulness": '',
                "hopelessness": '',
                "flat_affect": '',
                "anxiety": '',
                "panic_prone": '',
                "worrisome_thinking": '',
                "phobic_avoidance": '',
                "agitated": '',
                "restless_tension": '',
                "fearfulness": '',
                "verbally_abusive": '',
                "physically_abusive": '',
                "irritable": '',
                "anger_outbursts": '',
                "disruptive_vocalizing": '',
                "interpersonal_conflict": '',
                "emotionally_labile": '',
                "impaired_reality": '',
                "delusions": '',
                "hallucinations_vis": '',
                "hallucinations_aud": '',
                "danger_to_self": '',
                "danger_to_others": '',
                "disordered_thinking": '',
                "disorientation": '',
                "disorientation_status": '',
                "limited_self_expression": '',
                "limited_memory": '',
                "limited_concentration": '',
                "limited_judgment": '',
                "limited_attention": '',
                "limited_info_processing": '',
                other_status: '',
                "additional_comments": '',
                "plan": '',
                "interventions": '',
                "progress_and_outcome": '',
                "signature_degree": '',
                "id": '',
                "patient_id": '',
                "appointment_id": '',
                is_autosave: false,
                statuses: {
                    data_is_changed: false,
                    saving: false,
                    noErrors: true,
                    end_time: {
                        h: null,
                        m: null,
                        meridian: null,
                        origin: {}
                    },
                    confirm_diagnoses: false,
                    diagnoses_editable: true,
                },
                validation_message: '',
                modal_title: '',
                is_note: false,
                throttle_save: {
                    note_created: false,
                    func: null,
                    data_changed: false,
                    last_save_time: null
                },
                selected_diagnoses: [],
                appointment: null,
                dateOfServiceOptions: [],
                dateOfServiceSelected: null,
            }
        },

        mounted() {
            this.getTreatmentModalities();

            let self = this;
            window.setTimeout(() => {
                $('#note').on('shown.bs.modal', function() {
                    $('body').addClass('custom-modal');

                    self.initTimePicker();

                    self.statuses.diagnoses_editable = ('diagnoses_editable' in self.currentNote)
                        ? self.currentNote.diagnoses_editable
                        : true;

                    self.selected_diagnoses = self.currentNote.diagnoses || [];

                    self.$nextTick(() => {
                        self.initDateOfService();

                        if (!self.currentNote.is_finalized) {
                            if (!self.currentNote.is_note) {
                                self.quickSaveNote(true);
                            } else {
                                self.throttle_save.note_created = true;
                            }
                            self.throttle_save.watcher = self.$watch('formData', () => {
                                self.throttle_save.data_changed = true;
                            });
                            self.throttle_save.data_changed = false;
                            self.throttle_save.func = window.setInterval(() => {
                                self.quickSaveIfDataChanged();
                            }, 15000);
                        }
                    });
                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                    self.clearSaveConfig();
                    if (window.onbeforeunload !== null) {
                        window.onbeforeunload = null;
                    }
                });
            },500);
        },

        computed: {
            showTime() {
                return this.throttle_save.last_save_time && this.statuses.noErrors;
            },

            formData() {
                let form = {};
                const exceptKeys = ['statuses', 'validation_message', 'modal_title', 'is_note', 'throttle_save'];
                for (let [key, value] of Object.entries(this.$data)) {
                    if (exceptKeys.includes(key)) {
                        continue;
                    }
                    form[key] = value;
                }

                return form;
            },

            fastselect_url() {
                return '/patient/' + this.patient.patient_id + '/diagnoses';
            },

            computed_modal_title() {
                if (this.modal_title) {
                    return this.modal_title;
                }

                if (this.isUserAdmin === true || this.isSupervisorMode || (this.currentNote !== null && !this.currentNote.is_finalized)) {
                    return 'Progress Note';
                }

                if (this.currentNote !== null && this.isEditingAllowed !== undefined && this.currentNote.isEditingAllowed !== undefined) {
                    if (!this.isEditingAllowed) {
                        return "<span class='pn-modal-head'>This progress note can no longer be edited. To request an unlock, please use the button at the bottom of the progress note form.</span>";
                    }

                    return "<span class='pn-modal-head'>You have " + this.currentNote.isEditingAllowed.hours + " hour(s) to finalize this progress note.</span>";
                }
            },

            isEditingAllowed() {
                let note = this.currentNote;
                
                if (note === null) {
                    return false;
                }
                
                if (note.isEditingAllowed === undefined) {
                    return !this.isUserAdmin && !this.isSupervisorMode;
                }

                return !this.isUserAdmin && !this.isSupervisorMode && note.isEditingAllowed.allowed;
            },

            isDOSEditingAllowed() {
                return this.isEditingAllowed && this.currentNote && !this.currentNote.is_finalized;
            },

            isDiagnosesEditingAllowed() {
                if (!this.statuses.diagnoses_editable) {
                    return false;
                }

                return (this.currentNote && !this.currentNote.is_finalized) || (this.currentNote && this.isEditingAllowed);
            },

            unlockRequestIsSent() {
                return !!this.currentNote && this.currentNote.unlock_request;
            },

            isUserAdmin() {
                return Boolean(this.$store.state.isUserAdmin);
            },

            isSupervisorMode() {
                return this.$store.state.is_read_only_mode && this.$store.state.is_supervisor_mode;
            },

            patient(){
                return this.$store.state.currentPatient;
            },
            patientId(){
                return this.patient && this.patient.id;
            },
            provider(){
                return this.$store.state.currentProvider;
            },
            currentNote(){
                return this.$store.state.currentNote;
            },
            currentNoteStatus(){
                return this.$store.state.isNoteBlank;
            },
            formattedDateOfBirth() {
                return this.date_of_birth ? moment(this.date_of_birth).format('MM/DD/YYYY') : '';
            },
            formattedDateOfDocument() {
                return this.currentNote && this.currentNote.finalized_at
                    ? moment(this.currentNote.finalized_at).format('MM/DD/YYYY')
                    : moment().format('MM/DD/YYYY');
            },
            first_name_length() {
                return this.allowedLength('input', this.first_name);
            },
            last_name_length() {
                return this.allowedLength('input', this.last_name);
            },
            provider_name_length() {
                return this.allowedLength('input', this.provider_name);
            },
            provider_license_no_length() {
                return this.allowedLength('input', this.provider_license_no);
            },
            long_range_treatment_goal_length() {
                return this.allowedLength('textarea', this.long_range_treatment_goal);
            },
            shortterm_behavioral_objective_length() {
                return this.allowedLength('textarea', this.shortterm_behavioral_objective);
            },
            treatmentModalities() {
                return this.$store.state.treatmentModalities;
            },
        },

        methods: {
            isSamePatient(newNote, oldNote) {
                if (!newNote || !oldNote) {
                    return false;
                }

                const keysToCompare = ["first_name", "last_name", "date_of_birth"];
                
                return keysToCompare.every((key) => newNote[key] === oldNote[key]);
            },

            handleDateOfServiceChange(event) {
                const selectedValue = event.target.value;
                const selectedItem = this.dateOfServiceOptions.find(option => option.value.toString() === selectedValue);
                if (!selectedItem) {
                    return;
                }
                
                this.appointment_id = selectedItem.value;
                this.start_time = selectedItem.start_time;
                this.end_time = selectedItem.end_time;
                this.treatment_modality_id = selectedItem.treatment_modality_id;
                this.date_of_service = selectedItem.date;

                this.statuses.diagnoses_editable = selectedItem.diagnoses_editable;
                if (selectedItem.diagnoses && selectedItem.diagnoses.length) {
                    this.selected_diagnoses = selectedItem.diagnoses;
                } else {
                    this.selected_diagnoses = this.currentNote.diagnoses || [];
                }

                $('#date-of-service-select').parents('.form-group').removeClass('error-focus');
            },

            initDateOfService() {
                if (!this.patientId) {
                    return;
                }

                $('#date-of-service-select').parents('.form-group').addClass('select-loader');
                axios.get('/patient/' + this.patientId + '/appointment-dates')
                    .then(response => {
                        this.dateOfServiceOptions = response.data || [];

                        const initialValues = {
                            text: this.formatted_dos,
                            value: this.appointment_id,
                            start_time: this.currentNote.start_time,
                            end_time: this.currentNote.end_time,
                            treatment_modality_id: this.currentNote.treatment_modality_id,
                            diagnoses_editable: ('diagnoses_editable' in this.currentNote)
                                ? this.currentNote.diagnoses_editable
                                : true
                        };
                        
                        const hasInitServiceOptions = this.dateOfServiceOptions.find(option => option.value === initialValues.value);
                        if (!hasInitServiceOptions && initialValues.value) {
                            this.dateOfServiceOptions.push(initialValues);
                        }
                        this.dateOfServiceSelected = initialValues.value;
                    })
                    .finally(() => {
                        $('#date-of-service-select').parents('.form-group').removeClass('select-loader');
                    });
            },

            setDiagnoses(diagnoses) {
                this.selected_diagnoses = diagnoses || [];
            },

            clearSaveConfig() {
                window.clearInterval(this.throttle_save.func);
                this.throttle_save.func = null;
                this.throttle_save.note_created = false;
                this.throttle_save.data_changed = false;
                this.throttle_save.last_save_time = null;
            },

            quickSaveIfDataChanged() {
                if (this.throttle_save.data_changed) {
                    this.quickSaveNote(true);
                }
            },

            confirmClearingNote() {
                if(!this.statuses.data_is_changed && !this.is_note) {
                    this.clearPnValidationErrors();
                    setTimeout(() => {
                        this.closeNoteFromModal();
                    }, 150);
                    return false;
                }
                $('#confirm-clearing-note').modal('show').css('display', 'inline-block');
            },

            setChangedStatus() {
                if(this.statuses) {
                    this.statuses.data_is_changed = true;
                }
            },

            disorientationChange() {
                this.setChangedStatus();
                if(!this.disorientation) {
                    this.disorientation_status = undefined;
                }
            },

            disorientationStatusChange() {
                this.setChangedStatus();
                if(this.disorientation) {
                    let checkedCount = $('.form-note-row div.checkbox-group[data-required="one"] input[type=checkbox]:checked').length;
                    if(this.disorientation_status && checkedCount > 0) {
                        $('.form-note-row div.checkbox-group[data-required="one"]').parents('.form-group').removeClass('error-focus');
                    }
                }
            },

            allowedLength(fieldType, field) {
                let maxLen = 0;
                switch (fieldType) {
                    case 'input':
                        maxLen = 100;
                        break;
                    case 'textarea':
                        maxLen = 400;
                        break;
                    default:
                        maxLen = fieldType;
                }
                let len = 0;
                if (field !== undefined && field !== null) {
                    len = field.length;
                }
                return maxLen - len;
            },

            closeNote() {
                if (this.statuses.data_is_changed) {
                    $('#confirm-closing-modal').modal('show').css('display', 'inline-block');
                } else {
                    this.closeNoteFromModal();
                }
            },

            closeNoteFromModal(showQuickSaveInfo = false) {
                this.clearPnValidationErrors();
                $('#confirm-closing-modal').modal('hide');
                $('body').removeClass('custom-modal');
                $('#form-note input, #form-note textarea').each(function (i, element) {
                    $(element).parents('.form-group').removeClass('error-focus');
                    $(element).parents('.form-group').removeClass('focus');
                });
                this.$store.commit('clearCurrentNote');
                this.$store.commit('resetStatuses');
                this.$store.commit('setNoteBlankStatus', true);
                
                this.destroyTimePicker();

                $('#send-unlock-editing-request-modal').modal('hide');
                $('#cancel-unlock-editing-request-modal').modal('hide');
                $('#confirm-clearing-note').modal('hide');
                $('#note').modal('hide');

                if (showQuickSaveInfo) {
                    $('#pn-quick-saved-info').modal('show');
                }
            },

            closeNoteConfirmDialog() {
                $('#confirm-closing-modal').css('display', 'none');
            },

            closeConfirmClearingDialog() {
                $('#confirm-clearing-note').css('display', 'none');
            },

            deleteUnfinalizedNote() {
                let data = {
                    noteId: this.id | this.currentNote.id,
                    patientId: this.patient.id,
                };
                this.$store.dispatch('deleteUnfinalizedNote', data).then(response => {
                    this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                    this.$store.dispatch('getPatientVisitCreatedAppointments', this.patient.id);
                    if (this.is_note) {
                        this.updateSidebar();
                    }
                    setTimeout(() => {
                        this.closeNoteFromModal();
                    }, 150);
                });
            },

            clearPnValidationErrors() {
                $('#note .error-focus').removeClass('error-focus');
                this.statuses.noErrors = true;
                this.validation_message = '';
            },

            quickSaveNote(silent = false) {
                if (!silent) {
                    this.clearPnValidationErrors();
                    this.statuses.saving = true;
                } else {
                    this.is_autosave = true;
                }
                if (!this.disorientation) {
                    this.disorientation_status = "";
                }

                // @todo remove
                try {
                    this.logAdditionalData(`METHOD: quickSaveNote (silent=${silent}); PAYLOAD: ${JSON.stringify(this.$data)}`);
                } catch (err) {}

                const data = _.cloneDeep(this.$data);

                this.$store.dispatch('quickSavePatientNote', data).then(savingResponse => {
                    // @todo remove
                    try {
                        this.logAdditionalData(`METHOD: quickSaveNote (silent=${silent}); RESPONSE_STATUS: ${savingResponse && savingResponse.status}; RESPONSE: ${JSON.stringify(savingResponse)}`);
                    } catch (err) {}

                    if (savingResponse && (savingResponse.status === 200 || savingResponse.status === 201)) {
                        if ((silent && !this.throttle_save.note_created) || !silent) {
                            this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                            // this.$store.dispatch('getPatientAppointments', this.patient.id);
                            this.$store.dispatch('getPatientVisitCreatedAppointments', this.patient.id);
                            if (!this.is_note) {
                                this.updateSidebar();
                            }
                        }
                        if (!silent) {
                            setTimeout(() => {
                                this.closeNoteFromModal(true);
                            }, 150);
                        } else {
                            // @todo remove
                            try {
                                this.logAdditionalData(`METHOD: quickSaveNote (silent=${silent}); NOTE_CREATED: ${this.throttle_save.note_created}; NOTE_ID: ${savingResponse.data.id}`);
                            } catch (err) {}

                            if (!this.throttle_save.note_created) {
                                this.$set(this, 'id', savingResponse.data.id);
                                this.throttle_save.note_created = true;
                            }
                            this.$set(this, 'is_note', true);
                            this.throttle_save.last_save_time = this.$moment().format('hh:mm A');
                            this.$nextTick(() => {
                                this.throttle_save.data_changed = false;
                            });
                        }
                    } else {
                        this.statuses.saving = false;
                        if(savingResponse.status === 500) {
                            this.validation_message = 'Saving Progress Note failed. Please try again.';
                        }
                    }
                })
                .catch((e) => {
                    // @todo remove
                    try {
                        this.logAdditionalData(`METHOD: quickSaveNote (silent=${silent}); ERROR: ${e}`);
                    } catch (err) {}

                    this.$nextTick(() => {
                        this.throttle_save.data_changed = false;
                    });
                });
            },

            saveOrUpdateNote(dispatcher) {
                this.statuses.noErrors = true;
                $('.form-note-row input[type=text], .form-note-row textarea').each((i, element) => {
                    let elementVal = $(element).val().trim();
                    let minLength = +$(element).attr('minlength');

                    if (minLength && elementVal.length < minLength) {
                        $(element).parents('.form-group').removeClass('focus');
                        $(element).parents('.form-group').addClass('error-focus');
                        $(element).parents('.form-group').find('.help-block.with-errors').text(`The field must be at least ${minLength} characters.`);
                        this.statuses.noErrors = false;
                    } else if ($(element).prop('required') && elementVal === '') {
                        $(element).parents('.form-group').removeClass('focus');
                        $(element).parents('.form-group').addClass('error-focus');
                        $(element).parents('.form-group').find('.help-block.with-errors').text('');
                        this.statuses.noErrors = false;
                    } else {
                        $(element).parents('.form-group').removeClass('error-focus');
                    }
                });

                if (!this.selected_diagnoses || !this.selected_diagnoses.length) {
                    $('#diagnoseMultipleSelect').parents('.form-group').addClass('error-focus');
                    this.statuses.noErrors = false;
                }

                if (!this.statuses.confirm_diagnoses) {
                    $('#pn-confirm-diagnoses label').addClass('text-red');
                    this.statuses.noErrors = false;
                }

                if (!this.date_of_service) {
                    $('#date-of-service-select').parents('.form-group').addClass('error-focus');
                    this.statuses.noErrors = false;
                }

                if (this.treatment_modality_id === '') {
                    $('#treatment-modality-container').removeClass('focus').addClass('error-focus');
                    this.statuses.noErrors = false;
                }

                let checkedCount = $('.form-note-row div.checkbox-group[data-required="one"] input[type=checkbox]:checked').length;
                let el = $('.form-note-row div.checkbox-group[data-required="one"]');

                if (checkedCount === 0) {
                    this.statuses.noErrors = false;
                    $(el).parents('.form-group').removeClass('focus');
                    $(el).parents('.form-group').addClass('error-focus');
                } else {
                    if (this.disorientation) {
                        if (!this.disorientation_status) {
                            this.statuses.noErrors = false;
                            $(el).parents('.form-group').removeClass('focus');
                            $(el).parents('.form-group').addClass('error-focus');
                        } else {
                            $(el).parents('.form-group').removeClass('error-focus');
                        }
                    } else {
                        $(el).parents('.form-group').removeClass('error-focus');
                    }
                }

                if (this.statuses.noErrors) {
                    this.statuses.saving = true;
                    if (!this.disorientation) {
                        this.disorientation_status = "";
                    }

                    // @todo remove
                    try {
                        this.logAdditionalData(`METHOD: saveOrUpdateNote (dispatcher=${dispatcher}); PAYLOAD: ${JSON.stringify(this.$data)}`);
                    } catch (err) {}

                    const data = _.cloneDeep(this.$data);

                    this.$store.dispatch(dispatcher, data).then(savingResponse => {
                        // @todo remove
                        try {
                            this.logAdditionalData(`METHOD: saveOrUpdateNote (dispatcher=${dispatcher}); RESPONSE_STATUS: ${savingResponse && savingResponse.status}; RESPONSE: ${JSON.stringify(savingResponse)}`);
                        } catch (err) {}

                        if (savingResponse && (savingResponse.status === 200 || savingResponse.status === 201)) {
                            this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                            this.$store.dispatch('getPatientVisitCreatedAppointments', this.patient.id);
                            if (dispatcher === 'savePatientNote') {
                                this.updateSidebar();
                                this.$store.dispatch('getPatientAppointments', this.patient.id);
                            }
                            setTimeout(() => {
                                this.closeNoteFromModal();
                            }, 150);
                        } else {
                            this.statuses.saving = false;
                            if (savingResponse.status !== 401) {
                                this.validation_message = 'Saving Progress Note failed. Please try again.';
                            }
                        }
                    })
                    .catch((e) => {
                        // @todo remove
                        try {
                            this.logAdditionalData(`METHOD: saveOrUpdateNote (dispatcher=${dispatcher}); ERROR: ${e}`);
                        } catch (err) {}
                    });
                }
            },

            updateSidebar() {
                let q = $('input#patient-fast-search').val();
                this.$store.dispatch('getProviderTodayPatients');
                this.$store.dispatch('getSearchedPatients', {query: q});
            },

            saveNote() {
                this.saveOrUpdateNote('savePatientNote');
            },

            updateNote() {
                this.saveOrUpdateNote('updatePatientNote');
            },

            getTreatmentModalities() {
                this.$store.dispatch('getTreatmentModalities');
            },
            
            showSendUnlockEditingRequestModal() {
                $('#send-unlock-editing-request-modal').modal('show').css('display', 'inline-block');
            },

            showCancelUnlockEditingRequestModal() {
                $('#cancel-unlock-editing-request-modal').modal('show').css('display', 'inline-block');
            },

            // @todo remove this method
            logAdditionalData(message) {
                if (!this.provider || this.provider.id !== 233) {
                    return;
                }

                const messagePrefix = `PATIENT_ID: ${this.patient && this.patient.id}; PROVIDER_ID: ${this.provider && this.provider.id} ======> `;

                this.$store.dispatch('captureFrontendMessage', {message: (messagePrefix + message)});
            }
        },

        filters: {
            formatDate(time) {
                if (time) {
                    return this.$moment(time).format('hh:mm A');
                }
                return '';
            }
        },

        watch: {
            patientId(newVal, oldVal) {
                // @todo remove
                try {
                    this.logAdditionalData(`WATCHER: patientId; OLD_VAL: ${oldVal}; NEW_VAL: ${newVal}`);
                } catch (err) {}

                if (this.patient === null) {
                    return;
                }

                this.first_name = this.patient.first_name;
                this.last_name = this.patient.last_name;
                this.date_of_birth = this.patient.date_of_birth;
                this.patient_id = this.patient.id;
                this.id = '';

                if (this.currentNote) {
                    this.$nextTick(() => this.initDateOfService());
                }
            },

            currentNote(newVal, oldVal) {
                // @todo remove
                try {
                    this.logAdditionalData(`WATCHER: currentNote; OLD_VAL: ${JSON.stringify(oldVal)}; NEW_VAL: ${JSON.stringify(newVal)}`);
                } catch (err) {}

                if (this.currentNote === null) {
                    return;
                }

                if (this.isSamePatient(newVal, oldVal) && this.id && !newVal.id) {
                    // fix of the problem with duplicated notes
                    return;
                }

                let tempData = this.$data;
                for (let prop in tempData) {
                    if (prop === 'throttle_save') {
                        continue;
                    }

                    this.$data[prop] = this.currentNote[prop] !== null ? this.currentNote[prop] : '';
                }

                this.initDateOfService();
            },

            provider() {
                if (this.currentProvider !== null) {
                    this.provider_name = this.provider.provider_name;
                }
            },

            selected_diagnoses() {
                if (this.selected_diagnoses && this.selected_diagnoses.length) {
                    $('#diagnoseMultipleSelect').parents('.form-group').removeClass('error-focus');
                }
                this.statuses.confirm_diagnoses = false;
            },

            'statuses.confirm_diagnoses'() {
                if (this.statuses.confirm_diagnoses) {
                    $('#pn-confirm-diagnoses label').removeClass('text-red');
                }
            },
        }
    }
</script>

<style scoped>
    .validation-error-msg {
        /*padding-right: 20px;*/
    }

    .fastselect-disabled {
        z-index:10000;
        position: absolute;
        height: 100%;
        width: 100%;
        cursor: not-allowed;
    }
</style>
