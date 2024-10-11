<template>
    <div>
        <div class="black-layer" v-if="statuses && statuses.saving" style="position: fixed;
    top: 0;">
            <pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>
        </div>

        <div class="modal modal-vertical-center fade" :id="document_name" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="close" data-dismiss="modal"
                                        @click.prevent="closeDocument"
                                >&times;</button>
                                <h4 class="modal-title" v-html="computed_modal_title()"></h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 text-left">
                                Date: {{getFormattedDate()}}
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    Assessment/Diagnostic Interview
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document facey-rfr" novalidate>


                                <div class="row">
                                    <div class="col-lg-12 input-container table-container">
                                        <table class="table borderless status-table">
                                            <tbody>
                                            <tr>
                                                <td rowspan="2">
                                                    <div>
                                                        <label class="checkbox-inline without-checkbox">
                                                            Routine
                                                        </label>
                                                        <label class="checkbox-inline routine-1">
                                                            <input type="radio" class="checkbox-document-form-control"
                                                                   @change="setHasValue"
                                                                   v-model="form_data.service_status.routine"
                                                                   value="1"
                                                                   :disabled="statuses.editingDisabled"
                                                            >
                                                            1
                                                        </label>
                                                    </div>
                                                    <div>
                                                        <label class="checkbox-inline routine-2">
                                                            <input type="radio" class="checkbox-document-form-control"
                                                                   @change="setHasValue"
                                                                   v-model="form_data.service_status.routine"
                                                                   value="2"
                                                                   :disabled="statuses.editingDisabled"
                                                            >
                                                            2
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               v-model="form_data.service_status.urgent"
                                                               @change="setHasValue"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Urgent
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_status.retro"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Retro
                                                    </label>
                                                </td>
                                                <td rowspan="2">
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_status.ccmg"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        CCMG (SFV/SCV/SV All Potential Cardiac Consult/Flw up)
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_status.stat"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        STAT
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               :disabled="statuses.editingDisabled"
                                                               v-model="form_data.service_status.pr"
                                                        >
                                                        PR
                                                    </label>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>SERVICE REQUESTED</label>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 input-container table-container">
                                        <table class="table borderless service-table">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.consultation"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Consultation
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.follow_up"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Follow-up
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.second_opinion"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Second Opinion
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               v-model="form_data.service_requested.multiple_services.status"
                                                               @change="setHasValue"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Multiple Services
                                                    </label>
                                                    <div class="radio-group disorientation-status" v-show="form_data.service_requested.multiple_services.status">
                                                        <label class="radio">
                                                            <input type="radio"
                                                                   @change="setHasValue"
                                                                   :disabled="statuses.editingDisabled"
                                                                   value="2"
                                                                   v-model="form_data.service_requested.multiple_services.data"
                                                            >
                                                            2
                                                        </label>
                                                        <label class="radio">
                                                            <input type="radio"
                                                                   @change="setHasValue"
                                                                   :disabled="statuses.editingDisabled"
                                                                   value="3"
                                                                   v-model="form_data.service_requested.multiple_services.data"
                                                            >
                                                            3
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-lg-12 input-container table-container">
                                        <table class="table borderless more-services-table">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.allergy"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Allergy
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.hem"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Hem/Onc
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.high_risk"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Perinatal/High Risk (from OB only)
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.cardiology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Cardiology
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.infectious"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Infectious Disease
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.plastic"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Plastic Surgery
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.dermatology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Dermatology
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.nerphology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Nephrology
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.podiatry"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Podiatry
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.ent"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        ENT
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.neurology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Neurology
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.pulmonology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Pulmonology
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.endocrinology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Endocrinology
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.neurosurgery"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Neurosurgery
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.rad"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Rad/Onc
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.gi"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        GI
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.colon_soreening"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Colon Screening
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.ophtalmology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Ophthalmology
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.rheumatology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Rheumatology
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.general_surgery"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        General Surgery
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.orthopedics"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Orthopedics
                                                    </label>
                                                    <label class="checkbox-inline" style="margin-left: 0;">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.ortho"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Hand Ortho
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.urology"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Urology
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.gyn"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Gyn/Onc
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.pain_management"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Pain Management
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               @change="setHasValue"
                                                               v-model="form_data.service_requested.vascular"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Vascular Surgery
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="checkbox-document-form-control"
                                                               v-model="form_data.service_requested.other.status"
                                                               @change="setHasValue"
                                                               :disabled="statuses.editingDisabled"
                                                        >
                                                        Other
                                                    </label>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row first-input-row" v-show="form_data.service_requested.other.status">
                                    <document-textarea
                                            name="service_requested_other"
                                            label="Please specify"
                                            v-model="form_data.service_requested.other.data"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>OTHER SERVICES</label>
                                </div>

                                <div class="row">
                                    <div class="input-row services-input-row">
                                        <div class="col-sm-6 checkbox-group-bordered">
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.ankle"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Ankle Brachial Index</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.auditory.status"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Auditory Diagnostic (air, bone, speech)</label>
                                            </div>
                                            <div class="checkbox-level" v-show="form_data.other_services.auditory.status">
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.auditory.w_t"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >w/tympanograms (if ETD or CHL)</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.auditory.w_o"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >w/o tympanograms or pm CHL</label>
                                                </div>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.bone_dencity"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Bone Density (DEXA)*</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.bone_scan"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Bone Scan (nuclear medicine)*</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.breast"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Breast Biopsy</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.chemotherapy"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Chemotherapy</label>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox">Colonoscopy (G.L M.D. use only)</label>
                                            </div>
                                            <div class="checkbox-level checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.colonoscopy.screening"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Screening</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.colonoscopy.diagnostic"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Diagnostic</label>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox">Duplex</label>
                                            </div>
                                            <div class="checkbox-level checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.duplex.arterial"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Arterial</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.duplex.venous"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Venous</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.duplex.carotid"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Carotid</label>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="specify_extremity_1"
                                                        label="Specify Extremity"
                                                        v-model="form_data.other_services.duplex.specify"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.eeg"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >EEG</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.egd"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >EGD</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.echo"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Echocardiogram</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.home"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Home Health*</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.hospice"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Hospice*</label>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 checkbox-group-bordered">
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.hysteroscopy"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Hysteroscopy</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.injectable"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Injectable Meds*</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.ncv"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >NCV</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.emo"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >EMO</label>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="specify_extremity_2"
                                                        label="Specify Extremity"
                                                        v-model="form_data.other_services.specify"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.self_injectable.status"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Self-Injectable Meds*</label>
                                            </div>
                                            <div class="checkbox-level checkbox" v-show="form_data.other_services.self_injectable.status">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.self_injectable.education"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >with education</label>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox">Sleep Study</label>
                                            </div>
                                            <div class="checkbox-level checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.sleep_study.titration"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Titration</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.sleep_study.split_night"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Split Night</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.sleep_study.home"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Home</label>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox">Stress Tests</label>
                                            </div>
                                            <div class="checkbox-level">
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.stress_tests.adenosine"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >adenosine cardiolyte</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.stress_tests.dobutamine"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >dobutamine-echo</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.stress_tests.cardiolyte"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >stress-cardiolyte</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.stress_tests.echo"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >stress-echo</label>
                                                </div>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.surgery.status"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Surgery</label>
                                            </div>
                                            <div class="row" v-show="form_data.other_services.surgery.status">
                                                <document-textarea
                                                        name="surgery"
                                                        label="Please specify"
                                                        v-model="form_data.other_services.surgery.data"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox">Therapy*:</label>
                                            </div>
                                            <div class="col-lg-12 checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.therapy.pt"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >PT</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.therapy.ot"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >OT</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.therapy.speech"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Speech</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="input-row services-input-row">
                                        <div class="col-sm-6 checkbox-group-bordered">
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.other_services.status"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Other Services</label>
                                            </div>
                                            <div class="row" v-show="form_data.other_services.other_services.status">
                                                <document-textarea
                                                        name="specify"
                                                        label="Please specify"
                                                        v-model="form_data.other_services.other_services.data"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="cpt_codes"
                                                        label="CPT Codes"
                                                        v-model="form_data.other_services.cpt_codes"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.ct_sinus"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >CT Sinus w/ Medtronic Navigation Fusion Protocol*</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.ct_stone"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >CT Stone Survey</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.ct"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >CT*</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.cta"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >CTA*</label>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="specify_body_1"
                                                        label="Specify Body Part"
                                                        v-model="form_data.other_services.body_part.data"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox-level checkbox">
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part.w_o"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >W/o contrast</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part.w_contrast"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >W/ contrast</label>
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part.metlomin"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >On Metformin</label>
                                                </div>
                                                <div class="checkbox-level checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part.bun.status"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >IV (BUN/Cr)</label>
                                                </div>
                                            </div>
                                            <div class="row" v-show="form_data.other_services.body_part.bun.status">
                                                <document-textarea
                                                        name="bun_cr_1"
                                                        label="Please specify"
                                                        v-model="form_data.other_services.body_part.bun.data"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.dme"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >DME</label>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="o_liters"
                                                        label="O<sub>2</sub> Liters"
                                                        v-model="form_data.other_services.o_liters"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="o_sat"
                                                        label="O<sub>2</sub> Sat"
                                                        v-model="form_data.other_services.o_sat"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox">% by</label>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.abg"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >ABG or</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.pulse_ox"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Pulse Ox</label>
                                            </div>
                                        </div>


                                        <div class="col-sm-6 checkbox-group-bordered">
                                            <div class="row first-input-row">
                                                <document-textarea
                                                        name="cpap"
                                                        label="CPAP: Settings (Sleep, Study, Rpt, Req)"
                                                        v-model="form_data.other_services.cpap"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.nebulizer"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >Nebulizer (For drugs, follow <u>below</u>)</label>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox"><u>Senior</u>: Attach Med R<sub>x</sub> to SRF</label>
                                            </div>
                                            <div class="checkbox">
                                                <label class="without-checkbox"><u>Commercial</u>: Give Med R<sub>x</sub> to Pt.(EHR)</label>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="other_dme"
                                                        label="Other DME"
                                                        v-model="form_data.other_services.other_dme"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="patient_ht"
                                                        label="Patient HT"
                                                        v-model="form_data.other_services.patient_ht"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="patient_wt"
                                                        label="Patient WT"
                                                        v-model="form_data.other_services.patient_wt"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox">
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.mri"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >MRI*</label>
                                                <label><input type="checkbox"
                                                              v-model="form_data.other_services.mra"
                                                              @change="setHasValue"
                                                              :disabled="statuses.editingDisabled"
                                                >MRA*</label>
                                            </div>
                                            <div class="row">
                                                <document-textarea
                                                        name="specify_body_2"
                                                        label="Specify Body Part"
                                                        v-model="form_data.other_services.body_part_2.data"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                            <div class="checkbox-level checkbox">
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part_2.w_o"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >W/o contrast</label>
                                                </div>
                                                <div class="checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part_2.w_and_w_o"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >W/& W/o contrast</label>
                                                </div>
                                                <div class="checkbox-level checkbox">
                                                    <label><input type="checkbox"
                                                                  v-model="form_data.other_services.body_part_2.bun.status"
                                                                  @change="setHasValue"
                                                                  :disabled="statuses.editingDisabled"
                                                    >IV (BUN/Cr)</label>
                                                </div>
                                            </div>
                                            <div class="row" v-show="form_data.other_services.body_part_2.bun.status">
                                                <document-textarea
                                                        name="bun_cr_2"
                                                        label="Please specify"
                                                        v-model="form_data.other_services.body_part_2.bun.data"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br/>
                                <div class="row">
                                    <label>CLINICAL INDICATIONS</label>
                                    <p>
                                        Indicates DEA# is required Italicized services require questionnaire
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="form-group col-lg-12 input-container input-container-diagnosis">
                                        <label class="control-label diagnosis-label">Diagnosis and ICD code</label>
                                        <div class="fastselect-disabled" v-if="statuses.editingDisabled"></div>
                                        <input type="text"
                                               multiple
                                               id="diagnoseMultipleSelect"
                                               class="tagsInput"
                                               data-user-option-allowed="true"
                                        />
                                    </div>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="medical_need"
                                            label="Medical Need for Service Request"
                                            v-model="form_data.clinical_indications.medical_need"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="dea"
                                            label="*DEA#"
                                            v-model="form_data.clinical_indications.dea"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label"><i>Clinical Information to be Sent to Specialist</i></label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless clinical-information-table">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.clinical_information.lab"
                                                                >
                                                                Lab
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.clinical_information.x_ray"
                                                                >
                                                                X-Ray
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.clinical_information.other.status"
                                                                >
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
                                <div class="row" v-show="form_data.clinical_indications.clinical_information.other.status">
                                    <document-textarea
                                            name="clinical_info_other"
                                            label="Please specify"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-model="form_data.clinical_indications.clinical_information.other.data"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="speciality_provider"
                                            label="<i>Speciality Consulting Provider</i>"
                                            size="col-md-6"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-model="form_data.clinical_indications.clinical_information.speciality_consulting_provider"
                                    ></document-textarea>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label"><i>Appt. Date/Time</i></label>
                                        <el-date-picker
                                                v-model="form_data.clinical_indications.appt_date"
                                                type="datetime"
                                                name="appt_date"
                                                @focus="pickerFocus('appt_date')"
                                                @blur="pickerBlur('appt_date')"
                                                :editable="false"
                                                :format="dateTimePickerFormat"
                                                :value-format="dateTimePickerValueFormat"
                                                @change="resetDateError('appt_date')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label"><i>I Chose this Provider Because</i></label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless provider-table">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.chose_provider.preference"
                                                                >
                                                                MD Preference
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.chose_provider.discussed"
                                                                >
                                                                Discussed care w/SPC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.chose_provider.past"
                                                                >
                                                                Past appt. w/SPC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.clinical_indications.chose_provider.request"
                                                                >
                                                                Pt. Request
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-6 fix-row-1 current-status-container">
                                        <label class="control-label">Level of Service</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.level_of_service.in_patient"
                                                                >
                                                                In-patient
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.level_of_service.observation"
                                                                >
                                                                Observation
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.level_of_service.out_patient"
                                                                >
                                                                Outpatient
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-group-bordered col-md-6 fix-row-1 current-status-container">
                                        <label class="control-label">Place of Service</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.place_of_service.fmg"
                                                                >
                                                                FMG
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label">Hospital</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless hospital-table">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.hospital.phcmc"
                                                                >
                                                                PHCMC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.hospital.psimc"
                                                                >
                                                                PSJMC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.hospital.hmnmh"
                                                                >
                                                                HMNMH
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.hospital.svh"
                                                                >
                                                                SVH
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.hospital.other.status"
                                                                >
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
                                <div class="row" v-show="form_data.hospital.other.status">
                                    <document-textarea
                                            name="hospital_other"
                                            label="Please specify"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-model="form_data.hospital.other.data"
                                    ></document-textarea>
                                </div>


                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label">ASC</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless asc-table">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.asc.fec"
                                                                >
                                                                FEC ASC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.asc.sf"
                                                                >
                                                                SF ASC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.asc.summit"
                                                                >
                                                                Summit ASC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.asc.stone"
                                                                >
                                                                Stone Ctr / Encino ASC
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.asc.valley"
                                                                >
                                                                Valley Physicians S.C.
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
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                       v-model="form_data.asc.other.status"
                                                                >
                                                                Other ASC
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.asc.other.status">
                                    <document-textarea
                                            name="other_asc"
                                            label="Please specify"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-model="form_data.asc.other.data"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>AFFIX PATIENT DEMOGRAPHIC LABEL (or provide the following information)</label>
                                </div>
                                <div class="row">
                                    <document-input
                                            name="first_name"
                                            label="Firstname"
                                            size="col-md-4"
                                            v-model="form_data.first_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="last_name"
                                            label="Lastname"
                                            size="col-md-4"
                                            v-model="form_data.last_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="mrn"
                                            label="MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Patient DOB</label>
                                        <el-date-picker
                                                v-model="form_data.birth_date"
                                                type="date"
                                                name="birth_date"
                                                @focus="pickerFocus('birth_date')"
                                                @blur="pickerBlur('birth_date')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="resetDateError('birth_date')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <document-input
                                            name="provider_name"
                                            label="Requesting Provider"
                                            size="col-md-6"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                            @change="setHasValue"
                                    ></document-input>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="address"
                                            label="Address"
                                            size="col-md-6"
                                            v-model="form_data.address"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-input
                                            name="phone"
                                            label="Phone"
                                            size="col-md-4 col-xs-8"
                                            v-model="form_data.phone.number"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-mask="'###-###-####'"
                                            validateRules="regex:^([\d]{3}-){2}[\d]{4}$"
                                    ></document-input>
                                    <div class="form-group input-container col-md-2 col-xs-4 radio-without-label home-work">
                                        <div class="col-lg-12 checkbox">
                                            <label class="without-checkbox">
                                                <input type="radio"
                                                       name="phone_type"
                                                       value="home"
                                                       v-model="form_data.phone.type"
                                                       :disabled="statuses.editingDisabled"
                                                       @change="setHasValue"
                                                >
                                                Home
                                            </label>
                                            <label class="without-checkbox">
                                                <input type="radio"
                                                       name="phone_type"
                                                       value="work"
                                                       v-model="form_data.phone.type"
                                                       :disabled="statuses.editingDisabled"
                                                       @change="setHasValue"
                                                >
                                                Work
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="gender"
                                            label="Gender"
                                            size="col-md-6"
                                            v-model="form_data.gender"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="fsc_ins"
                                            label="FSC/INC"
                                            size="col-md-6"
                                            v-model="form_data.fsc_ins"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <div class="form-group input-container col-md-6 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label class="patient-appointment">
                                                <input type="checkbox"
                                                       name="patient_to_call"
                                                       v-model="form_data.patient_to_call"
                                                       :disabled="statuses.editingDisabled"
                                                       @change="setHasValue"
                                                >
                                                Patient to Call for Appointment
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Date Provider/Pt. Notified</label>
                                        <el-date-picker
                                                v-model="form_data.date_notified"
                                                type="date"
                                                name="date_notified"
                                                @focus="pickerFocus('date_notified')"
                                                @blur="pickerBlur('date_notified')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="resetDateError('date_notified')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="comments"
                                            label="Comments"
                                            v-model="form_data.comments"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="reference"
                                            label="Reference #"
                                            v-model="form_data.reference"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>





                                <div class="form-note-button-block">
                                    <div class="row">
                                        <div class="col-lg-12 text-center" style="padding-right:0;margin-bottom:15px;">
                                            <span class="text-red validation-error-msg" v-if="statuses.noErrors === false && !validation_message">
                                                Please make sure you have filled all the required fields.
                                            </span>
                                            <span class="text-red validation-error-msg" v-if="validation_message">
                                                    {{validation_message}}
                                            </span>
                                        </div>

                                        <div class="col-lg-12 text-right" style="padding-right:0;">
                                            <div class="col-lg-12" style="padding-right:0;">
                                                <button type="submit" class="btn btn-primary document-button"
                                                        @click.prevent="saveDocument"
                                                        v-if="!statuses.editingDisabled"
                                                >
                                                    Save
                                                </button>

                                                <button type="button" class="btn btn-default document-button"
                                                        @click.prevent="closeDocument"
                                                >
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
    </div>
</template>

<script>
    import validate from './../../../mixins/validate';
    import save from './../../../mixins/save-document-if-valid';
    import methods from './../../../mixins/document-methods';
    import diagnosisCode from './../../../mixins/diagnosis-and-icd-code';

    export default {
        mixins: [validate, save, methods, diagnosisCode],
        data(){
            return{
                document_name: 'cwr-initial-assessment',
                document_title: 'CWR Initial Assessment',
            }
        },
        beforeMount(){
            if(!this.form_data && this.$store.state.currentDocument){
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {

                    diagnosis_icd_code: null,
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    mrn: null,
                    birth_date: this.$store.state.currentPatient.date_of_birth,
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    provider_name: this.currentProvider.provider_name,
                    address: null,
                    phone: {
                        number: null,
                        type: null,
                    },
                    gender: null,
                    fsc_ins: null,
                    patient_to_call: null,
                    date_notified: null,
                    comments: null,
                    reference: null,
                    service_requested: {
                        consultation: null,
                        follow_up: null,
                        second_opinion: null,
                        multiple_services: {
                            status: null,
                            data: null,
                        },
                        allergy: null,
                        cardiology: null,
                        dermatology: null,
                        ent: null,
                        endocrinology: null,
                        gi: null,
                        colon_soreening: null,
                        general_surgery: null,
                        gyn: null,
                        hem: null,
                        infectious: null,
                        nerphology: null,
                        neurology: null,
                        neurosurgery: null,
                        ophtalmology: null,
                        orthopedics: null,
                        ortho: null,
                        hand_ortho: null,
                        pain_management: null,
                        high_risk: null,
                        plastic: null,
                        podiatry: null,
                        pulmonology: null,
                        rad: null,
                        rheumatology: null,
                        urology: null,
                        vascular: null,
                        other: {
                            status: null,
                            data  : null,
                        }
                    },
                    service_status: {
                        routine: null,
                        urgent: null,
                        stat: null,
                        retro: null,
                        pr: null,
                        ccmg: null,
                    },
                    other_services:{
                        ankle: null,
                        auditory: {
                            status: null,
                            w_t: null,
                            w_o: null,
                        },
                        bone_dencity: null,
                        bone_scan: null,
                        breast: null,
                        chemotherapy: null,
                        colonoscopy: {
                            screening: null,
                            diagnostic: null,
                        },
                        duplex: {
                            arterial: null,
                            venous: null,
                            carotid: null,
                            specify: null,
                        },
                        eeg: null,
                        egd: null,
                        echo: null,
                        home: null,
                        hospice: null,
                        hysteroscopy: null,
                        injectable: null,
                        ncv: null,
                        emo: null,
                        specify: null,
                        self_injectable:{
                            status: null,
                            education: null,
                        },
                        sleep_study: {
                            titration: null,
                            split_night: null,
                            home: null,
                        },
                        stress_tests: {
                            adenosine: null,
                            dobutamine: null,
                            cardiolyte: null,
                            echo: null,
                        },
                        surgery: {
                            status: null,
                            data: null,
                        },
                        therapy: {
                            pt: null,
                            ot: null,
                            speech: null,
                        },
                        other_services: {
                            status: null,
                            data: null,
                        },
                        cpt_codes: null,
                        ct_sinus: null,
                        ct_stone: null,
                        ct: null,
                        cta: null,
                        body_part: {
                            data: null,
                            w_o: null,
                            w_contrast: null,
                            metlomin: null,
                            bun: {
                                status: null,
                                data: null,
                            },
                        },
                        dme: null,
                        o_liters: null,
                        o_sat: null,
                        abg: null,
                        pulse_ox: null,
                        cpap: null,
                        nebulizer: null,
                        other_dme: null,
                        patient_ht: null,
                        patient_wt: null,
                        mri: null,
                        mra: null,
                        body_part_2: {
                            data: null,
                            w_o: null,
                            w_and_w_o: null,
                            bun: {
                                status: null,
                                data: null,
                            },
                        },
                    },
                    clinical_indications: {
                        medical_need: null,
                        dea: null,
                        clinical_information: {
                            lab: null,
                            x_ray: null,
                            other: {
                                status: null,
                                data: null,
                            }
                        },
                        speciality_consulting_provider: null,
                        appt_date: null,
                        chose_provider: {
                            preference: null,
                            discussed: null,
                            past: null,
                            request: null,
                        }
                    },
                    level_of_service: {
                        in_patient: null,
                        observation: null,
                        out_patient: null,
                    },
                    place_of_service: {
                        fmg: null,
                    },
                    hospital: {
                        phcmc: null,
                        psimc: null,
                        hmnmh: null,
                        svh: null,
                        other: {
                            status: null,
                            data: null,
                        }
                    },
                    asc: {
                        fec: null,
                        sf: null,
                        summit: null,
                        stone: null,
                        valley: null,
                        other: {
                            status: null,
                            data: null,
                        }
                    }

                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'facey-rfr';
            let document_name = self.getFormName(menu_item_selector);
            self.document_name = document_name;
            self.document_title = self.getFormTitle(menu_item_selector);

            window.setTimeout(() => {
                $('#'+document_name).on('shown.bs.modal', function() {
                    $('body').addClass('custom-modal');

                    autosize($('#'+document_name).find('textarea'));

                    $('.input-container').on('click', function(){

                        $(this).find('.input-element').focus();
                    });

                    $('#'+document_name).find('input.el-input__inner').addClass('input-element');

                    self.initDiagnosisAndIcdCode();

                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
        methods: {
            getCustomValidation(){

                let error = false;

                this.setDiagnosisIcdCode();

                return error;
            },
            getValidationMessage(){
                for (let child in this.$children) {

                    if(this.$children[child].name == 'phone'){

                        if(this.$children[child].errors.has('phone')){

                            if(this.$children[child].errors.items[0].rule == 'regex'){

                                this.validation_message = 'Invalid phone format. (Example: 111-111-1111)';
                                break;
                            }
                        }
                    }
                }
            }
        },
    }
</script>
