<template>
    <div id="page-content-wrapper">
        <div id="page-content" class="content-with-footer" :class="{'wo-pb': !tabs.chart.active}">
            <doctors-availability-alert/>
            <div v-if="patient !== null" class="new-profile-wrapper">
                <div class="section section-new-profile">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-xs-8 col-md-9">
                                    <h3 class="patient-name">{{fullName(patient)}},
                                        <span class="patient-age">{{ yearsOld(patient) }}</span>
                                        <span class="patient-age" v-if="patient.date_of_birth">,
                                            {{ getFormattedDate(patient.date_of_birth)}}
                                        </span>
                                        <i class="fa fa-refresh patient-age sync-patient" title="Sync with OfficeAlly"
                                           @click.prevent="showConfirmSyncPatient()"
                                           v-if="patient.patient_id != 11111111 && !patient.start_synchronization_time && !is_audit_mode"></i>
                                        <pageloader add-classes="patient-sync" v-if="patient.start_synchronization_time"
                                                    title="Synchronization"/>
                                        <send-removal-request :showButton="showRemovalRequestButton"
                                                              @patientChangedEvent="emitPatientChanged"
                                                              v-if="!isUserAdmin && !is_read_only_mode && !is_audit_mode"></send-removal-request>
                                        <provider-cancel-removal-request :showButton="showCancelRemovalRequestButton"
                                                                         @patientChangedEvent="emitPatientChanged"
                                                                         v-if="!isUserAdmin && !is_read_only_mode && !is_audit_mode"></provider-cancel-removal-request>
                                        <patient-tags :tags="patient.tags"/>
                                    </h3>
                                </div>
                                <div class="col-xs-4 col-md-3 text-right">
                                    <div class="patient-status">
                                        <span v-if="patient.status" :style="{color: '#' + patient.status.hex_color}">
                                            {{ patient.status.status }} 
                                        </span>
                                        <el-tooltip v-if="patient.status_patient_info && patient.status_patient_info.date" class="item" effect="dark" placement="bottom">
                                            <template #content>
                                                <div v-if="patient.status_patient_info.provider">
                                                    Provider: {{ patient.status_patient_info.provider }}
                                                </div>
                                                <div>Date: {{ patient.status_patient_info.date }}</div> 
                                            </template>
                                            <help />
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-flexbox">
                            <div class="two-col">
                                <div class="user-info-container">
                                    <div class="profile-row">
                                        <div class="profile-title">
                                            ID:
                                        </div>
                                        <div class="profile-value">
                                            {{patient.patient_id}}
                                        </div>
                                    </div>

                                    <div class="profile-row">
                                        <div class="profile-title">
                                            Assigned to:
                                        </div>
                                        <div class="profile-value">
                                            <ul class="patient-assigned-to-list" v-if="patient.providers.length === 1">
                                                <li v-for="(t, index) in patient.providers">
                                                    {{t.provider_name.trim()}}
                                                    <a v-if="isUserAdmin" href="/dashboard/patients-management" target="_blank">
                                                        <i class="fa fa-times fa-relationship-button"></i>
                                                    </a>
                                                    <i class="fa fa-plus fa-relationship-button" v-if="isUserAdmin"
                                                       @click.prevent="showAddPatientProviderRelationship()"></i>
                                                </li>
                                            </ul>
                                            <a
                                                v-else-if="patient.providers.length > 1"
                                                style="cursor: pointer;"
                                                @click.prevent="showPatientTherapistList"
                                            >
                                                View
                                            </a>
                                            <span v-else>
                                                <i class="fa fa-plus fa-relationship-button" v-if="isUserAdmin"
                                                   @click.prevent="showAddPatientProviderRelationship()"></i>
                                                <span v-else>-</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="profile-row" style="align-items: center" v-if="patient.primary_insurance">
                                        <div class="profile-title">
                                            Insurance:
                                        </div>
                                        <div class="profile-value" v-html="getPatientInsurance(patient)"></div>
                                    </div>
                                    <div class="profile-row" style="align-items: center" v-if="patient.insurance_plan && patient.insurance_plan.name">
                                        <div class="profile-title text-right">
                                            Insurance plan:
                                        </div>
                                        <div class="profile-value">
                                            {{ patient.insurance_plan.name }}
                                        </div>
                                    </div>

                                    <div class="profile-row" style="align-items: center" v-if="showAuthorizationNumber">
                                        <div class="profile-title text-right">
                                            Authorization number:
                                        </div>
                                        <insurance-authorization-number :patient="insuranceAuthorizationNumberData" />
                                    </div>

                                    <div class="profile-row" style="align-items: center">
                                        <div class="profile-title text-right">
                                            Frequency of Treatment:
                                        </div>
                                        <div class="profile-value">
                                            <span v-if="patient.visit_frequency">
                                                {{ patient.visit_frequency.name }}
                                            </span>
                                            <span v-else>
                                                -
                                            </span>
                                            <visit-frequency-changes-tooltip 
                                                v-if="patient.visit_frequency_changes && patient.visit_frequency_changes.length"
                                                :visit-frequency-changes="patient.visit_frequency_changes"
                                            />
                                            &nbsp;
                                            <i
                                                v-if="(isUserAdmin && !isUserSecretary && !isUserPatientRelationManager) || !patient.visit_frequency"
                                                title="Update frequency of treatment"
                                                style="margin-top:2px;z-index:10;"
                                                class="fa fa-pencil fa-relationship-button"
                                                onclick="$('#modal-change-visits-frequency').modal('show')"
                                            ></i>
                                        </div>
                                    </div>

                                    <div class="profile-row" style="align-items: center">
                                        <div class="profile-title text-right">
                                            Therapy Type:
                                        </div>
                                        <div class="profile-value">
                                            <span v-if="patient.therapy_type">
                                                {{ patient.therapy_type.name }}
                                            </span>
                                            <span v-else>-</span>
                                        </div>
                                    </div>

                                    <div v-if="patient.preferred_language" class="profile-row">
                                        <patients-language
                                            v-if="!is_audit_mode"
                                            :title = "patient.preferred_language.title"
                                            :languagesList="patientLanguageList"
                                            :show_edit_button="!!(patient && ((isUserAdmin !== null && isUserAdmin) || !is_read_only_mode))"
                                            :patient_id="patient.id">   
                                        </patients-language>  
                                    </div>
                                    <div  v-else class="profile-row">
                                        <patients-language-unknown
                                            v-if="!is_audit_mode"
                                            :languagesList="patientLanguageList"
                                            :show_edit_button="!!(patient && ((isUserAdmin !== null && isUserAdmin) || !is_read_only_mode))"
                                            :patient_id="patient.id">   
                                        </patients-language-unknown>  
                                    </div>
                                    <div class="profile-row">
                                        <div class="profile-title">
                                            Sex:
                                        </div>
                                        <div class="profile-value">
                                            <template v-if="patient.sex === 'M'">Male</template>
                                            <template v-else-if="patient.sex === 'F'">Female</template>
                                            <template v-else>Unknown</template>
                                        </div>
                                    </div>
                                    <patient-phone
                                            v-if="!is_audit_mode"
                                            title="Cell Phone"
                                            :phone="patient.cell_phone"
                                            :phone_label="patient.cell_phone_label"
                                            :additional_phones="getAdditionalPhones('cell_phone')"
                                            :show_edit_button="!!(patient && ((isUserAdmin !== null && isUserAdmin) || !is_read_only_mode))"
                                            :patient_id="patient.id"
                                            field_name="cell_phone"
                                    ></patient-phone>

                                    <patient-phone
                                            v-if="!is_audit_mode"
                                            title="Home Phone"
                                            :phone="patient.home_phone"
                                            :phone_label="patient.home_phone_label"
                                            :additional_phones="getAdditionalPhones('home_phone')"
                                            :show_edit_button="!!(patient && ((isUserAdmin !== null && isUserAdmin) || !is_read_only_mode))"
                                            :patient_id="patient.id"
                                            field_name="home_phone"
                                    ></patient-phone>

                                    <patient-phone
                                            v-if="!is_audit_mode"
                                            title="Work Phone"
                                            :phone="patient.work_phone"
                                            :phone_label="patient.work_phone_label"
                                            :additional_phones="getAdditionalPhones('work_phone')"
                                            :show_edit_button="!!(patient && ((isUserAdmin !== null && isUserAdmin) || !is_read_only_mode))"
                                            :patient_id="patient.id"
                                            field_name="work_phone"
                                    ></patient-phone>

                                    <div class="profile-row" v-if="!is_audit_mode">
                                        <div class="profile-title">
                                            Email:
                                        </div>
                                        <div class="profile-value">
                                            <a
                                                v-if="patient.email" 
                                                :href="`${'mailto:' + patient.email}`" 
                                                :class="{'text-red': patient.email_rejection_info && patient.email_rejection_info.email_rejected}"
                                            >
                                                {{ patient.email }}
                                            </a>
                                            <span v-else>-</span>
                                            &nbsp;
                                            <template v-if="patient">
                                                <i v-if="(isUserAdmin || !is_read_only_mode)"
                                                    title="Update email"
                                                    style="margin-top:2px;z-index:10;"
                                                    class="fa fa-pencil fa-relationship-button"
                                                    onclick="$('#modal-change-email').modal('show')"
                                                ></i>
                                                <i v-if="isUserAdmin && patient.email && patient.email_rejection_info && patient.email_rejection_info.email_can_be_restored"
                                                    title="Remove email from blacklist"
                                                    style="margin-left:10px;margin-top:3px;z-index:10;"
                                                    class="fa fa-repeat fa-relationship-button"
                                                    @click="openEmailUnsubscribedDialog(patient.email)"
                                                ></i>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="profile-row" v-if="!is_audit_mode">
                                        <div class="profile-title">
                                            Email Add:
                                        </div>
                                        <div class="profile-value">
                                            <a
                                                v-if="patient.secondary_email"
                                                :href="`${'mailto:' + patient.secondary_email}`"
                                                :class="{'text-red': patient.secondary_email_rejection_info && patient.secondary_email_rejection_info.email_rejected}"
                                            >
                                                {{ patient.secondary_email }}
                                            </a>
                                            <span v-else>-</span>
                                            &nbsp;
                                            <template v-if="patient">
                                                <i v-if="(isUserAdmin || !is_read_only_mode)"
                                                    title="Update email"
                                                    style="margin-top:2px;z-index:10;"
                                                    class="fa fa-pencil fa-relationship-button"
                                                    onclick="$('#modal-change-email').modal('show')"
                                                ></i>
                                                <i v-if="isUserAdmin && patient.secondary_email && patient.secondary_email_rejection_info && patient.secondary_email_rejection_info.email_can_be_restored"
                                                    title="Remove email from blacklist"
                                                    style="margin-left:10px;margin-top:3px;z-index:10;"
                                                    class="fa fa-repeat fa-relationship-button"
                                                    @click="openEmailUnsubscribedDialog(patient.secondary_email)"
                                                ></i>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="profile-row" style="align-items: center;" v-if="!is_audit_mode && isUserAdmin">
                                        <div class="profile-title text-right">
                                            Patient agreements:
                                        </div>
                                        <div class="profile-value">
                                            <a
                                                style="cursor: pointer;"
                                                @click.prevent="showConsentInfo"
                                            >
                                                View
                                            </a>
                                        </div>
                                    </div>

                                    <div class="profile-row" v-if="!is_audit_mode">
                                        <div class="profile-title">
                                            Address:
                                        </div>
                                        <div class="profile-value">
                                            {{patientAddress}}
                                        </div>
                                    </div>

                                    <div class="profile-row" v-if="!is_audit_mode">
                                        <div class="profile-title">
                                            Credit card{{ creditCards.length > 1 ? 's' : '' }}:
                                        </div>
                                        <div v-if="creditCards.length === 0" class="profile-value">
                                            -
                                        </div>
                                        <div v-else-if="creditCards.length === 1" class="profile-value">
                                            <span :class="{'text-red': isCardExpired(creditCards[0])}">{{ getFormattedCardDetails(creditCards[0]) }}</span>
                                        </div>
                                        <div v-else class="profile-value">
                                            <a style="cursor: pointer;" @click.prevent="showPatientCreditCardList">
                                                View
                                            </a>
                                        </div>
                                    </div>

                                    <div class="profile-row" v-if="patient.subscriber_id">
                                        <div class="profile-title">
                                            Subscriber ID:
                                        </div>
                                        <div class="profile-value">
                                            {{ patient.subscriber_id }}
                                        </div>
                                    </div>

                                    <div class="profile-row" style="align-items: center" v-if="!is_audit_mode">
                                        <div class="profile-title text-right">
                                            Pays out-of-pocket:
                                        </div>
                                        <div class="profile-value">
                                            {{ patient.is_self_pay ? "Yes" : "No" }}
                                        </div>
                                    </div>

                                    <template v-if="isUserAdmin">
                                        <template v-if="patient.is_self_pay">
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Self-Pay:
                                                </div>
                                                <div class="profile-value">
                                                    ${{ patient.self_pay }}
                                                </div>
                                            </div>
                                        </template>

                                        <template v-else>
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Co-Pay:
                                                </div>
                                                <div class="profile-value">
                                                    {{ formatted_patient_copay }}
                                                </div>
                                            </div>
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Deductible:
                                                </div>
                                                <div class="profile-value">
                                                    ${{ patient.deductible }}
                                                </div>
                                            </div>
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Deductible Met:
                                                </div>
                                                <div class="profile-value">
                                                    ${{ patient.deductible_met }}
                                                </div>
                                            </div>
                                            <div class="profile-row" style="align-items: center" v-if="!is_audit_mode">
                                                <div class="profile-title text-right">
                                                    Remaining Deductible:
                                                </div>
                                                <div class="profile-value">
                                                    ${{ patient.deductible_remaining }}
                                                </div>
                                            </div>
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Insurance Pay:
                                                </div>
                                                <div class="profile-value">
                                                    ${{ patient.insurance_pay }}
                                                </div>
                                            </div>
                                        </template>
                                    </template>

                                    <template v-else>
                                        <template v-if="patient.is_self_pay">
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Self-Pay:
                                                </div>
                                                <div class="profile-value">
                                                    ${{ patient.self_pay }}
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="profile-row" v-if="!is_audit_mode">
                                                <div class="profile-title">
                                                    Co-Pay:
                                                </div>
                                                <div class="profile-value">
                                                    {{ formatted_patient_copay }}
                                                </div>
                                            </div>
                                            <div class="profile-row" style="align-items: center" v-if="!is_audit_mode">
                                                <div class="profile-title text-right">
                                                    Has Deductible:
                                                </div>
                                                <div class="profile-value">
                                                    {{ patient.deductible_remaining > 0 ? "Yes" : "No" }}
                                                </div>
                                            </div>
                                        </template>
                                    </template>

                                    <div class="profile-row" style="align-items: center" v-if="!is_audit_mode">
                                        <div class="profile-title text-right">
                                            Charge for cancellation:
                                        </div>
                                        <div class="profile-value d-flex align-items-center gap-1">
                                            <span :class="{'text-red cursor-pointer': patient_charge_for_cancellation && !canChargeLateCancellationFee}">
                                                {{ formatted_patient_charge_for_cancellation }}
                                            </span>
                                            <el-tooltip v-if="patient_charge_for_cancellation && !canChargeLateCancellationFee" class="item" effect="dark" placement="bottom">
                                                <template #content>
                                                    <CancellationFeeRestrictions :patientLateCancellationFeeInfo="patient.cancelation_fee_info" :onlyReasons="true" />
                                                </template>
                                                <help />
                                            </el-tooltip>
                                        </div>
                                    </div>

                                    <template v-if="!is_audit_mode">
                                        <div class="profile-row" v-if="isUserAdmin || isCollectPaymentAvailable">
                                            <div class="profile-title">
                                                Balance:
                                            </div>
                                            <div class="profile-value">
                                                <span :class="{'text-red': getPatientPreprocessedBalance(patient) < 0}">
                                                    {{ getFormattedMoney(getPatientPreprocessedBalance(patient), false) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="profile-row" v-else-if="getPatientPreprocessedBalance(patient) < 0">
                                            <div class="profile-title">
                                                Balance:
                                            </div>
                                            <div class="profile-value">
                                                <span :class="'text-red'">
                                                    Negative
                                                </span>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="profile-row" v-if="!is_audit_mode">
                                        <div class="profile-title">
                                            Payment:
                                        </div>
                                        <div class="profile-value">
                                            {{ formatted_payment_forbidden }}
                                        </div>
                                    </div>
                                </div>
                                <div class="center-col" v-show="!is_audit_mode">
                                    <div class="profile-row">
                                        <div class="profile-title">
                                            Progress Notes:
                                        </div>
                                        <div class="profile-value">
                                            {{ patientNoteCount }}
                                            <!-- <span v-if="patientPaperNoteCount > 0">
                                                &nbsp;(+{{patientPaperNoteCount}} on paper)
                                            </span> -->
                                        </div>
                                    </div>
                                    <div class="profile-row no-wrap" v-if="isUserAdmin">
                                        <div class="profile-title">
                                            Draft Progress Notes:
                                        </div>
                                        <div class="profile-value">
                                            {{ patientDraftNoteCount }}
                                        </div>
                                    </div>
                                    <div class="profile-row no-wrap" v-if="isUserAdmin">
                                        <div class="profile-title">
                                            Missing Progress Notes:
                                        </div>
                                        <div class="profile-value">
                                            {{ patientMissingNoteCount }}
                                        </div>
                                    </div>
                                    <div class="profile-row" v-if="isUserAdmin">
                                        <div class="profile-title">
                                            Initial Assessments:
                                        </div>
                                        <div class="profile-value">
                                            {{ patientInitialAssessmentCount }}
                                        </div>
                                    </div>
                                    <div class="profile-row">
                                        <div class="profile-title">
                                            Completed Sessions:
                                        </div>
                                        <div class="profile-value">
                                            {{ patientAppointmentVisitCreatedCount }}
                                        </div>
                                    </div>
                                    <div class="profile-row" v-if="isUserAdmin">
                                        <div class="profile-title no-wrap">
                                            Cancelled Appointments:
                                        </div>
                                        <div class="profile-value d-flex align-items-center gap-1">
                                            <span>{{ patientCancelledAppointmentsCount }} ({{ percentageOfCanceledAppointments }}%)</span>
                                            <el-tooltip v-if="patientCancelledAppointmentsCount" class="item" effect="dark" placement="bottom">
                                                <template #content>
                                                    <div v-html="cancelledAppointmentTooltipText"></div>
                                                </template>
                                                <help />
                                            </el-tooltip>
                                        </div>
                                    </div>
                                    <div class="profile-row" v-if="isUserAdmin">
                                        <div class="profile-title no-wrap">
                                            Logged Sessions (Google meet and Phone):
                                        </div>
                                        <div class="profile-value d-flex align-items-center gap-1">
                                            <span>
                                                {{ patientGoogleMeetingAppointmentCount + patientRingCentralAppointmentCount }} ({{ percentageOfGoogleMeetingAndRingCentralAppointments }}%)
                                            </span>
                                            <el-tooltip v-if="completedAndVisitCreatedAppointmentCount" class="item" effect="dark" placement="bottom">
                                                <template #content>
                                                    <div>Google meet sessions: {{ patientGoogleMeetingAppointmentCount }}</div>
                                                    <div>Phone sessions: {{ patientRingCentralAppointmentCount }}</div>
                                                </template>
                                                <help />
                                            </el-tooltip>
                                        </div>
                                    </div>
                                    <div class="profile-row" v-if="isUserAdmin">
                                        <div class="profile-title">
                                            Average Session Length:
                                        </div>
                                        <div class="profile-value">
                                            {{ Math.round(patientVisitAverageDuration) }} min.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="buttons-container">
                                <a v-if="isShowMoveToArchive"
                                   @click.prevent="showMoveToArchiveConfirmation"
                                   class="btn view-in-officeally-btn pull-right btn-full-width btn-danger patient-info-block-btn"
                                   role="button"
                                   style="margin-bottom:5px;"
                                >
                                    Move to Archive
                                </a>
                                <a v-if="isUserAdmin && !isUserPatientRelationManager && patient.patient_id != 11111111"
                                   class="btn btn-warning view-in-officeally-btn pull-right patient-info-block-btn"
                                   role="button"
                                   :href="`${office_ally_href}${patient.patient_id}`" target="_blank"
                                >
                                    View in OfficeAlly
                                </a>
                                <a v-if="patient.upheal_user_id"
                                   class="btn btn-warning view-in-officeally-btn view-in-upheal-btn pull-right patient-info-block-btn"
                                   role="button"
                                   :href="`${upheal_href}${patient.upheal_user_id}`" target="_blank"
                                >
                                    View in Upheal
                                </a>
                                <collect-payment-button
                                    v-if="patient && !isUserPatientRelationManager && (isUserAdmin || isCollectPaymentAvailable)"
                                    :patient="patient"
                                ></collect-payment-button>
                                <div class="form-buttons">
                                    <button
                                        v-if="isUserAdmin && !isUserPatientRelationManager"
                                        class="btn btn-warning add-note-btn pull-right patient-info-block-btn"
                                        @click="openPatientDialog"
                                    >
                                        Edit Patient
                                    </button>

                                    <add-form-dropdown
                                        v-if="isUserAdmin !== null && !isUserAdmin && !isUserPatientRelationManager && !is_read_only_mode && !is_audit_mode"
                                        :form-templates="assessmentFormsTemplates"
                                        @show-document="showDocument"
                                    />

                                    <button
                                        v-if="isUserAdmin !== null && !isUserAdmin && !isUserPatientRelationManager && !is_read_only_mode && !is_audit_mode"
                                        class="btn btn-warning add-note-btn pull-right patient-info-block-btn"
                                        :disabled="statuses.loading_note_blank"
                                        @click="checkForDraftPN()"
                                    >
                                        Add Progress Note
                                    </button>
                                    <button
                                        v-if="isUserAdmin !== null && !isUserAdmin && !isUserPatientRelationManager && !is_read_only_mode && !is_audit_mode"
                                        class="btn btn-warning add-note-btn pull-right patient-info-block-btn"
                                        :disabled="statuses.starting_video_session"
                                        @click="openGoogleMeetDialog"
                                    >
                                        Video Session
                                    </button>
                                    <button
                                        class="btn btn-warning add-note-btn pull-right patient-info-block-btn"
                                        @click="openCallDialog"
                                    >
                                        Call Patient
                                    </button>
                                </div>
                                <button
                                    v-if="isUserAdmin && !isUserPatientRelationManager"
                                    class="btn btn-primary btn-download-docs pull-right patient-info-block-btn"
                                    @click="openDocumentDownloadDialog"
                                >
                                    Download Docs
                               </button>
                            </div>
                        </div>

                        <!--                        <div class="alert alert-danger alert-dismissible alert-reversed alert-declined-documents" v-if="patient.missing_forms && patient.missing_forms.length > 0">-->
                        <!--                            <div class="close" aria-label="Warning">-->
                        <!--                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">-->
                        <!--                                    <path d="M7.06073 6.00012L11.5693 1.49128C11.8628 1.19799 11.8628 0.723798 11.5693 0.430517C11.2761 0.137236 10.8019 0.137236 10.5086 0.430517L5.99985 4.93936L1.49124 0.430517C1.19783 0.137236 0.723784 0.137236 0.430509 0.430517C0.137096 0.723798 0.137096 1.19799 0.430509 1.49128L4.93912 6.00012L0.430509 10.509C0.137096 10.8022 0.137096 11.2764 0.430509 11.5697C0.576666 11.716 0.76884 11.7895 0.960877 11.7895C1.15291 11.7895 1.34495 11.716 1.49124 11.5697L5.99985 7.06087L10.5086 11.5697C10.6549 11.716 10.8469 11.7895 11.039 11.7895C11.231 11.7895 11.423 11.716 11.5693 11.5697C11.8628 11.2764 11.8628 10.8022 11.5693 10.509L7.06073 6.00012Z" fill="white"/>-->
                        <!--                                </svg>-->

                        <!--                            </div>-->
                        <!--                            <el-collapse class="collapse-declined">-->
                        <!--                                <el-collapse-item>-->
                        <!--                                    <template v-slot:title>-->
                        <!--                                        <span>-->
                        <!--                                          ATTENTION! Missing Required Documents-->
                        <!--                                        </span>-->
                        <!--                                    </template>-->
                        <!--                                    <ul class="list-declined">-->
                        <!--                                        <li v-for="message in patient.missing_forms" class="list-declined__item">-->
                        <!--                                            {{ message }}-->
                        <!--                                        </li>-->
                        <!--                                    </ul>-->
                        <!--                                </el-collapse-item>-->
                        <!--                            </el-collapse>-->
                        <!--                        </div>-->

                    </div><!--/.row-->
                </div><!--/.section-new-profile-->
                <div class="chart-tabs">
                    <div class="chart-tab" :class="{active: tabs.timeline.active}" @click="changeTab('timeline')">
                        <div class="chart-tab-title">
                            Chart
                        </div>
                    </div>

                    <div class="chart-tab" :class="{active: tabs.patientForms.active}" v-show="!is_audit_mode"
                         @click="changeTab('patientForms')">
                        <div class="chart-tab-title">
                            Patient Forms ({{tabs.patient_forms_count.count}})
                        </div>
                    </div>
                    <div class="chart-tab" :class="{active: tabs.appointments.active}" v-show="!is_audit_mode"
                         @click="changeTab('appointments')">
                        <div class="chart-tab-title">
                            Appt. ({{tabs.appointments.count}})
                        </div>
                    </div>
                    <div class="chart-tab" :class="{active: tabs.visits.active}" @click="changeTab('visits')" v-show="!is_audit_mode">
                        <div class="chart-tab-title">
                            Visits ({{tabs.visits.count}})
                        </div>
                    </div>
                    <div class="chart-tab" :class="{active: tabs.transactions.active}" v-show="!is_audit_mode && (isUserAdmin || isCollectPaymentAvailable)"
                         @click="changeTab('transactions')">
                        <div class="chart-tab-title">
                            Payments ({{tabs.patient_card_transactions.count}})
                        </div>
                    </div>
                    <div class="chart-tab" :class="{active: tabs.notifications.active}" v-show="!is_audit_mode && isUserAdmin"
                         @click="changeTab('notifications')">
                        <div class="chart-tab-title">
                           Alerts ({{notifications_count}}) 
                        </div>
                    </div>
                </div>

            </div><!--/.new-profile-wrapper-->
            <template v-else-if="!this.$route.params.id">
                <dashboard/>
            </template>
            <clock :with-wrapper="false" v-else/>


            <div class="note-loader-container" v-if="(isNoteLoading && tabs.timeline.active) ||
                (statuses.appointments_loading && tabs.appointments.active) ||
                (statuses.notifications_loading && tabs.notifications.active) ||
                (statuses.transactions_loading && tabs.transactions.active) ||
                (statuses.patient_forms_loading && tabs.patientForms.active) ||
                (statuses.visits_loading && tabs.visits.active)">
                <pageloader add-classes="note-loader"></pageloader>
            </div>

            <div v-else-if="tabs.timeline.active">
                <chart-timeline
                    :scroll-to="scrollTo"
                    @loadChart="loadMoreChart"
                    @deleteCommentConfirmation="deleteCommentConfirmation($event)"
                />
            </div>


            <appointments
                v-if="tabs.appointments.active && !statuses.appointments_loading"
                @telehealth-confirmed="telehealthConfirmed"
                @updateAppointments="loadAppointments"
            />
            <visits
                v-if="tabs.visits.active && !statuses.visits_loading"
                @showPn="viewNote($event)"
                @addPn="openNoteBlank($event)"
                @addInitialAssessment="openInitialAssessmentBlank($event)"
                @reloadTabs="reloadTabs(['visits'])"
                @show-document="showDocument"
            />
            <notifications v-if="tabs.notifications.active && !statuses.notifications_loading"/>
            <patient-forms-tab
                v-if="tabs.patientForms.active && !statuses.patient_forms_loading"
                :key="`patient-form-for-${patient ? patient.id : Date.now()}`"
                :patient="patient"
            />
            <patient-transactions v-if="tabs.transactions.active && !loading_transactions_tab"/>

            <div id="fine-upload-container" v-show="tabs.timeline.active && !isNoteLoading && !is_audit_mode">
                <ul class="nav nav-pills nav-stacked col-xs-2">
                    <li class="active" v-if="!is_read_only_mode">
                        <a data-toggle="tab" href="#file">File</a>
                    </li>
                    <li :class="{active: is_read_only_mode}">
                        <a data-toggle="tab" href="#comment" @click="initTributePlugin()">Comment</a>
                    </li>
                </ul>

                <div class="tab-content col-xs-10">
                    <div id="file" class="tab-pane fade in active" v-if="!is_read_only_mode">
                        <div id="fine-upload"></div>
                    </div>
                    <div id="comment" class="tab-pane fade" :class="{'in active': is_read_only_mode}">
                        <div id="comment-container">
                            <div class="input-group">
                                <div id="comment-textarea" class="form-control comment-textarea" contenteditable></div>
                                <span class="input-group-addon" id="save-comment">
                                    <button
                                        type="button"
                                        class="btn btn-primary pull-right"
                                        @click="storeComment"
                                    >
                                        Submit
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Modals-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="specify-file-type-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                          <span>File Type</span>
                          <button
                              type="button"
                              class="close"
                              data-dismiss="modal"
                              aria-label="Close"
                              @click.prevent="deleteDocument"
                          >
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <select id="document_type_id" class="form-control" v-model="document_type_id"
                                    v-html="document_types_html"></select>
                        </div>
                        <div class="form-group" v-if="isUserAdmin">
                            <label>
                                <input type="checkbox" v-model="visible_only_for_admin">
                                Visible Only For Administrator
                            </label>
                        </div>
                        <!--<div id="document_other_type" class="form-group" v-if="is_other_document_type">-->
                        <!--<label>Document Type</label>-->
                        <!--<input class="form-control" v-model="other_document_type">-->
                        <!--</div>-->
                    </div>
                    <div class="modal-footer">
                        <span class="text-red validation-error-msg">{{ validation_message }}</span>
                        <template v-if="!statuses.saving_document_type">
                            <button
                                type="button"
                                class="btn btn-default"
                                @click.prevent="deleteDocument"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click.prevent="saveDocumentType"
                            >
                                OK
                            </button>
                        </template>

                        <pageloader add-classes="save-loader" v-show="statuses.saving_document_type"></pageloader>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="delete-patient-provider-modal" role="dialog" style="z-index:9999;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5 v-if="provider_to_delete">
                            Are you sure you want to remove <b>{{provider_to_delete.provider_name}}</b> from the
                            <b>{{fullName(patient)}}</b>
                        </h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger"
                                @click.prevent="deletePatientProviderRelationship()">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default"
                                @click.prevent="closeDeletePatientProviderModal()">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="add-patient-provider-modal" role="dialog" style="z-index:9999;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <select class="form-control" v-if="available_providers" v-model="selected_provider">
                            <option v-for="p in available_providers" :value="p.id">{{p.provider_name}}</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click.prevent="addPatientProviderRelationship()"
                                :disabled="!available_providers || !available_providers.length || !selected_provider">
                            Assign
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="closeAddPatientProviderModal()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            id="pls-add-initial-assessment-before-upload-doc-modal"
            class="modal modal-vertical-center fade"
            data-backdrop="static"
            data-keyboard="false"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p>
                            The document cannot be uploaded at this time. You must first complete the Initial Assessment
                            Form through this system for all new patients. If you believe you see this message due to an
                            error,
                            please contact your system administrator for further assistance at:
                            <a href="mailto:support@cwr.care">support@cwr.care</a>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <div class="dropdown inline-block">
                            <button
                                v-if="!isUserAdmin"
                                class="btn btn-primary"
                                type="button"
                                data-toggle="dropdown"
                                @click.prevent="showDocument(electronicDocumentsTypes.cwr_initial_assessment)"
                            >
                                Add Initial Assessment
                            </button>
                        </div>

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="pls-add-initial-assessment-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p>
                            Progress Notes cannot be added at this time. You must first complete the Initial Assessment
                            Form through this system for all new patients. If you believe you see this message due to an
                            error,
                            please contact your system administrator for further assistance at:
                            <a href="mailto:support@cwr.care">support@cwr.care</a>
                        </p>
                        <br>
                        <p><b>IMPORTANT! PLEASE READ:</b></p>
                        <ol style="padding-left:15px;">
                            <li>
                                The Initial Assessment Form must be created and added through this system. It cannot be
                                uploaded
                                as an additional document. If you are not sure how to do this, please schedule a
                                training session
                                with our support team. Email requests to:
                                <a href="mailto:support@cwr.care">support@cwr.care</a> (put "Training" in the subject)
                            </li>
                            <li>
                                You do not need to file a Progress Note on the same date of service in addition to the
                                Initial Assessment for new patients.
                            </li>
                            <li>
                                You must make sure the Initial Assessment is filed on time, and if required, also sent
                                to the insurance company via secure
                                channel to ensure compliance in accordance with the instructions provided during
                                training.
                            </li>
                            <li>
                                You must verify the identity of each patient and collect all information required to
                                provide services on behalf of the company
                                (retain a copy of insurance card, photo ID, and other documents)
                            </li>
                            <li>
                                You are responsible for collecting co-payments from patients on the date when services
                                were provided and must fulfill any documentation
                                filing requests from patients in accordance with the state and federal laws.
                            </li>
                        </ol>
                    </div>
                    <div class="modal-footer">
                        <div class="dropdown inline-block">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                Add Initial Assessment
                            </button>
                            <ul class="dropdown-menu">
                                <li v-if="initial_assessment_forms" v-for="item in initial_assessment_forms">
                                    <a href="" @click.prevent="showDocument(item.slug)">{{item.title}}</a>
                                </li>
                            </ul>
                        </div>

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="document-mail-send-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-loader-container" v-if="sending">
                    <pageloader add-classes="note-loader"></pageloader>
                </div>
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                @click.prevent="hideEmailModal()"
                                :disabled="sending">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Send this document via email</h4>
                    </div>
                    <form class="form-horizontal" @submit.prevent>
                        <div class="modal-body">

                            <div class="form-group" id="email-to">
                                <label class="control-label col-sm-2">To</label>
                                <div class="col-sm-10">
                                    <select class="form-control" v-model="selected_email">
                                        <option v-for="item in document_default_emails" :value="item.email">
                                            {{item.title}}
                                        </option>
                                        <option value="-1">Enter another email address</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="selected_email && selected_email != -1">
                                <div class="form-group">
                                    <label class="control-label col-sm-2">Email: </label>
                                    <div class="col-sm-10" style="padding-top:7px;">
                                        {{documentEmail}}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2">Password: </label>
                                    <div class="col-sm-10" style="padding-top:7px;">
                                        {{documentPassword}}
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="selected_email">
                                <div class="form-group" id="document-email">
                                    <label class="control-label col-sm-2">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" v-model="documentEmail">
                                    </div>
                                </div>
                                <div class="form-group" id="document-password">
                                    <label class="control-label col-sm-2">Password</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" v-model="documentPassword">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <span class="text-red validation-error-msg">{{ validation_message }}</span>
                            <button type="button" class="btn btn-primary"
                                    @click.prevent="sendDocumentByEmail(sendingDocumentId)"
                                    :disabled="sending || errors.has('document-password document-email')">
                                Send
                            </button>

                            <pageloader add-classes="save-loader" v-show="statuses.saving_document_type"></pageloader>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="document-fax-send-modal" role="dialog">

            <div class="modal-dialog">
                <div class="modal-loader-container" v-if="sending">
                    <pageloader add-classes="note-loader"></pageloader>
                </div>
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                @click.prevent="hideFaxModal()"
                                :disabled="sending">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Send this document via fax</h4>
                    </div>
                    <form class="form-horizontal" @submit.prevent>
                        <div class="modal-body">

                            <div class="form-group" id="fax-to">
                                <label class="control-label col-sm-1">To</label>
                                <div class="col-sm-11">
                                    <select class="form-control" v-model="selected_fax">
                                        <option v-for="item in document_default_faxes" :value="item.fax">
                                            {{item.title}}
                                        </option>
                                        <option value="-1">Enter another fax number</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="selected_fax && selected_fax != -1">
                                <div class="form-group">
                                    <label class="control-label col-sm-1">Fax: </label>
                                    <div class="col-sm-11" style="padding-top:7px;">
                                        {{documentFax}}
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="selected_fax">
                                <div class="form-group">
                                    <label class="col-xs-12" :class="{'error': documentFax.length !== 10}"
                                           for="document-fax">Please enter fax number, digits only.</label>
                                    <div class="col-xs-12">
                                        <the-mask id="document-fax" name="fax"
                                                  ref="fax"
                                                  type="tel"
                                                  :class="{'input': true, 'error': documentFax.length !== 10 }"
                                                  @keydown.enter.prevent
                                                  class="form-control" v-validate="'numeric|digits:10'"
                                                  mask="+1 (###) ###-####"
                                                  raw="true"
                                                  v-model="documentFax"></the-mask>
                                        <span v-show="documentFax.length !== 10"
                                              class="help error">Fax must have 10 digits</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <span class="text-red validation-error-msg">{{ validation_message }}</span>
                            <button type="button" class="btn btn-primary"
                                    @click.prevent="sendDocumentByFax(sendingDocumentId)"
                                    :disabled="sending || (selected_fax == -1 && documentFax.length !== 10)">
                                Send
                            </button>

                            <pageloader add-classes="save-loader" v-show="statuses.saving_document_type"></pageloader>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-deletion-document" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5 v-if="doc_to_delete" v-text="getConfirmDeletionDocumentText"></h5>
                    </div>
                    <div class="modal-footer">
                        <span class="text-red validation-error-msg">{{ validation_message }}</span>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteDoc()">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="closeConfirmDeleting()">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-deletion-comment" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>Are you sure you want to delete comment?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click.prevent="deleteComment()">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="closeConfirmDeletingComment()">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-sync-patient-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Synchronize Patient Information</h5>
                    </div>
                    <div class="modal-body">
                        <p v-if="patient">This action will retrieve information associated with this patient record
                            (such as appointments, visits, patient information, etc.) from Office Ally. Do you want to
                            synchronize this patient's information now?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click.prevent="syncPatientWithOfficeAlly()">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            id="patient-therapist-list-modal"
            class="modal modal-vertical-center fade"
            data-backdrop="static"
            data-keyboard="false"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content" v-loading="statuses.detaching_provider">
                    <div class="modal-header">
                        <h5 class="modal-title">Assigned to:</h5>
                    </div>
                    <div class="modal-body">
                        <ul v-if="patient">
                            <li v-for="p in patient.providers">
                                {{ p.provider_name }}
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-between">
                            <a v-if="isUserAdmin" href="/dashboard/patients-management" target="_blank" class="btn btn-primary mr-auto">
                                Unassign therapist
                            </a>
                            <div class="d-flex">
                                <button v-if="isUserAdmin" class="btn btn-primary" @click.prevent="showAddPatientProviderRelationship()">
                                    Assign Therapist
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="confirm-change-document-status" class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5 v-if="doc_to_change_status">Are you sure you want to make document as Public/Private?</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click.prevent="confirmChangingDocumentStatus()">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="cancelChangingDocumentStatus">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-info" class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body" v-html="modal_info_msg"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-move-to-archive" class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>
                            Are you sure you want to archive the patient?
                        </h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click.prevent="movePatientToArchive"
                                :disabled="statuses.moving_to_archive">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default"
                                @click.prevent="closeModal('modal-move-to-archive')"
                                :disabled="statuses.moving_to_archive">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-change-email" class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content" v-loading="statuses.changing_email">
                    <div class="modal-header">
                        <h4 class="modal-title">Change Email</h4>
                    </div>
                    <div class="modal-body">
                        <el-form :rules="emailFormRules" :model="emailFormData" ref="emailForm" class="row justify-content-center">
                            <div class="col-md-6">
                                <el-form-item label="Email" prop="patient_email">
                                    <el-input type="email" v-model="emailFormData.patient_email"></el-input>
                                </el-form-item>
                            </div>

                            <div class="col-md-6">
                                <el-form-item label="Email Add" prop="patient_secondary_email">
                                    <el-input type="email" v-model="emailFormData.patient_secondary_email" placeholder="Email Add"></el-input>
                                </el-form-item>
                            </div>
                        </el-form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" :disabled="statuses.changing_email" @click.prevent="changePatientEmails">
                            Save
                        </button>
                        <button type="button" class="btn btn-default" :disabled="statuses.changing_email" @click.prevent="closeModal('modal-change-email')">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
           
        <div id="modal-draft-list" class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5>
                            You have {{ draftProgressNotes.length }} unfinished progress notes. You may either edit them
                            or create a new one.
                        </h5>
                        <table class="table table-draft-list" v-if="draftProgressNotes.length > 0">
                            <thead>
                            <tr>
                                <th>
                                    Created at
                                </th>
                                <th>
                                    Date of Service
                                </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="draftPN in draftProgressNotes" :key="`pn-${draftPN.id}`">
                                <td>{{ getFormattedDateTime(draftPN.created_at) }}</td>
                                <td>{{ draftPN.date_of_service ? getFormattedDateTime(draftPN.date_of_service) : '-' }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-primary"
                                            @click.prevent="viewNoteFromModal(draftPN.id)">
                                        View/Edit
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click.prevent="createBlankNoteFromModal">
                            Add Progress Note
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="closeModal('modal-draft-list')">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <kaiser-referrals
                v-if="this.$store.state.currentDocument == 'kaiser-referrals'"
                :patient="patient"
        >
        </kaiser-referrals>
        <cwr-initial-assessment
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.cwr_initial_assessment"
        ></cwr-initial-assessment>
        <kp-initial-assessment-adult-pc
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_initial_assessment_adult_pc"
        ></kp-initial-assessment-adult-pc>
        <kp-initial-assessment-child-pc
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_initial_assessment_child_pc"
        ></kp-initial-assessment-child-pc>
        <kp-initial-assessment-adult-wh
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_initial_assessment_adult_wh"
        ></kp-initial-assessment-adult-wh>
        <kp-initial-assessment-child-wh
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_initial_assessment_child_wh"
        ></kp-initial-assessment-child-wh>
        <kp-initial-assessment-child-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_initial_assessment_child_la"
        ></kp-initial-assessment-child-la>
        <kp-initial-assessment-adult-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_initial_assessment_adult_la"
        ></kp-initial-assessment-adult-la>
        <va-request-for-reauthorization
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.va_request_for_reauthorization"
        ></va-request-for-reauthorization>
        <kp-request-for-reauthorization-pc
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_request_for_reauthorization_pc"
        ></kp-request-for-reauthorization-pc>
        <kp1-request-for-reauthorization-wh
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp1_request_for_reauthorization_wh"
        >
        </kp1-request-for-reauthorization-wh>
        <kp2-request-for-reauthorization-wh
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp2_request_for_reauthorization_wh"
        >
        </kp2-request-for-reauthorization-wh>
        <kp-request-for-reauthorization-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_request_for_reauthorization_la"
        >
        </kp-request-for-reauthorization-la>
        <cwr-patient-discharge-summary
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.cwr_patient_discharge_summary"
        >
        </cwr-patient-discharge-summary>
        <kp-patient-discharge-summary
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_patient_discharge_summary"
        ></kp-patient-discharge-summary>
        <kp-patient-discharge-summary-wh
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_patient_discharge_summary_wh"
        ></kp-patient-discharge-summary-wh>
        <kp-patient-discharge-summary-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_patient_discharge_summary_la"
        ></kp-patient-discharge-summary-la>
        <axminster-rfr
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.axminster_rfr"
        ></axminster-rfr>
        <facey-rfr
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.facey_rfr"
        ></facey-rfr>
        <kp-behavioral-health-pc
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_behavioral_health_pc"
        ></kp-behavioral-health-pc>
        <kp-medication-evaluation-referral
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_medication_evaluation_referral"
        ></kp-medication-evaluation-referral>
        <kp-bhios-wh
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_bhios_wh"
        ></kp-bhios-wh>
        <kp-medication-evaluation-referral-pc
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_medication_evaluation_referral_pc"
        ></kp-medication-evaluation-referral-pc>
        <kp-medication-evaluation-referral-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_medication_evaluation_referral_la"
        ></kp-medication-evaluation-referral-la>
        <kpep-couples-counseling-referral
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kpep_couples_counseling_referral"
        ></kpep-couples-counseling-referral>
                <kpep-group-referral
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kpep_group_referral"
        ></kpep-group-referral>
                <kpep-intensive-treatment-referral
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kpep_intensive_treatment_referral"
        ></kpep-intensive-treatment-referral>
                <kpep-medication-evaluation-referral
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kpep_medication_evaluation_referral"
        ></kpep-medication-evaluation-referral>
        <kp-ref-for-groups-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_referral_for_groups_los_angeles"
        ></kp-ref-for-groups-la>
        <kp-ref-to-hloc-la
                v-if="this.$store.state.currentDocument == this.electronicDocumentsTypes.kp_hloc_los_angeles"
        ></kp-ref-to-hloc-la>
        <document-confirm-dialog
                :modalId="this.$store.state.currentDocument"
        ></document-confirm-dialog>

        <management-patient-dialog
            v-if="isShowPatientDialog"
            :patient-id="patient && patient.id"
            :is-show-dialog="isShowPatientDialog"
            :is-created="isCreatedPatientDialog"
            @closeDialog="closePatientDialog" 
        />

        <email-unsubscribed-dialog
            :patient-id="patient && patient.id"
            :email="restoreEmail"
            :is-admin="!!isUserAdmin"
            :show="showEmailUnsubscribedDialog"
            :close="closeEmailUnsubscribedDialog"
            @emailRemovedFromRejectList="onEmailRemovedFromRejectList"
        />

        <consent-info-modal v-if="isUserAdmin" :consent-info="patient && patient.last_document_consent_info"/>

        <credit-cards-modal :cards="creditCards" :formatter="getFormattedCardDetails" :isExpired="isCardExpired"/>

        <change-visits-frequency-modal v-if="patient" :patient="patient" />

        <call-patient-dialog
            :isVisible="callDialogIsVisible"
            :setIsVisible="setCallDialogIsVisible"
            :is-admin="!!isUserAdmin"
        />

        <download-docs  
           v-if="isShowDocumentDownloadDialog"   
           :is-show-dialog="isShowDocumentDownloadDialog" 
           @closeDialog="closeDocumentDownloadDialog" 
        />
    </div>
</template>

<script>
    import {OFFICE_ALLY_BASE_HREF, UPHEAL_BASE_HREF} from "../settings";
    import CommentBlock from "./comments/CommentBlock.vue";
    import PatientBalance from '../mixins/patient-balance';
    import onbeforeunload from '../helpers/onbeforeunload';
    import datetimeFormatted from '../mixins/datetime-formated.js';
    import UploadFileSize from '../mixins/upload-file-size.js';
    import ManagementPatientDialog from "./ManagementPatientDialog";
    import ConsentInfoModal from "./main-view/ConsentInfoModal";
    import CreditCardsModal from "./main-view/CreditCardsModal";
    import ChangeVisitsFrequencyModal from "./main-view/ChangeVisitsFrequencyModal";
    import KaiserReferrals from "./documents/kaiser/KaiserReferrals.vue";
    import AddFormDropdown from "./main-view/AddFormDropdown";
    import CancellationFeeRestrictions from './appointments/CancellationFeeRestrictions';
    import DownloadDocs from "./dashboard/components/DownloadDocs";
    import PatientTags from "./PatientTags.vue";
    import VisitFrequencyChangesTooltip from "./VisitFrequencyChangesTooltip.vue";
    import { Notification } from "element-ui";
  
    export default {
        components: {
            PatientTags,
            CommentBlock,
            ManagementPatientDialog,
            ConsentInfoModal,
            CreditCardsModal,
            AddFormDropdown,
            ChangeVisitsFrequencyModal,
            CancellationFeeRestrictions,
            DownloadDocs,
            KaiserReferrals,
            VisitFrequencyChangesTooltip
        },

        data() {
            return {
                emailFormData: {
                    patient_email: '',
                    patient_secondary_email: ''
                },
                emailFormRules: {
                    patient_email: [
                        { type: 'email', message: ' Invalid email address', trigger: ['change', 'blur']}
                    ],
                    patient_secondary_email: [
                        { type: 'email', message: ' Invalid additional email address', trigger: ['change', 'blur']}
                    ]
                },
                uploader: null,
                comment: '',
                documentComment: '',
                documentCommentError: false,
                documentCommentErrorMessage: 'Error connecting to server. Please try to post comment later.',
                selected_email: null,
                documentEmail: '',
                documentPassword: '',
                selected_fax: null,
                documentFax: '',
                sending: false,
                sendingDocumentId: null,
                sendingDocumentModel: null,
                sendingDocument: null,
                statuses: {
                    loading_note_blank: false,
                    saving_document_type: false,
                    appointments_loading: false,
                    visits_loading: false,
                    notifications_loading: false,
                    transactions_loading: false,
                    moving_to_archive: false,
                    changing_email: false,
                    patient_forms_loading: false,
                    starting_video_session: false,
                    detaching_provider: false,
                },
                other_type_id: null,
                document_type_id: null,
                document_id: null,
                other_document_type: '',
                has_document_without_type: false,
                document_types_html: '',
                validation_message: '',
                doc_to_delete: null,
                comment_to_delete: null,
                doc_to_change_status: null,
                is_init: true,
                office_ally_href: OFFICE_ALLY_BASE_HREF,
                upheal_href: UPHEAL_BASE_HREF,
                tabs: {
                    chart: {
                        active: false,
                    },
                    timeline: {
                        active: true,
                    },
                    patientForms: {
                        active: false,
                    },
                    appointments: {
                        active: false,
                        count: '0/0',
                    },
                    visits: {
                        active: false,
                        count: 0,
                    },
                    notifications: {
                        active: false,
                        count: 0,
                    },
                    transactions: {
                        active: false,
                    },
                    patient_forms_count:{
                        count: '0/0',
                    },
                    patient_card_transactions:{
                        count: 0,
                    }

                },
                default_document_previews: {},
                tribute: null,
                provider_to_delete: null,
                available_providers: null,
                selected_provider: null,
                initial_assessment_forms: null,
                visible_only_for_admin: false,
                patientInterval: null,
                modal_info_msg: '',
                visit_created_count: 0,
                discharge_ids: [],
                patientLanguageList:[],
                electronicDocumentsInfo: null,
                electronicDocumentsTypes: {
                    cwr_initial_assessment: 'none',
                    kp_initial_assessment_adult_pc: 'none',
                    kp_initial_assessment_child_pc: 'none',
                    kp_initial_assessment_adult_wh: 'none',
                    kp_initial_assessment_child_wh: 'none',
                    kp_initial_assessment_child_la: 'none',
                    kp_initial_assessment_adult_la: 'none',
                    va_request_for_reauthorization: 'none',
                    kp_request_for_reauthorization_pc: 'none',
                    kp1_request_for_reauthorization_wh: 'none',
                    kp2_request_for_reauthorization_wh: 'none',
                    kp_request_for_reauthorization_la: 'none',
                    cwr_patient_discharge_summary: 'none',
                    kp_patient_discharge_summary: 'none',
                    kp_patient_discharge_summary_wh: 'none',
                    kp_patient_discharge_summary_la: 'none',
                    axminster_rfr: 'none',
                    facey_rfr: 'none',
                    kp_behavioral_health_pc: 'none',
                    kp_medication_evaluation_referral: 'none',
                    kp_medication_evaluation_referral_pc: 'none',
                    kp_medication_evaluation_referral_la: 'none',
                    kp_bhios_wh: 'none',
                    kp_referral_for_groups_los_angeles: 'none',
                    kp_hloc_los_angeles: 'none',
                    kpep_couples_counseling_referral: 'none',
                    kpep_group_referral: 'none',
                    kpep_intensive_treatment_referral: 'none',
                    kpep_medication_evaluation_referral: 'none',

                },
                draftProgressNotes: [],
                isShowPatientDialog: false,
                isCreatedPatientDialog: false,
                showRemovalRequestButton: false,
                showCancelRemovalRequestButton: false,
                showEmailUnsubscribedDialog: false,
                restoreEmail: null,
                isShowDocumentDownloadDialog: false,
                callDialogIsVisible: false,
                scrollTo: null,
            }
        },
        mixins: [
            PatientBalance,
            datetimeFormatted,
            UploadFileSize,
        ],
        computed: {
            isUserAdmin() {
                return this.$store.state.isUserAdmin;
            },
            formatted_patient_copay() {
                if (this.patient.visit_copay) {
                    let co_pay = parseFloat(this.patient.visit_copay);
                    if (co_pay > 0) {
                        co_pay = '$' + co_pay;
                        if (this.patient.insurance_plan && !this.patient.insurance_plan.need_collect_copay_for_telehealth) {
                            co_pay = co_pay + ' (Waived for Telehealth Visits)';
                        }
                        return co_pay;
                    }
                }

                return '-';
            },

            patient_charge_for_cancellation() {
                return this.patient && this.patient.charge_for_cancellation_appointment ? parseFloat(this.patient.charge_for_cancellation_appointment) : 0
            },

            formatted_patient_charge_for_cancellation() {
                return this.patient_charge_for_cancellation > 0 ? `$${this.patient_charge_for_cancellation}` : '-';
            },

            formatted_payment_forbidden() {
                return this.patient && this.patient.is_payment_forbidden ? 'Forbidden' : 'Allowed';
            },

            loading_transactions_tab() {
                return this.$store.state.loading_transactions_tab;
            },

            document_default_faxes() {
                return this.$store.state.document_default_faxes;
            },

            document_default_emails() {
                return this.$store.state.document_default_emails;
            },

            is_read_only_mode() {
                return this.$store.state.is_read_only_mode;
            },
            is_audit_mode() {
                return this.$store.state.is_audit_mode;
            },

            providers_dataset() {
                return this.$store.state.providers_dataset_for_tribute;
            },

            document_previews() {
                return this.$store.state.documents_preview;
            },

            getConfirmDeletionDocumentText() {
                let text = "Are you sure you want to delete ";
                if (this.doc_to_delete.model === 'PatientNote') {
                    text += 'progress note?';
                } else {
                    text += (this.doc_to_delete.original_document_name ? this.doc_to_delete.original_document_name : this.doc_to_delete.document_name) + " document?";
                }
                return text;
            },

            patient_statuses() {
                return this.$store.state.patient_statuses;
            },

            patient_lost_status() {
                return this.patient_status("Lost")
            },

            patient_archive_status() {
                return this.patient_status("Archived")
            },

            is_other_document_type() {
                if (this.document_type_id !== null && this.document_type_id == this.other_type_id) {
                    return true;
                }
                return false
            },

            isNoteLoading() {
                return this.$store.state.isNoteLoading;
            },

            patient() {
                return this.$store.state.currentPatient;
            },

            patientAddress() {
                if (this.patient.address || this.patient.city || this.patient.state || this.patient.zip) {
                    return this.createPatientAddressText([this.patient.address, this.patient.city, this.patient.state, this.patient.zip]);
                }
                return '-';
            },
            getAdditionalPhones() {
                return (phone_type) => {
                  return this.patient.additional_phones.filter(item => item.phone_type === phone_type);
                }
            },
            provider() {
                return this.$store.state.currentProvider;
            },
            isCollectPaymentAvailable() {
                return this.provider && this.provider.is_collect_payment_available;
            },
            notes() {
                return this.$store.state.currentPatientNotes;
            },
            documents() {
                return this.$store.state.currentPatientDocuments;
            },
            add_note_is_blocked() {
                return this.$store.state.add_note_is_blocked;
            },
            patientNoteCount() {
                return this.$store.state.progressNoteCount; 
            },
            patientAppointmentCount() {
                return this.$store.state.appointmentCount;
            },
            patientAppointmentVisitCreatedCount() {
                return this.$store.state.appointmentVisitCreatedCount;
            },
            patientAppointmentCompletedCount() {
                return this.$store.state.appointmentCompletedCount;
            },
            patientGoogleMeetingAppointmentCount() {
                return this.$store.state.googleMeetingAppointmentCount;
            },
            patientRingCentralAppointmentCount() {
                return this.$store.state.ringCentralAppointmentCount;
            },
            patientVisitAverageDuration() {
                return this.$store.state.visitAverageDuration;
            },
            patientDraftNoteCount() {
                return this.$store.state.draftProgressNoteCount;
            },
            patientMissingNoteCount() {
                return this.$store.state.missingProgressNoteCount;
            },
            patientInitialAssessmentCount() {
                return this.$store.state.initialAssessmentCount;
            },
            patientCancelledAppointments() {
                return this.$store.state.cancelledAppointments;
            },

            patientCancelledAppointmentsCount() {
                return this.patientCancelledAppointments ? this.patientCancelledAppointments.length : 0;
            },

            patientPaperNoteCount() {
                return this.$store.state.paperNoteCount;
            },

            isUserAdmin() {
                return this.$store.state.isUserAdmin;
            },
            isUserSecretary() {
                return this.$store.state.isUserSecretary;
            },
            isUserPatientRelationManager() {
                return this.$store.state.isUserPatientRelationManager;
            },
            assessmentFormsTemplates() {
                return this.$store.state.assessmentFormsTemplates;
            },
            document_types() {
                return this.$store.state.document_types;
            },

            appointments() {
                return this.$store.state.patient_appointments;
            },

            visits() {
                return this.$store.state.patient_visit_created_appointments;
            },

            patientForms() {
                return this.$store.state.patient_forms;
            },

            notifications() {
                return this.$store.state.patient_notifications;
            },
            notifications_count() {
                return this.$store.state.patient_notifications_count;
            },

            isShowMoveToArchive() {
                return this.isUserAdmin
                    && !this.is_read_only_mode
                    && this.patient.status
                    && this.patient.status.id == this.patient_lost_status.id
            },

            paginationChart() {
                return this.$store.state.paginationChart;
            },

            transactions() {
                return this.$store.state.patient_transactions;
            },

            insuranceAuthorizationNumberData() {
                if (!this.patient.insurance_plan) {
                    return null;
                }

                return {
                    insurance_requires_verification: this.patient.insurance_plan.is_verification_required,
                    insurance_authorization_number: this.patient.auth_number,
                    insurance_visits_auth: this.patient.visits_auth,
                    insurance_visits_auth_left: this.patient.visits_auth_left,
                    insurance_eff_start_date: this.patient.eff_start_date,
                    insurance_eff_stop_date: this.patient.eff_stop_date,
                    reauthorization_notification_visits_count: this.patient.insurance_plan.reauthorization_notification_visits_count,
                    reauthorization_notification_days_count: this.patient.insurance_plan.reauthorization_notification_days_count,
                };
            },

            creditCards() {
                if (!this.patient || !this.patient.square_accounts) {
                    return [];
                }

                return this.patient.square_accounts.reduce((prev, curr) => {
                    if (curr.cards) {
                        prev.push(...curr.cards);
                    }
                    
                    return prev;
                }, []);
            },

            showAuthorizationNumber() {
                if (!this.insuranceAuthorizationNumberData) {
                    return false;
                }
                if (this.insuranceAuthorizationNumberData.insurance_requires_verification) {
                    return true;
                }

                return !!this.insuranceAuthorizationNumberData.insurance_authorization_number;
            },

            canChargeLateCancellationFee() {
                let canChargeLateCancellationFee = true;

                if (this.patient.cancelation_fee_info) {
                    const properties = Object.values(this.patient.cancelation_fee_info.booking_cancellation_policy);
                    canChargeLateCancellationFee = properties.every((property) => property);
                }

                return canChargeLateCancellationFee;
            },

            percentageOfCanceledAppointments() {
                if (!this.patientAppointmentCount) {
                    return 0;
                }
                
                return Math.round(this.patientCancelledAppointmentsCount / this.patientAppointmentCount * 100);
            },

            percentageOfGoogleMeetingAndRingCentralAppointments() {
                if (!this.completedAndVisitCreatedAppointmentCount) {
                    return 0;
                }

                return Math.round((this.patientGoogleMeetingAppointmentCount + this.patientRingCentralAppointmentCount) / this.completedAndVisitCreatedAppointmentCount * 100);
            },

            cancelledAppointmentTooltipText() {
                let text = "";

                if (this.patientCancelledAppointments) {
                    const statusCount = {};

                    this.patientCancelledAppointments.forEach(appointment => {
                        const statusName = appointment.status.status;
                        if (!statusCount[statusName]) {
                            statusCount[statusName] = 0;
                        }
                        statusCount[statusName] += 1;
                    });

                    for (const statusName in statusCount) {
                        const count = statusCount[statusName];
                        text += `<div>${statusName}: ${count}</div>`;
                    }
                }
                
                return text;
            },

            completedAndVisitCreatedAppointmentCount() {
                return this.patientAppointmentVisitCreatedCount + this.patientAppointmentCompletedCount;
            }
        },

        watch: {
            selected_email() {
                if (this.selected_email) {
                    $('#email-to').removeClass('with-errors');
                }
                if (this.selected_email == -1) {
                    this.documentEmail = "";
                    this.documentPassword = "";
                } else {
                    $('#document-email').removeClass('with-errors');
                    $('#document-password').removeClass('with-errors');
                    for (let i in this.document_default_emails) {
                        if (this.document_default_emails[i]['email'] === this.selected_email) {
                            this.documentEmail = this.document_default_emails[i]['email'];
                            this.documentPassword = this.document_default_emails[i]['password'];
                        }
                    }

                }
            },

            selected_fax() {
                if (this.selected_fax) {
                    $('#fax-to').removeClass('with-errors');
                }
                if (this.selected_fax == -1) {
                    this.documentFax = "";
                } else {
                    for (let i in this.document_default_faxes) {
                        if (this.document_default_faxes[i]['fax'] === this.selected_fax) {
                            this.documentFax = this.document_default_faxes[i]['fax'];
                        }
                    }

                }
            },

            documentEmail() {
                if (this.documentEmail.length) {
                    $('#document-email').removeClass('with-errors');
                }
            },
            documentPassword() {
                if (this.documentPassword.length) {
                    $('#document-password').removeClass('with-errors');
                }
            },

            // isNoteLoading() {
            //     if (!this.isNoteLoading) {

            //     }
            // },

            has_document_without_type() {
                let self = this;
                if (this.has_document_without_type) {
                    window.onbeforeunload = function (event) {
                        self.$store.dispatch('deleteDocument', {id: self.document_id}).then(response => {
                            if (response.status === 401) {
                                window.onbeforeunload = null;
                            }
                        });
                    };
                } else {
                    window.onbeforeunload = null;
                }
            },

            document_type_id() {
                $('#document_type_id').removeClass('input-error');
                this.validation_message = "";
            },

            other_document_type() {
                $('#document_other_type input').removeClass('input-error');
                $('#document_other_type label').removeClass('label-error');
                this.validation_message = '';
            },

            patient() {
                if (!this.patient) {
                    return;
                }

                if(this.$route.query.tab) {
                  this.changeTab(this.$route.query.tab);
                }
                if(this.$route.query.action === 'add_progress_note') {
                  this.openNoteBlank(this.$route.query.appointment_id);
                  this.$router.push('/chart/' + this.patient.id);
                }
                this.emailFormData.patient_email = this.patient.email;
                this.emailFormData.patient_secondary_email = this.patient.secondary_email;
                if (this.patientInterval) {
                    window.clearInterval(this.patientInterval);
                    this.patientInterval = null;
                }
                if (this.patient.start_synchronization_time) {
                    this.patientInterval = window.setInterval(() => {
                        this.$store.dispatch('checkPatientIsSynchronized', this.patient.id).then(({data}) => {
                            if (!data) {
                                return;
                            }
                            
                            this.$store.dispatch('getPatient', {patientId: this.patient.id});
                        });
                    }, 30000);
                }
                window.setTimeout(() => {
                    this.openUploadForm();
                    if (this.isUserAdmin && !this.isUserSecretary) {
                        this.$store.commit('setVal', {
                            key: 'patient_square_customers',
                            val: []
                        });
                        this.$store.dispatch('getPatientSquareCustomers', this.patient.id);
                    }
                }, 1000);
            },
            visits() {
                if (this.visits) {
                    this.tabs.visits.count = this.visits.length;
                }
            },

            '$route'(to, from) {
                this.init();
                this.$store.dispatch('clearTabsData');
                let activeTab = 'timeline';
                let tabNames = Object.keys(this.tabs);
                for (let tabName of tabNames) {
                    if (this.tabs[tabName].active) {
                        activeTab = tabName;
                    }
                }
                this.changeTab(activeTab);
            },
        },
        methods: {
            openEmailUnsubscribedDialog(email) {
                this.restoreEmail = email;
                this.showEmailUnsubscribedDialog = true;
            },

            closeEmailUnsubscribedDialog() {
                this.showEmailUnsubscribedDialog = false;
            },

            onEmailRemovedFromRejectList() {
                if (!this.$route.name === 'patient-chart' || !this.$route.params.id) {
                    return;
                }

                this.$store.dispatch('getPatient', {patientId: this.$route.params.id})
                    .catch(() => {
                        //
                    });
                this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {id: this.$route.params.id})
                    .catch(() => {
                        //
                    });
            },

            syncPatientWithOfficeAlly() {
                let payload = {
                    patientOfficeAllyId: this.patient.patient_id,
                };
                this.$store.dispatch('syncPatientWithOfficeAlly', payload).then(response => {
                    this.$store.dispatch('getPatient', {patientId: this.patient.id});
                });
                $('#confirm-sync-patient-modal').modal('hide');
            },

            telehealthConfirmed(status) {
              this.$emit('telehealth-confirmed', status);
            },

            showConfirmSyncPatient() {
                $('#confirm-sync-patient-modal').modal('show');
            },

            showAddPatientProviderRelationship() {
                this.$store.dispatch('getAvailableProvidersForPatient', this.patient.id).then(response => {
                    this.available_providers = response.data;
                });
                $('#add-patient-provider-modal').modal('show');
            },

            addPatientProviderRelationship() {
                let payload = {
                    patientId: this.patient.id,
                    providerId: this.selected_provider,
                };
                this.$store.dispatch('addPatientProviderRelationship', payload).then(() => {
                    const patientId = this.patient.id;
                    this.$store.dispatch('getPatient', {patientId: patientId}).then(() => {
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: patientId});
                        if (this.patient.providers.length === 1) {
                            this.$store.dispatch('getSearchedPatients', {query: ''});
                        }
                    });
                });
                this.closeAddPatientProviderModal();
            },

            closeAddPatientProviderModal() {
                $('#add-patient-provider-modal').modal('hide');
                this.available_providers = null;
                this.selected_provider = null;
            },

            showDeletePatientProviderRelationship(provider) {
                this.provider_to_delete = provider;
                $('#delete-patient-provider-modal').modal('show');
            },

            closeDeletePatientProviderModal() {
                $('#delete-patient-provider-modal').modal('hide');
                this.provider_to_delete = null;
            },

            deletePatientProviderRelationship() {
                this.statuses.detaching_provider = true;
                let payload = {
                    patientId: this.patient.id,
                    providerId: this.provider_to_delete.id,
                };
                this.$store.dispatch('deletePatientProviderRelationship', payload).then(() => {
                    const patientId = this.patient.id;
                    this.$store.dispatch('getPatient', {patientId: patientId}).then(response => {
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: patientId});
                        if (!this.patient.providers.length) {
                            $('#patient-therapist-list-modal').modal('hide');
                            this.$store.dispatch('getSearchedPatients', {query: ''});
                        }
                    })
                }).finally(() => {
                    this.statuses.detaching_provider = false;
                });
                this.closeDeletePatientProviderModal();
            },

            getLoadingNoteBlankStatus() {
                return this.statuses.loading_note_blank;
            },

            initTributePlugin() {
                let self = this;
                this.tribute = new Tribute({
                    values: self.providers_dataset,
                    selectTemplate: function (item) {
                        return '<span class="comment-mention" data-id="' + item.original.id + '" contenteditable="false">@' + item.original.value + '</span>';
                    },
                });
                window.setTimeout(() => {
                    self.tribute.attach(document.getElementById('comment-textarea'));
                }, 500);
            },

            getPatientInsurance(patient) {
                let html = '';
                if (patient.primary_insurance) {
                    html = patient.primary_insurance;
                } else {
                    return '-';
                }
                if (patient.secondary_insurance) {
                    html += ",<br/>" + patient.secondary_insurance;
                }
                return html;
            },

            changeTab(tabName) {
                if (this.tabs[tabName]) {
                    for (let i in this.tabs) {
                        this.tabs[i].active = false;
                    }
                    this.tabs[tabName].active = true;
                    if (tabName === 'chart') {
                        this.openUploadForm();
                        window.setTimeout(() => {

                        }, 1000);

                    }

                    this.loadTabData(tabName);

                    if (tabName === 'timeline') {
                        $('.zEWidget-launcher, .zEWidget-webWidget').css('bottom', '85px');
                    } else {
                        $('.zEWidget-launcher, .zEWidget-webWidget').css('bottom', '0');
                    }
                }
            },

            loadTabData(tabName) {
                if (!this[tabName] || this[tabName].length < 1) {
                    switch (tabName) {
                        case 'appointments':
                            this.loadAppointments();
                            break;
                        case 'visits':
                            this.loadVisits();
                            break;
                        case 'notifications':
                            this.loadNotifications();
                            break;
                        case 'transactions':
                            this.loadTransactions();
                            break;
                        case 'patientForms':
                            this.loadPatientForms();
                            break;
                    }
                }
            },

            loadPatientForms() {
                this.statuses.patient_forms_loading = true;
                this.$store.dispatch('getPatientFormsForChart', {id: this.$route.params.id})
                    .finally(() => {
                        this.statuses.patient_forms_loading = false;
                    });
            },

            loadAppointments() {
                this.statuses.appointments_loading = true;
                this.$store.dispatch('getPatientAppointments', this.$route.params.id).then(() => {
                    if (this.appointments) {
                        let firstCount = 0;
                        let lastCount = 0;
                        for (let i in this.appointments) {
                            if (this.appointments[i]['i'] == 1) {
                                firstCount = this.appointments[i].appointments.length;
                            } else if (this.appointments[i]['i'] == 2) {
                                lastCount = this.appointments[i].appointments.length;
                            }
                        }

                        this.tabs.appointments.count = firstCount + '/' + lastCount;
                    }
                    this.statuses.appointments_loading = false;
                    this.$emit('updateAppointments')
                });
            },

            loadVisits() {
                this.statuses.visits_loading = true;
                this.$store.dispatch('getPatientVisitCreatedAppointments', this.$route.params.id).then(response => {
                    if (this.visits) {
                        this.tabs.visits.count = this.visits.length;
                    }
                    this.statuses.visits_loading = false;
                });
            },

            loadTransactions() {
                this.statuses.transactions_loading = true;
                (this.isUserAdmin || this.isCollectPaymentAvailable) && this.$store.dispatch('getPatientPreprocessedTransactions', this.$route.params.id).then(() => {
                    this.statuses.transactions_loading = false;
                });
            },

            loadNotifications() {
                this.statuses.notifications_loading = true;
                this.isUserAdmin && this.$store.dispatch('getPatientNotifications', this.$route.params.id).then(() => {
                    this.statuses.notifications_loading = false;
                });
            },

            deleteNote(note) {
                this.$store.dispatch('deleteNote', note.id);
            },

            allowEditingAssessmentForm(form) {
                form.is_editing_allowed.allowed = true;
                form.is_editing_allowed.hours = 72;
                this.$store.dispatch('allowEditingAssessmentForm', form.id);
            },

            allowEditingNote(note) {
                note.is_editing_allowed.allowed = true;
                note.is_editing_allowed.hours = 72;
                this.$store.dispatch('allowEditingNote', note.id);
            },

            allowEditingDocument(note) {
                note.is_editing_allowed.allowed = true;
                note.is_editing_allowed.hours = 72;
                this.$store.dispatch('allowEditingDocument', note.id);
            },

            showPatientTherapistList() {
                $('#patient-therapist-list-modal').modal('show');
            },

            showPatientCreditCardList() {
                $('#patient-credit-card-list-modal').modal('show');
            },

            showConsentInfo() {
                $('#consent-info-modal').modal('show');
            },

            getDocumentType(doc) {
                if (doc.document_type === 'Other') {
                    if (doc.other_document_type) {
                        return doc.other_document_type;
                    } else {
                        return doc.document_type;
                    }
                } else {
                    return doc.document_type;
                }
            },

            saveDocumentType() {
                this.validation_message = "";
                if (this.document_type_id === undefined || this.document_type_id === null) {
                    $('#document_type_id').addClass('input-error');
                    this.validation_message = this.$store.state.validation_messages.required;
                    return false;
                }

                if (this.visit_created_count == 0 && this.discharge_ids.indexOf(parseInt(this.document_type_id)) !== -1) {
                    $('#document_type_id').addClass('input-error');
                    this.validation_message = 'The patient does not have Visit Created appointments.';
                    return false;
                }

                if (parseInt(this.document_type_id) === this.other_type_id && this.other_document_type.trim() === '') {
                    $('#document_other_type input').addClass('input-error');
                    $('#document_other_type label').addClass('label-error');
                    this.validation_message = this.$store.state.validation_messages.required;
                    return false;
                }

                this.statuses.saving_document_type = true;
                let data = {
                    document_id: parseInt(this.document_id),
                    document_type_id: parseInt(this.document_type_id),
                    other_document_type: this.other_document_type,
                    visible_only_for_admin: this.visible_only_for_admin,
                };
                this.visible_only_for_admin = false;
                this.$store.dispatch('setDocumentType', data).then(response => {
                    console.log('resp status ', response.status);
                    if (response.status === 401) {
                        this.has_document_without_type = false;

                        return false;
                    }

                    this.statuses.saving_document_type = false;
                    if (response.status === 200) {
                        const patientId = this.patient.id;
                        this.$store.dispatch('getPatient', {patientId: patientId});
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: patientId});
                        this.$store.dispatch('getProviderTodayPatients');
                        this.$store.dispatch('getSearchedPatients', {query: ''}).then(() => {
//                        window.setTimeout(() => {
//                            new List('search-block', {
//                                valueNames: ['office-ally-id', 'patient-name']
//                            });
//                        }, 500);
                        });

                        this.closeDocumentTypeModal();
                    } else {
                        this.validation_message = this.$store.state.validation_messages.try_again;
                    }
                });
            },

            deleteDocument() {
                if (! this.statuses.saving_document_type) {
                    this.$store.dispatch('deleteDocument', {id: this.document_id});
                }

                this.closeDocumentTypeModal();
            },

            closeDocumentTypeModal() {
                $('#specify-file-type-modal').modal('hide');

                this.document_id = null;
                this.document_type_id = null;
                this.validation_message = '';
                this.other_document_type = '';
                this.has_document_without_type = false;
            },

            getDocumentTypesHtmlTree(doc_types) {
                for (let k in doc_types) {
                    this.getDocumentTypesHtmlTreeRec(doc_types[k], 0);
                }
            },

            getDocumentTypesHtmlTreeRec(doc_types, level) {
                let disabled = !doc_types.clickable ? "class='select-head' disabled" : "";
                let indent = '';
                for (let s = 0; s < level; s++) {
                    indent += '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                if (doc_types.type === 'Other') {
                    this.other_type_id = doc_types.id;
                }
                this.document_types_html += "<option " + disabled + " value='" + doc_types.id + "'>" + indent + doc_types.type + "</option>";
                level++;
                for (let i in doc_types.childs) {
                    this.getDocumentTypesHtmlTreeRec(doc_types.childs[i], level);
                }
            },

            getProviderName(note) {
                let providerName = note.provider_name;
                if (providerName !== null && providerName !== undefined) {
                    return providerName;
                } else if (note.full_admin_name !== null && note.full_admin_name !== undefined) {
                    return note.full_admin_name;
                } else if (note.firstname && note.lastname) {
                    return `${note.firstname} ${note.lastname}`;
                }
                return 'Admin';
            },

            storeComment() {
                this.comment = $('#comment-textarea').html();
                if (this.comment) {
                    this.comment = this.comment.trim();
                }
                if (!this.comment.length) {
                    return false;
                }
                let requestData = {
                    patient_id: parseInt(this.$route.params.id),
                    provider_id: this.provider.id,
                    comment: this.comment
                };
                this.$store.dispatch("storeComment", requestData).then(response => {
                    if (response.status === 201) {
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: requestData.patient_id});
                        this.$store.dispatch('getProviderMessages');
                    }
                });
                this.comment = '';
                $('#comment-textarea').html('');
                this.doScrolling(document.getElementsByClassName('chart-tabs')[0], 500);
            },

            storeDocumentComment(note, payload) {
                if (!payload.length) {
                    return false;
                }
                ;
                let requestData = {
                    provider_id: this.provider.id,
                    content: payload,
                    patient_documents_id: note.id,
                    document_model: note.model,
                };
                this.$store.dispatch("storeDocumentComment", requestData).then(response => {
                    if (response.status === 201) {
                        this.$store.dispatch('getProviderMessages');
                        this.documentCommentError = false;
                        note.document_comments.push({
                            content: response.data.comment.content,
                            created_at: response.data.comment.created_at,
                            firstname: response.data.user ? response.data.user.firstname : null,
                            lastname: response.data.user ? response.data.user.lastname : null,
                            provider_name: response.data.provider ? response.data.provider.provider_name : null,
                        })
                    } else {
                        this.documentCommentError = true;
                    }
                }).catch(error => {
                    this.documentCommentError = true;
                });
                this.documentComment = '';
                this.doScrolling(document.getElementsByClassName('chart-tabs')[0], 500);
            },

            validateSendByEmailForm() {
                let hasErrors = false;
                if (!this.selected_email) {
                    $('#email-to').addClass('with-errors');
                    hasErrors = true;
                }

                if (this.selected_email == -1) {
                    this.documentEmail = this.documentEmail.trim();
                    if (!this.documentEmail.length) {
                        $('#document-email').addClass('with-errors');
                        hasErrors = true;
                    }

                    this.documentPassword = this.documentPassword.trim();
                    if (!this.documentPassword.length) {
                        $('#document-password').addClass('with-errors');
                        hasErrors = true;
                    }
                }

                return !hasErrors;
            },

            sendDocumentByEmail(id) {
                if (!this.validateSendByEmailForm()) {
                    return false;
                }
                this.sending = true;
                this.$store.dispatch('sendDocumentByEmail', {
                    provider_id: this.provider.id,
                    patient_documents_id: id,
                    recipient: this.documentEmail,
                    shared_link_password: this.documentPassword,
                    document_model: this.sendingDocumentModel,
                    method: 'email',
                })
                    .then((response) => {
                        this.hideEmailModal();
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: parseInt(this.$route.params.id)});
                        this.sending = false;
                        if (response.status !== 200) {
                            window.alert('Email could not have been sent due to connection problems. Please try again later.')
                        }
                    })
                    .catch((error) => {
                        if(error.status === 409) {
                            this.$message({
                                type: 'error',
                                message: error.data.error,
                                duration: 10000,
                            });
                        } else {
                            this.hideEmailModal();
                            window.alert('Email could not have been sent due to connection problems. Please try again later.');
                        }
                        this.sending = false;
                    })
            },

            validateSendByFaxForm() {
                let hasErrors = false;
                if (!this.selected_fax) {
                    $('#fax-to').addClass('with-errors');
                    hasErrors = true;
                }

                if (this.selected_fax == -1) {
                    if (this.documentFax.length < 10) {
                        hasErrors = true;
                    }
                }

                return !hasErrors;
            },

            sendDocumentByFax(id) {
                if (!this.validateSendByFaxForm()) {
                    return false;
                }
                this.sending = true;
                this.$store.dispatch('sendDocumentByFax', {
                    provider_id: this.provider.id,
                    patient_documents_id: id,
                    recipient: `+1${this.documentFax}`,
                    document_model: this.sendingDocumentModel,
                    method: 'fax',
                })
                    .then((response) => {
                        console.log(response.status, 'response status');
                        this.hideFaxModal();
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: parseInt(this.$route.params.id)});
                        this.sending = false;
                        if (response.status === 403) {
                            window.alert(response.data);
                        } else if (response.status !== 200) {
                            window.alert('Fax could not have been sent due to connection problems. Please try again later.')
                        }
                    })
                    .catch((error) => {
                        console.log(error.response.status, 'error status');
                        this.hideFaxModal();
                        this.sending = false;
                        if (error.response.status === 403) {
                            window.alert(error.response.data);
                        } else {
                            window.alert('Fax could not have been sent due to connection problems. Please try again later.');
                        }
                        console.log(error);
                    })
            },

            init() {
                this.is_init = true;
                if (this.tabs.appointments.active) {
                    this.statuses.appointments_loading = true;
                }
                if (this.tabs.notifications.active) {
                    this.statuses.notifications_loading = true;
                }
                if (this.tabs.visits.active) {
                    this.statuses.visits_loading = true;
                }
                if (this.tabs.transactions.active) {
                    this.statuses.transactions_loading = true;
                }
                if (this.tabs.patientForms.active) {
                    this.statuses.patient_forms_loading = true;
                }
                
                this.continueInit();
            },

            emitPatientChanged() {
                this.showRemovalRequestButton = false
                this.showCancelRemovalRequestButton = false
                this.$store.dispatch('getActiveRemoveRequests', this.patient.id).then(response => {
                    if(response.data && response.data.length === 0) {
                        this.showRemovalRequestButton = true;
                    } else {
                        this.showCancelRemovalRequestButton = true;
                    }
                });
            },

            continueInit() {
                let id = this.$route.params.id;
                this.scrollTo = this.$route.query.scrollto;
                if (!id) {
                    return false;
                }
                if (this.$route.query.progress_note_id) {
                    this.viewNote(this.$route.query.progress_note_id);
                }
                this.$store.dispatch('isReadOnlyMode', id);
                this.$store.dispatch('getPatient', {patientId: id}).then(response => {
                    if (!response || response.status === 403 || response.status === 404) {
                        return;
                    }

                    let data = response.data;
                    this.emitPatientChanged();
                    this.is_init = true;
                    this.tabs.appointments.count = data.counts.upcoming_appointments + '/' + data.counts.past_appointments;
                    this.tabs.visits.count = data.counts.visits;
                });

                this.$store.dispatch('getPatientFormsCount', {patientId: id}).then(response => {
                    if (!response || response.status === 403 || response.status === 404) {
                        return;
                    }

                    let data = response.data;
                    this.is_init = true;
                    this.tabs.patient_forms_count.count = data.signed_forms_count + '/' + data.submitted_forms_count;
                });

                (this.isUserAdmin || this.isCollectPaymentAvailable) && this.$store.dispatch('getPatientPreprocessedTransactionsCount', {patientId: id}).then(response => {
                    if (!response || response.status === 403 || response.status === 404) {
                        return;
                    }

                    let data = response.data;
                    this.is_init = true;
                    this.tabs.patient_card_transactions.count = data.patient_transaction_count;
                });

                this.isUserAdmin && this.$store.dispatch('getPatientNotifications', this.$route.params.id).then(response => {
                    if (!response || response.status === 403 || response.status === 404) {
                        return;
                    }

                    let data = response.data;
                    this.is_init = true;
                    this.$store.state.patient_notifications_count = data.length;
                    this.tabs.notifications.count = this.$store.state.patient_notifications_count;
                });
               
                this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: id}).then(() => {
                    if (this.scrollTo) {
                        let self = this;
                        this.changeTab('timeline');

                        window.setTimeout(() => {
                            if (self.is_read_only_mode) {
                                self.initTributePlugin();
                            }
                        }, 500);
                    }
                });

                function getCookie(name) {
                    let matches = document.cookie.match(new RegExp(
                        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                    ));
                    return matches ? decodeURIComponent(matches[1]) : undefined;
                }

                let checker = window.setInterval(() => {
                    let downloadCookie = getCookie('document-download');
                    let previewCookie = getCookie('document-preview');
                    if (downloadCookie || previewCookie) {
                        this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: parseInt(this.$route.params.id)});
                    }
                }, 3000);

                if (this.tabs.timeline.active) {
                    $('.zEWidget-launcher, .zEWidget-webWidget').css('bottom', '85px');
                } else {
                    $('.zEWidget-launcher, .zEWidget-webWidget').css('bottom', '0');
                }
            },

            isFileHasPreview(name) {
                let extension = this.getFileExt(name);
                let extensions = [
                    'pdf',
                    'png',
                    'jpg',
                    'jpeg',
                    'gif'
                ];
                return extensions.indexOf(extension) !== -1;
            },

            getFileExt(name) {
                let extension = name.split('.');
                extension = extension[extension.length - 1];
                return extension.toLowerCase();
            },

            getFileIcon(note) {
                if (note.preview) {
//                    return 'data:' + note.mime + ';base64,' + note.preview;
                    return note.preview;
                }

                let name = note.original_document_name;

                let exists_icons = [
                    '7z',
                    'rar',
                    'zip',
                    'word',
                    'excel',
                    'pdf',
                    'image'
                ];
                let extension = this.getFileExt(name);
                switch (extension) {
                    case 'doc':
                    case 'docx':
                        extension = 'word';
                        break;
                    case 'xls':
                    case 'xlsx':
                        extension = 'excel';
                        break;
                    case 'png':
                    case 'jpg':
                    case 'jpeg':
                    case 'gif':
                        extension = 'image';
                        break;
                }
                if (exists_icons.indexOf(extension) !== -1) {
                    this.default_document_previews[note.aws_document_name] = "/images/file_type/" + extension + ".png";
                } else {
                    this.default_document_previews[note.aws_document_name] = "/images/file_type/default.png";
                }
            },

            getFormattedDateTime(date) {
                return this.$moment(date).format('MM/DD/YYYY hh:mm A');
            },

            getFormattedDate(date) {
                return this.$moment(date).format('MM/DD/YYYY');
            },

            downloadDoc(docName) {
                window.open('/patient/download-document/' + docName, '_blank');
            },

            confirmDeletionDocument(doc) {
                this.doc_to_delete = doc;
                $('#confirm-deletion-document').modal('show');
            },

            confirmChangeStatusDialog(doc) {
                this.doc_to_change_status = doc;
                $('#confirm-change-document-status').modal('show');
            },

            cancelChangingDocumentStatus() {
                this.doc_to_change_status = null;
                $('#confirm-change-document-status').modal('hide');
            },

            confirmChangingDocumentStatus() {
                let payload = {
                    document_id: this.doc_to_change_status.id,
                };
                if (this.doc_to_change_status.only_for_admin == 1) {
                    payload.only_for_admin = false;
                } else {
                    payload.only_for_admin = true;
                }

                this.$store.dispatch('changeDocumentStatus', payload).then(() => {
                    this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                    this.cancelChangingDocumentStatus();
                });
            },

            closeConfirmDeleting() {
                $('#confirm-deletion-document').modal('hide');
                this.doc_to_delete = null;
            },
            closeConfirmDeletingComment() {
                $('#confirm-deletion-comment').modal('hide');
                this.comment_to_delete = null;
            },

            deleteCommentConfirmation(comment) {
                this.comment_to_delete = comment;
                $('#confirm-deletion-comment').modal('show');
            },

            deleteComment() {
                if (this.comment_to_delete) {
                    switch (this.comment_to_delete.model) {
                        case 'PatientComment':
                            this.$store.dispatch('deleteComment', this.comment_to_delete.id).then(() => {
                                this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                                this.$store.dispatch('getProviderMessages');
                            });
                            break;
                    }
                    this.closeConfirmDeletingComment();
                }
            },

            deleteDoc() {
                if (this.doc_to_delete) {
                    if (this.doc_to_delete.model === 'PatientElectronicDocument') {
                        const patientId = this.patient.id;
                        this.$store.dispatch('deleteElectronicDocument', this.doc_to_delete.id).then(response => {
                            if (response.statusText === 'OK') {
                                this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: patientId});
                                this.closeConfirmDeleting();
                            }
                        });
                        return;
                    }
                    let type = 'doc';
                    if (this.doc_to_delete.assessment_doc) {
                        type = 'assessment';
                    }
                    if (this.doc_to_delete.model === 'PatientNote') {
                        type = 'PatientNote';
                    }
                    let data = {
                        id: this.doc_to_delete.id,
                        type: type
                    };
                    this.$store.dispatch('softDeleteDocument', data).then(response => {
                        if (response.statusText === 'OK') {
                            const patientId = this.patient.id;
                            this.$store.dispatch('getPatient', {patientId: patientId})
                            this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: patientId});
                            this.closeConfirmDeleting();
                        }
                    });
                }

            },

            previewDoc(docName) {
                window.open('/patient/preview-document/' + docName, '_blank');
            },

            openUploadForm() {
                if (!this.patient) {
                    return;
                }
                
                if (this.uploader) {
                    this.uploader.reset();
                    $('#fine-upload').empty();
                }
                if (!this.is_read_only_mode) {
                    let self = this;
                    let st = this.$store;
                    let patientID = this.patient.id;
                    this.uploader = new qq.FineUploader({
                        element: document.getElementById('fine-upload'),
                        request: {
                            endpoint: '/patient/upload-file',
                            params: {
                                patient_id: this.patient.id
                            }
                        },
                        callbacks: {
//                            onSubmit: function() {
//                                self.$store.dispatch('getPatient', {patientId: patientID}).then(getPatientResponse => {
//                                    if(getPatientResponse.status !== 401) {
//                                        self.$store.dispatch('hasInitialAssessmentForm', self.patient.id).then(response => {
//                                            if(!response || response.data.response === false) {
//                                                self.initial_assessment_forms = response.data.initial_assessment;
//                                            } else {
//                                                self.initial_assessment_forms = null;
//                                                if(response.status === 200) {
//                                                    this.uploadStoredFiles();
//                                                }
//                                            }
//
//                                            if(!self.patient.is_documents_uploading_allowed) {
//                                                $('#pls-add-initial-assessment-before-upload-doc-modal').modal('show');
//                                            }
//                                        });
//                                    }
//                                });
//                            },
                            onValidate: function(file) {
                                return self.validateFileSize(file);
                            },
                            onError: function (id, fileName, response, xhr) {
                                console.error(response, xhr);
                                if (xhr && xhr.status === 401) {
                                    self.has_document_without_type = false;
//                                    window.location.reload();
                                }
                            },
                            onComplete: function (id, fileName, response, xhr) {
                                if (!self.patient.is_documents_uploading_allowed) {
                                    if (!response.success) {
                                        self.initial_assessment_forms = response.initial_assessment;
                                        $('#pls-add-initial-assessment-before-upload-doc-modal').modal('show');
                                    }
                                } else {
                                    self.document_id = response.new_file_id;
                                    self.discharge_ids = response.discharge_ids;
                                    self.visit_created_count = response.visit_created_count;
                                    self.has_document_without_type = true;
                                    self.setFileType();
                                }
                                //                            st.dispatch('getPatientNotesWithDocumentsPaginated', patientID);
                            },

                            onAllComplete: function () {
//                                this.reset();
                            },
                        },
                        thumbnails: {
                            placeholders: {
                                waitingPath: '../plugins/fine-uploader/placeholders/waiting-generic.png',
                                notAvailablePath: '../images/file_type/default.png'
                            }
                        },
                        autoUpload: true,
                        debug: false,
                        multiple: false
                    });
                }
            },

            setFileType() {
                $('#specify-file-type-modal').modal('show');
                return true;
            },

            showEmailModal(note) {
                this.sendingDocumentId = note.id;
                this.sendingDocumentModel = note.model;
//                this.documentEmail = note.default_address.email;
                if (note.default_address && note.default_address.email) {
                    for (let i in this.document_default_emails) {
                        if (this.document_default_emails[i]['email'] === note.default_address.email) {
                            this.selected_email = note.default_address.email;
                        }
                    }
                }

                $('#document-mail-send-modal').modal('show');
                return true;
            },

            hideEmailModal() {
                $('#document-mail-send-modal').modal('hide');
                setTimeout(() => {
                    this.sendingDocumentId = null;
                    this.sendingDocumentModel = null;
                    this.documentEmail = '';
                    this.documentPassword = '';
                    this.selected_email = null;
                    $('#document-email').removeClass('with-errors');
                    $('#document-password').removeClass('with-errors');
                    $('#email-to').removeClass('with-errors');
                }, 500);

                return true;
            },

            showFaxModal(note) {
                this.sendingDocumentId = note.id;
                this.sendingDocumentModel = note.model;
                this.documentFax = note.default_address.fax ? note.default_address.fax : '';
                $('#document-fax-send-modal').modal('show');
                return true;
            },

            hideFaxModal() {
                $('#document-fax-send-modal').modal('hide');
                setTimeout(() => {
                    this.sendingDocumentId = null;
                    this.sendingDocumentModel = null;
                    this.documentFax = '';
                    this.selected_fax = null;
                    if (this.$refs.fax) {
                        this.$refs.fax.lastValue = '';
                        this.$refs.fax.display = '';
                    }
                }, 500);

                return true;
            },

            fullName(patient) {
                let name = patient.first_name + ' ' + patient.last_name;
                if (patient.middle_initial && patient.middle_initial.length) {
                    name += ' ' + patient.middle_initial;
                }
                return name;
            },

            yearsOld(p) {
                if (p.date_of_birth == '' || p.date_of_birth == null) {
                    return '';
                }
                let diff = new Date(Date.now() - new Date(p.date_of_birth).getTime());
                diff = Math.abs(diff.getUTCFullYear() - 1970)
                return diff + ' years old';
            },

            getDraftProgressNotes() {
                return this.$store.dispatch('getDraftProgressNote', {id: this.patient.id})
                    .then((response) => {
                        this.draftProgressNotes = response.data;
                        return response;
                    })
            },

            checkForDraftPN() {
                this.statuses.loading_note_blank = true;
                this.getDraftProgressNotes()
                    .then(() => {
                        if (this.draftProgressNotes.length > 0) {
                            $('#modal-draft-list').modal('show');
                        } else {
                            this.openNoteBlank();
                        }
                    })
                    .finally(() => {
                        this.statuses.loading_note_blank = false;
                    });
            },

            createBlankNoteFromModal() {
                this.closeModal('modal-draft-list');
                this.openNoteBlank();
            },

            viewNoteFromModal(noteId) {
                this.closeModal('modal-draft-list');
                window.setTimeout(() => {
                    this.viewNote(noteId);
                }, 500);
            },

            openNoteBlank(appointment_id) {
                this.statuses.loading_note_blank = true;
                this.$store.dispatch('getCurrentDateAndTime')
                    .then(response => {
                        response = response.data;
                        let first_name = this.patient.first_name;
                        let last_name = this.patient.last_name;
                        let date_of_birth = this.patient.date_of_birth;
                        let patient_id = this.patient.id;
                        let provider_name = this.provider.provider_name;
                        let provider_license_no = this.provider.license_no;
                        let note = {};

                        let payloadStr = "patient_id=" + patient_id;
                        if (appointment_id) {
                            payloadStr += "&appointment_id=" + appointment_id;
                        }
                        // this.$store.dispatch('hasInitialAssessmentForm', patient_id).then(response1 => {
                        // if (response1 && response1.data.response === true) {
                        //   //show PN modal
                        // } else {
                        //   this.initial_assessment_forms = response1.data.initial_assessment;
                        //   $('#pls-add-initial-assessment-modal').modal('show');
                        //   this.statuses.loading_note_blank = false;
                        // }
                        // });
                        this.$store.dispatch('getFirstAppointmentDateHasntNote', payloadStr)
                            .then(responseDate => {
                                if (responseDate.status === 401) {
                                    return false;
                                }
                                if (responseDate.status === 400) {
                                    this.$message({
                                        type: 'error',
                                        message: responseDate.data.message,
                                        duration: 10000,
                                    });
                                    this.$router.push('/chart/' + patient_id);
                                    return false;
                                }
                                responseDate = responseDate.data;
                                this.$store.dispatch('getPreviousNoteData', patient_id)
                                    .then(responseNote => {
                                        note = {
                                            "first_name": first_name,
                                            "last_name": last_name,
                                            "date_of_birth": date_of_birth,
                                            "date_of_service": "",
                                            "provider_name": provider_name,
                                            "patient_id": patient_id,
                                            "id": "",
                                            treatment_modality_id: "",
                                            start_time: response.time,
                                            end_time: response.time,
                                            provider_license_no: provider_license_no,
                                            modal_title: 'Progress Note',
                                            appointment_id: appointment_id,
                                            formatted_dos: responseDate.formatted_dos,
                                            long_range_treatment_goal: responseNote.data.data.long_range_treatment_goal,
                                            shortterm_behavioral_objective: responseNote.data.data.shortterm_behavioral_objective,
                                            diagnoses: responseNote.data.data.diagnoses || [],
                                            diagnoses_editable: ('diagnoses_editable' in responseDate) ? responseDate.diagnoses_editable : true
                                        };
                                        if (appointment_id) {
                                            note['start_time'] = responseDate.start_time;
                                            note['end_time'] = responseDate.end_time;
                                            note['treatment_modality_id'] = responseDate.treatment_modality_id;
                                            note['date_of_service'] = this.getFormattedDate(responseDate.date);
                                        }
                                        this.statuses.loading_note_blank = false;
                                        this.$store.dispatch('refreshCurrentNote', note)
                                            .then(() => {
                                                this.openNoteDialog();
                                                window.setTimeout(() => {
                                                    autosize.update($('textarea[data-autosize=true]'));
                                                }, 300);
                                            });
                                    });
                            });
                    });
            },

            createInitialAssessmentForm(form) {
                $('#pls-add-initial-assessment-modal').modal('hide');
                this.createAssessmentForm(form);
            },

            editAssessmetForm(form) {
                $('#assessment-form-modal').modal('show');
                this.$store.dispatch('getAssessmentFormData', form.id);
            },

            createAssessmentForm(form) {
                this.$store.dispatch('createNewPatientAssessmentForm', {
                    assessment_form_id: form.id,
                    patient_id: this.$store.state.currentPatient.id
                }).then(response => {
                    if (response.status === 200) {
//                        this.$store.commit('setOpenAssessmentFormToModal', form);
                        $('#assessment-form-modal').modal('show');
                    } else {
                        if (response.data && response.data.message) {
                            this.modal_info_msg = response.data.message;
                            $('#modal-info').modal('show');
                        }
                    }
                });
            },

            openNoteDialog() {
                let textareaAutoresize = $('textarea[data-autosize=true]');
                autosize.destroy(textareaAutoresize);
//                $('body').addClass('custom-modal');
                $('#note').modal('show');
                autosize(textareaAutoresize);
                window.onbeforeunload = onbeforeunload;
            },

            viewNote(noteId) {
                this.$store.dispatch('getPatientNote', noteId).then(() => {
                    this.$store.commit('setNoteBlankStatus', false);
                    this.openNoteDialog();
                    this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: parseInt(this.$route.params.id)});
                    window.setTimeout(() => {
                        autosize.update($('textarea[data-autosize=true]'));
                    }, 300);
                });

            },

            exportNote(note_id) {
                window.open('/patient/export-note/' + note_id, '_blank');
            },

            openInitialAssessmentBlank(appointment_id) {
                this.showDocument('cwr-initial-assessment');
                this.$store.state.currentDocumentAppointmentId = appointment_id;
            },

            showDocument(documentId) {
                $('#pls-add-initial-assessment-modal').modal('hide');
                $('#pls-add-initial-assessment-before-upload-doc-modal').modal('hide');
                if ((documentId === this.electronicDocumentsTypes.kp_patient_discharge_summary ||
                    documentId === this.electronicDocumentsTypes.kp_patient_discharge_summary_wh ||
                    documentId === this.electronicDocumentsTypes.kp_patient_discharge_summary_la ||
                    documentId === this.electronicDocumentsTypes.cwr_patient_discharge_summary) && this.tabs.visits.count == 0) {
                    this.modal_info_msg = 'The patient does not have Visit Created appointments.';
                    $('#modal-info').modal('show');
                    return false;
                }

                if (!this.canFillInitialAssessment(documentId)) {
                    let tridiuumUrl = 'https://polestarapp.com/';
                    if (this.patient.tridiuum_patient_id) {
                        tridiuumUrl += 'admin/patients/' + this.patient.tridiuum_patient_id + '/edit';
                    }
                    
                    this.modal_info_msg = 'Initial Assessment for this patient should be filled in the <a href="' + tridiuumUrl + '" target="_blank">Lucet</a>.';
                    $('#modal-info').modal('show');

                    return false;
                }

                this.$store.state.currentDocument = documentId;
                this.$store.state.currentDocumentId = null;
                this.$store.state.currentDocumentData = null;
                this.$store.state.currentDocumentAppointmentId = null;

                setTimeout(function () {
                    let textareaAutoresize = $('textarea[data-autosize=true]');
                    autosize.destroy(textareaAutoresize);
                    $('#' + documentId).modal('show');
                    autosize(textareaAutoresize);
                }, 400);
            },

            canFillInitialAssessment(documentId) {
                let initialAssessmentTypes = [
                    this.electronicDocumentsTypes.cwr_initial_assessment,
                    this.electronicDocumentsTypes.kp_initial_assessment_adult_pc,
                    this.electronicDocumentsTypes.kp_initial_assessment_child_pc,
                    this.electronicDocumentsTypes.kp_initial_assessment_adult_wh,
                    this.electronicDocumentsTypes.kp_initial_assessment_child_wh,
                    this.electronicDocumentsTypes.kp_initial_assessment_child_la,
                    this.electronicDocumentsTypes.kp_initial_assessment_adult_la,
                ];
                
                return !this.patient.tridiuum_patient_id || !initialAssessmentTypes.includes(documentId);
            },

            getElectronicDocument(documentUniqueId, documentId) {
                this.$store.dispatch('getElectronicDocument', {
                    'documentUniqueId': documentUniqueId,
                    'documentId': documentId
                }).then(() => {
                    this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: parseInt(this.$route.params.id)});
                    window.setTimeout(() => {
                        autosize.update($('textarea[data-autosize=true]'));
                    }, 300);
                });
            },

            getElectronicDocumentsInfo(assessmentFormsTemplates) {

                let electronicDocumentsInfo = [];

                for (let templateIndex in assessmentFormsTemplates) {

                    let assessmentFormsTemplate = assessmentFormsTemplates[templateIndex];

                    for (let submenuIndex in assessmentFormsTemplate.childs) {

                        let submenu1 = assessmentFormsTemplate.childs[submenuIndex];


                        if (submenu1.slug) {
                            if (submenu1.group_id == 19) {
                                if (submenu1.ind == 1) {
                                    electronicDocumentsInfo["va-request-for-reauthorization"] = submenu1
                                    this.electronicDocumentsTypes.va_request_for_reauthorization = submenu1.slug;
                                }
                            } else if (submenu1.group_id == 18) {
                                if (submenu1.ind == 4) {
                                    electronicDocumentsInfo["kpep-couples-counseling-referral"] = submenu1;
                                    this.electronicDocumentsTypes.kpep_couples_counseling_referral = submenu1.slug;
                                }
                                else if (submenu1.ind == 5) {
                                    electronicDocumentsInfo["kpep-group-referral"] = submenu1;
                                    this.electronicDocumentsTypes.kpep_group_referral = submenu1.slug;
                                }
                                else if (submenu1.ind == 6) {
                                    electronicDocumentsInfo["kpep-intensive-treatment-referral"] = submenu1;
                                    this.electronicDocumentsTypes.kpep_intensive_treatment_referral = submenu1.slug;
                                }
                                else if (submenu1.ind == 7) {
                                    electronicDocumentsInfo["kpep-medication-evaluation-referral"] = submenu1;
                                    this.electronicDocumentsTypes.kpep_medication_evaluation_referral = submenu1.slug;
                                }
                            } else if (submenu1.group_id == 8) {
                                if (submenu1.ind == 1) {
                                    electronicDocumentsInfo["kp-medication-evaluation-referral"] = submenu1;
                                    this.electronicDocumentsTypes.kp_medication_evaluation_referral = submenu1.slug;
                                } else if (submenu1.ind == 2) {
                                    electronicDocumentsInfo["kp-medication-evaluation-referral-pc"] = submenu1;
                                    this.electronicDocumentsTypes.kp_medication_evaluation_referral_pc = submenu1.slug;
                                } else if (submenu1.ind == 3) {
                                    electronicDocumentsInfo["kp-medication-evaluation-referral-la"] = submenu1;
                                    this.electronicDocumentsTypes.kp_medication_evaluation_referral_la = submenu1.slug;
                                }
                            } else if (submenu1.group_id == 9) {
                                if (submenu1.ind == 1) {
                                    electronicDocumentsInfo["kp-bhios-wh"] = submenu1;
                                    this.electronicDocumentsTypes.kp_bhios_wh = submenu1.slug;
                                }
                            } else if (submenu1.group_id == 10) {
                                if (submenu1.ind == 1) {
                                    electronicDocumentsInfo["kp-ref-for-groups-la"] = submenu1;
                                    this.electronicDocumentsTypes.kp_referral_for_groups_los_angeles = submenu1.slug;
                                }
                            } else if (submenu1.group_id == 11) {
                                if (submenu1.ind == 1) {
                                    electronicDocumentsInfo["kp-ref-to-hloc-la"] = submenu1;
                                    this.electronicDocumentsTypes.kp_hloc_los_angeles = submenu1.slug;
                                }
                            }

                        } else {
                            for (let submenu2Index in submenu1.childs) {

                                let submenu2 = submenu1.childs[submenu2Index];
                                let index = null;

                                if (submenu2.group_id == 1 && submenu2.ind == 1) {

                                    index = 'cwr-initial-assessment';
                                    this.electronicDocumentsTypes.cwr_initial_assessment = submenu2.slug;
                                } else if (submenu2.group_id == 2) {

                                    if (submenu2.ind == 1) {

                                        index = 'kp-initial-assessment-adult-pc';
                                        this.electronicDocumentsTypes.kp_initial_assessment_adult_pc = submenu2.slug;
                                    } else if (submenu2.ind == 2) {

                                        index = 'kp-initial-assessment-child-pc';
                                        this.electronicDocumentsTypes.kp_initial_assessment_child_pc = submenu2.slug;
                                    } else if (submenu2.ind == 3) {

                                        index = 'kp-initial-assessment-adult-wh';
                                        this.electronicDocumentsTypes.kp_initial_assessment_adult_wh = submenu2.slug;
                                    } else if (submenu2.ind == 4) {

                                        index = 'kp-initial-assessment-child-wh';
                                        this.electronicDocumentsTypes.kp_initial_assessment_child_wh = submenu2.slug;
                                    } else if (submenu2.ind == 5) {

                                        index = 'kp-initial-assessment-adult-la';
                                        this.electronicDocumentsTypes.kp_initial_assessment_adult_la = submenu2.slug;
                                    } else if (submenu2.ind == 6) {

                                        index = 'kp-initial-assessment-child-la';
                                        this.electronicDocumentsTypes.kp_initial_assessment_child_la = submenu2.slug;
                                    }
                                } else if (submenu2.group_id == 3) {
                                    if (submenu2.ind == 1) {

                                        index = 'kp-request-for-reauthorization-pc';
                                        this.electronicDocumentsTypes.kp_request_for_reauthorization_pc = submenu2.slug;
                                    } else if (submenu2.ind == 2) {

                                        index = 'kp1-request-for-reauthorization-wh';
                                        this.electronicDocumentsTypes.kp1_request_for_reauthorization_wh = submenu2.slug;
                                    } else if (submenu2.ind == 3) {

                                        index = 'kp2-request-for-reauthorization-wh';
                                        this.electronicDocumentsTypes.kp2_request_for_reauthorization_wh = submenu2.slug;
                                    } else if (submenu2.ind == 4) {
                                        index = 'kp-request-for-reauthorization-la';
                                        this.electronicDocumentsTypes.kp_request_for_reauthorization_la = submenu2.slug;
                                    }
                                } else if (submenu2.group_id == 4) {

                                    index = "cwr-patient-discharge-summary";
                                    this.electronicDocumentsTypes.cwr_patient_discharge_summary = submenu2.slug;
                                } else if (submenu2.group_id == 5) {

                                    if (submenu2.ind == 1) {

                                        index = "kp-patient-discharge-summary";
                                        this.electronicDocumentsTypes.kp_patient_discharge_summary = submenu2.slug;
                                    } else if (submenu2.ind == 2) {

                                        index = "kp-patient-discharge-summary-wh";
                                        this.electronicDocumentsTypes.kp_patient_discharge_summary_wh = submenu2.slug;
                                    } else if (submenu2.ind == 3) {

                                        index = "kp-patient-discharge-summary-la";
                                        this.electronicDocumentsTypes.kp_patient_discharge_summary_la = submenu2.slug;
                                    }

                                } else if (submenu2.group_id == 6) {

                                    if (submenu2.ind == 1) {

                                        index = "axminster-rfr";
                                        this.electronicDocumentsTypes.axminster_rfr = submenu2.slug;
                                    }
                                    if (submenu2.ind == 2) {

                                        index = "facey-rfr";
                                        this.electronicDocumentsTypes.facey_rfr = submenu2.slug;
                                      
                                    }
                                } else if (submenu2.group_id == 7) {

                                    if (submenu2.ind == 1) {

                                        index = "kp-behavioral-health-pc";
                                        this.electronicDocumentsTypes.kp_behavioral_health_pc = submenu2.slug;
                                        
                                    }
                                }
                              
                                electronicDocumentsInfo[index] = submenu2;
                            }
                        }
                    }
                }

                return electronicDocumentsInfo;
            },

            showMoveToArchiveConfirmation() {
                $('#modal-move-to-archive').modal('show');
            },

            closeModal(id) {
                $('#' + id).modal('hide');
            },

            movePatientToArchive() {
                let payload = {
                    patientId: this.patient.id,
                    statusId: this.patient_archive_status.id,
                };
                this.statuses.moving_to_archive = true;
                this.$store.dispatch('updatePatient', payload).then(() => {
                    this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                    this.$store.dispatch('getPatient', {patientId: this.patient.id}).then(() => {
                            this.closeModal('modal-move-to-archive');
                            this.statuses.moving_to_archive = false;
                        }
                    );
                });
            },

            changePatientEmails() {
                this.$refs.emailForm.validate(valid => {
                    if (!valid) {
                        return;
                    }

                    let updates = [];
                    this.statuses.changing_email = true;

                    let mainEmailPayload = {
                        patientId: this.patient.id,
                        email: this.emailFormData.patient_email,
                    };
                    updates.push(this.$store.dispatch('updatePatient', mainEmailPayload));
                    
                    let secondaryEmailPayload = {
                        patient_id: this.patient.id,
                        secondary_email: this.emailFormData.patient_secondary_email,
                    };
                    updates.push(this.$store.dispatch('updatePatientSecondEmail', secondaryEmailPayload));

                    Promise.all(updates).then(() => {
                        this.$store.dispatch('getPatient', {patientId: this.patient.id})
                            .then(() => {
                                this.closeModal('modal-change-email');
                                Notification.success({
                                    title: 'Success',
                                    message: 'Emails successfully updated.',
                                    type: 'success'
                                });
                            })
                            .finally(() => {
                                this.statuses.changing_email = false;
                            });
                    });
                });
            },

            patient_status(status_name) {
                return this.patient_statuses.find(function (status) {
                    return status.status == status_name;
                });
            },

            loadMoreChart() {
                this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {
                    id: this.$route.params.id,
                    page: this.paginationChart.next_page
                });
            },

            doScrolling(element, duration) {
                let navbarHeight = document.getElementById('navbar-header').scrollHeight;
                let startingY = window.pageYOffset;
                let diff = element.offsetTop - startingY - navbarHeight;
                let start;
                let scrollingElement = window.isSafari || (window.isIE || window.isIOS) ? document.querySelector('body') : document.querySelector('html');

                window.requestAnimationFrame(function step(timestamp) {
                    if (!start) start = timestamp;
                    // Elapsed milliseconds since start of scrolling.
                    let time = timestamp - start;
                    // Get percent of completion in range [0, 1].
                    let percent = Math.min(time / duration, 1);

                    scrollingElement.scrollTop = startingY + diff * percent;

                    // Proceed with animation as long as we wanted it to.
                    if (time < duration) {
                        window.requestAnimationFrame(step);
                    }
                })
            },

            openGoogleMeetDialog() {
                this.$store.dispatch('setVideoSessionAppointment', {patient: this.patient, appointment_id: null})
            },

            openCallDialog() {
                this.setCallDialogIsVisible(true);
            },

            setCallDialogIsVisible(value) {
                this.callDialogIsVisible = value;
            },

            openPatientDialog() {
                this.isShowPatientDialog = true;
            },

            closePatientDialog() {
                this.isShowPatientDialog = false;
            },

            openDocumentDownloadDialog() { 
                this.isShowDocumentDownloadDialog = true;
            },

            closeDocumentDownloadDialog() {
                this.isShowDocumentDownloadDialog = false;
            },

            createPatientAddressText(patientAddress) {
                return patientAddress.filter(item => item).join(', ');
            },

            getFormattedCardDetails(card) {
                const formattedYear = moment(card.exp_year, 'YYYY').format('YY');
                const formattedMonth = card.exp_month < 10 ? `0${card.exp_month}` : `${card.exp_month}`;
                const expiredText = this.isCardExpired(card) ? 'Expired' : 'Expires';

                return `**** **** **** ${card.last_four} (${expiredText} ${formattedMonth}/${formattedYear})`;
            },

            isCardExpired(card){
                const currentDate = moment();
                const expiryDate = moment(`${card.exp_year}-${card.exp_month}`, 'YYYY-MM');

                return currentDate.isAfter(expiryDate, 'month');
            },

            listenZipArchivesCreation(userId) {
                window.Echo.private(`zip-archive.${userId}`)
                    .listen('.zip-archive.created', (data) => {
                        if (!data || !data.generated_zip_archive) {
                            return;
                        }

                        const generatedZipArchive = data.generated_zip_archive;
                        const patientIdZipArchive = generatedZipArchive.patient_id;
                        const fileName = generatedZipArchive.zip_file_unique_name;
                    
                        this.$store.dispatch("downloadPatientZipDocuments", {
                            patientId: patientIdZipArchive,
                            fileName: fileName
                        })
                            .then((response) => {
                                const blob = new Blob([response.data], { type: 'application/zip' });
                                const url = window.URL.createObjectURL(blob);
                                const link = document.createElement('a');
                                link.href = url;
                                link.setAttribute('download', fileName);
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            });
                    });
            },

            reloadTabs(tabs) {
                this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});

                if (tabs.includes('visits')) {
                  this.statuses.visits_loading = true;

                  this.$store.dispatch('getPatientVisitCreatedAppointments', this.patient.id)
                      .then(() => {
                        this.statuses.visits_loading = false;
                      });
                }
            },
        },
        created() {
            this.is_init = true;
        },
        mounted() {
            this.$store.dispatch('getUserInfo').then(({ data }) => {
                this.listenZipArchivesCreation(data.user_id);
            });

            this.$store.dispatch('getPatientPreferredLanguageList').then(({ data }) => {
                this.patientLanguageList = data.languages;
            });
            this.$store.dispatch('getAssessmentFormTemplates').then(response => {
                if (response.status === 401) {
                    return;
                }

                this.$store.dispatch('getDocumentTypesTree').then(() => {
                    this.getDocumentTypesHtmlTree(this.document_types);
                });
                this.$store.dispatch('getProvidersDatasetForTribute').then(() => {
                    if (!this.is_read_only_mode) {
                        return;
                    }

                    this.initTributePlugin();
                });

                this.electronicDocumentsInfo = this.getElectronicDocumentsInfo(this.assessmentFormsTemplates);

                this.init();
            });
        },
    }
</script>

<style lang="scss" scoped>
    .new-profile-wrapper {
        .section.section-new-profile {
            .profile-row {
                &-edit-info {
                    display: flex;
                    align-items: center;
                }
            }
        }
    }
</style>

<style scoped>
    .fa-icon{
        margin-top: 1px;
        margin-left: 2px;
    }
    .progress-note-block.inline > * {
        display: inline-block;
    }

    .progress-note-block.inline > h4 {
        color: gray;
    }

    .progress-note-block > * {
        font-size: 12px;
    }

    .progress-note-block > h4 {
        color: gray;
    }

    .progress-note-block > p {
        font-weight: 600;
    }

    .document-name {
        margin-left: 15px;
    }

    .note-loader-container {
        vertical-align: middle;
        text-align: center;
    }

    .modal-dialog {
        position: relative;
    }

    .modal-loader-container {
        background-color: rgba(0, 0, 0, 0.2);
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }

    .modal-loader-container .note-loader {
        display: inline-block;
        max-height: 100px;
        max-width: 100px;
    }

    .note-loader-container .note-loader {
        display: inline-block;
        max-height: 100px;
        max-width: 100px;
    }

    .patient-name {
        margin-bottom: 0;
    }

    .nav-pills.nav-stacked {
        padding-right: 0 !important;
        padding-left: 0;
        margin-top: -5px;
    }

    #save-comment {
        padding: 0;
    }

    #save-comment > button {
        height: 84px;
        border-bottom-left-radius: 0;
        border-top-left-radius: 0;
    }

    #comment-container textarea {
        height: 85px;
    }

    #comment-container {
        margin-top: -5px;
    }

    .tab-content {
        padding-right: 0;
    }

    @media (min-width: 768px) {
        .dropdown-submenu .dropdown-menu {
            right: 100%;
            left: auto;
        }
    }

    .dropdown-menu {
        z-index: 10000;
    }

    .validation-error-msg {
        padding-right: 20px;
    }

    .save-loader {
        max-width: 36px;
        max-height: 36px;
    }

    .patient-document-comments {
        margin-top: 20px;
        border-top: 1px solid #eee;
        padding-left: 66px;
        padding-top: 11px;
    }

    .patient-document-comments .form-group {
        width: 100%;
    }

    .patient-document-comments .form-group input {
        width: calc(100% - 123px);
    }

    .error {
        color: red;
        border-color: red;
    }

    @media (max-width: 1100px) {
        .patient-document-comments .form-group input {
            width: 100%;
        }

        .patient-document-comments .form-group button {
            margin: 10px auto;
        }
    }

    .document-type {
        font-size: 12px;
    }

    select.status-select {
        display: inline-block !important;
        max-width: 100px !important;
        height: auto !important;
        padding: 3px 12px 3px 6px !important;
    }

    ul.patient-assigned-to-list {
        padding-left: 0;
        list-style: none;
        display: inline-block;
        margin-bottom: 0;
    }

    ul.patient-assigned-to-list li {
        display: inline-block;
    }

    .wo-pb {
        padding-bottom: 0 !important;
    }

    #comment-textarea {
        overflow-y: auto;
        height: 85px !important;
    }

    .patient-sync {
        height: 23px;
        width: 23px;
    }

    .patient-info-block-btn {
        width: 178px;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .btn-download-docs{
        margin-top: 9px;
        border-radius: 10px;
        color: #ffffff;
        font-size: 18px;
    }

</style>
