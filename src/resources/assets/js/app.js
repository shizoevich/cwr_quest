
require('./bootstrap');
require('intersection-observer');
// import 'es6-promise/auto';

import Vue from 'vue';
import Vuex from 'vuex';
import VeeValidate from 'vee-validate';
import store from './store';
import moment from "moment-timezone";
import VueMomentJS from "vue-momentjs";
import VueRouter from "vue-router";
import routes from "./routes";
import VueTheMask from 'vue-the-mask';
import fullCalendar from 'vue-fullcalendar';
import 'nodep-date-input-polyfill';
import VueScrollTo from 'vue-scrollto';
import Datepicker from 'vuejs-datepicker';
import DevelopMode from './mixins/develop_mode';
import {
    DatePicker,
    Select,
    Option,
    Pagination,
    Button,
    Link,
    Collapse,
    CollapseItem,
    Tabs,
    TabPane,
    Message,
    InputNumber,
    Loading,
    Table,
    TableColumn,
    Checkbox,
    CheckboxGroup,
    MessageBox,
    Alert,
    Col,
    Dialog,
    Form,
    FormItem,
    Input,
    RadioGroup,
    Radio,
    TimeSelect,
    Tooltip,
    Popover,
    Popconfirm,
    TimePicker,
    Dropdown,
    DropdownItem, 
    DropdownMenu, 
    Cascader,
    Spinner,
} from 'element-ui';
import VueObserveVisibility from 'vue-observe-visibility';

import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';

// configure language
locale.use(lang);

moment.tz.setDefault("America/Los_Angeles");
Vue.use(VueMomentJS, moment);
Vue.use(VueTheMask);
Vue.use(Vuex);
Vue.use(VueRouter);
// Vue.use(VeeValidate, {
//   events: 'input|blur',
// });
const config = {
    errorBagName: 'errors',
    fieldsBagName: 'fieldBags'
};
Vue.use(VeeValidate, config);

Vue.use(VueScrollTo);
Vue.use(VueObserveVisibility);
Vue.use(Loading.directive);

Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$message = Message;
Vue.prototype.$alert = MessageBox.alert;

export const eventBus = new Vue()

const router = new VueRouter({
    mode: 'history',
    routes: routes,
    linkActiveClass: 'active1'
});

router.beforeEach((to, from, next) => {
    let zendeskWidgetOffset = '0';
    if(to.name === 'patient-chart') {
        zendeskWidgetOffset = '85px';
    }
    try {
        if(typeof zE === 'function') {
            zE('webWidget', 'updateSettings', {
                webWidget: {
                    zIndex: 161,
                    offset: {
                        vertical: zendeskWidgetOffset,
                        mobile: {
                            vertical: zendeskWidgetOffset
                        }
                    }
                }
            });
        }
    } catch(e) {
        console.error(e);
    }


    if(typeof from.query.develop_mode !== 'undefined' && typeof to.query.develop_mode === 'undefined') {
        let newPath = to.path;
        newPath += '?develop_mode=' + (from.query.develop_mode ? 'true' : 'false');
        next(newPath);
    } else {
        next();
    }
});

// Add a 401 response interceptor
window.axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {

    if (error.response && 401 === error.response.status) {
        window.location.href = window.location.href;
    }

    return Promise.reject(error);
});

$.ajaxSetup({
    statusCode: {
        401: function(){
            window.location.href = window.location.href;
        }
    }
});

Vue.component('collect-payment-button', require('./components/CollectPaymentButton.vue'));
Vue.component('chart', require('./components/Chart.vue'));
Vue.component('page-404', require('./components/PageNotFound.vue'));
Vue.component('note', require('./components/Note.vue'));
Vue.component('assessment-form-modal', require('./components/forms/AssessmentFormModal.vue'));
Vue.component('sidebar', require('./components/Sidebar.vue'));
Vue.component('main-view', require('./components/MainView.vue'));
Vue.component('pageloader', require('./components/Pageloader.vue'));
Vue.component('clock', require('./components/Clock.vue'));
Vue.component('patients-statistics-visual-diagrams', require('./components/dashboard/PatientsStatisticVisualDiagramsTab.vue'));
Vue.component('doctors-dropdown', require('./components/dashboard/DoctorsDropdown.vue'));
Vue.component('patient-diagram-status-checkboxes', require('./components/dashboard/PatientDiagramStatusCheckboxes.vue'));
Vue.component('patient-status-checkboxes', require('./components/dashboard/PatientStatusCheckboxes.vue'));
Vue.component('comment-form', require('./components/comments/CommentForm.vue'));
Vue.component('comment-block', require('./components/comments/CommentBlock.vue'));
Vue.component('chart-timeline', require('./components/chart/Timeline.vue'));
Vue.component('doctors-availability', require('./components/DoctorsAvailability.vue'));
Vue.component('appointments', require('./components/Appointments.vue'));
Vue.component('patient-transactions', require('./components/PatientTransactions.vue'));
Vue.component('visits', require('./components/Visits.vue'));
Vue.component('notifications', require('./components/Notifications.vue')); 
Vue.component('doctors-availability-alert', require('./components/alerts/Alert.vue'));
Vue.component('patient-forms-tab', require('./components/chart/PatientForms.vue'));
Vue.component('complete-appointment', require('./components/appointments/CompleteAppointment.vue'));
Vue.component('reschedule-appointment', require('./components/appointments/RescheduleAppointment.vue'));
Vue.component('cancel-appointment', require('./components/appointments/CancelAppointment.vue'));
Vue.component('system-messages', require('./components/dashboard/SystemMessages.vue'));
Vue.component('change-password-modal', require('./components/profile/ChangePasswordModal.vue'));
Vue.component('sent-documents-emails-tab', require('./components/dashboard/SentDocumentsEmailsTab.vue'));
Vue.component('sent-documents-faxes-tab', require('./components/dashboard/SentDocumentsFaxesTab.vue'));
Vue.component('date-range-filter', require('./components/DateRangeFilter.vue'));
Vue.component('salary-filters', require('./components/SalaryFilters.vue'));
Vue.component('salary-management', require('./components/SalaryManagement.vue'));
Vue.component('provider-comments', require('./components/providers/ProviderComments.vue'));

// @todo fix components import for new version of laravel mix
Vue.component('provider-availability-filters', require('./components/dashboard/provider-availability-components/ProviderAvailabilityFilters.vue'));
Vue.component('provider-availability-detail', require('./components/dashboard/provider-availability-components/ProviderAvailabilityDetail.vue'));
Vue.component('help', require('./components/Help.vue'));
// end todo

Vue.component('datepicker', Datepicker);
Vue.component(DatePicker.name, DatePicker);
Vue.component(TimePicker.name, TimePicker);
Vue.component(Dropdown.name, Dropdown);
Vue.component(DropdownItem.name, DropdownItem);
Vue.component(DropdownMenu.name, DropdownMenu);
Vue.component(Select.name, Select);
Vue.component(Option.name, Option);
Vue.component(Pagination.name, Pagination);
Vue.component(Collapse.name, Collapse);
Vue.component(CollapseItem.name, CollapseItem);
Vue.component(Button.name, Button);
Vue.component(Link.name, Link);
Vue.component(Tabs.name, Tabs);
Vue.component(TabPane.name, TabPane);
Vue.component(Select.name, Select);
Vue.component(Option.name, Option);
Vue.component(InputNumber.name, InputNumber);
Vue.component(Col.name, Col);
Vue.component(Dialog.name, Dialog);
Vue.component(Form.name, Form);
Vue.component(FormItem.name, FormItem);
Vue.component(Input.name, Input);
Vue.component(RadioGroup.name, RadioGroup);
Vue.component(Radio.name, Radio);
Vue.component(TimeSelect.name, TimeSelect);
Vue.component(Table.name, Table);
Vue.component(TableColumn.name, TableColumn);
Vue.component(Checkbox.name, Checkbox);
Vue.component(CheckboxGroup.name, CheckboxGroup);
Vue.component(Tooltip.name, Tooltip);
Vue.component(Popover.name, Popover);
Vue.component(Popconfirm.name, Popconfirm);
Vue.component(Alert.name, Alert);
Vue.component(Cascader.name, Cascader);
Vue.component(Spinner.name, Spinner);
Vue.component('dashboard', require('./components/dashboard/Dashboard.vue'));
Vue.component('dashboard-reauthorization-requests', require('./components/dashboard/components/ReauthorizationRequests.vue'));
Vue.component('dashboard-missing-notes', require('./components/dashboard/components/MissingNotes.vue'));
Vue.component('dashboard-missing-initial-assessments', require('./components/dashboard/components/MissingInitialAssessments.vue'));
Vue.component('dashboard-copay', require('./components/dashboard/components/CoPay.vue'));
Vue.component('dashboard-assigned-patients', require('./components/dashboard/components/AssignedPatients.vue'));
Vue.component('dashboard-inactive-patients', require('./components/dashboard/components/InactivePatients.vue'));
Vue.component('dashboard-visits-chart', require('./components/dashboard/components/VisitsChart.vue'));
Vue.component('documents-to-send-base-template', require('./components/dashboard/documents-to-send-components/BaseTemplate.vue'));
Vue.component('salary-sync', require('./components/dashboard/salary-components/Synchronization.vue'));
Vue.component('completed-appointments', require('./components/dashboard/CompletedAppointments.vue'));
Vue.component('payment-posting', require('./components/dashboard/PaymentPosting.vue'));
Vue.component('initial-assessment', require('./components/documents/InitialAssessment.vue'));
Vue.component('kp-initial-assessment-adult-pc', require('./components/documents/kaiser/KpInitialAssessmentAdultPc.vue'));
Vue.component('kp-initial-assessment-child-pc', require('./components/documents/kaiser/KpInitialAssessmentChildPc.vue'));
Vue.component('kp-initial-assessment-adult-wh', require('./components/documents/kaiser/KpInitialAssessmentAdultWh.vue'));
Vue.component('kp-initial-assessment-child-wh', require('./components/documents/kaiser/KpInitialAssessmentChildWh.vue'));
Vue.component('kp-initial-assessment-child-la', require('./components/documents/kaiser/KpInitialAssessmentChildLa.vue'));
Vue.component('kp-initial-assessment-adult-la', require('./components/documents/kaiser/KpInitialAssessmentAdultLa.vue'));
Vue.component('cwr-initial-assessment', require('./components/documents/cwr/CwrInitialAssessment.vue'));
Vue.component('cwr-patient-discharge-summary', require('./components/documents/cwr/CwrPatientDischargeSummary.vue'));
Vue.component('va-request-for-reauthorization', require('./components/documents/reauthorization-requests/VaRequestForReauthorization.vue'));
Vue.component('kp-request-for-reauthorization-pc', require('./components/documents/kaiser/KpRequestForReauthorizationPc.vue'));
Vue.component('kp1-request-for-reauthorization-wh', require('./components/documents/kaiser/Kp1RequestForReauthorizationWh.vue'));
Vue.component('kp2-request-for-reauthorization-wh', require('./components/documents/kaiser/Kp2RequestForReauthorizationWh.vue'));
Vue.component('kp-request-for-reauthorization-la', require('./components/documents/kaiser/KpRequestForReauthorizationLa.vue'));
Vue.component('kp-patient-discharge-summary', require('./components/documents/kaiser/KpPatientDischargeSummary.vue'));
Vue.component('kp-patient-discharge-summary-wh', require('./components/documents/kaiser/KpPatientDischargeSummaryWH.vue'));
Vue.component('kp-patient-discharge-summary-la', require('./components/documents/kaiser/KpPatientDischargeSummaryLA.vue'));
Vue.component('axminster-rfr', require('./components/documents/rfr/AxminsterRFR.vue'));
Vue.component('facey-rfr', require('./components/documents/rfr/FaceyRFR.vue'));
Vue.component('kp-behavioral-health-pc', require('./components/documents/kaiser/KpBehavioralHealthPc.vue'));
Vue.component('kp-medication-evaluation-referral', require('./components/documents/kaiser/KpMedicationEvaluationReferral'));
Vue.component('kp-medication-evaluation-referral-pc', require('./components/documents/kaiser/KpMedicationEvaluationReferralPc'));
Vue.component('kp-medication-evaluation-referral-la', require('./components/documents/kaiser/KpMedicationEvaluationReferralLa'));


Vue.component('kpep-couples-counseling-referral', require('./components/documents/kaiser/KPEPKaiserPermanenteCouplesCounselingReferral'));
Vue.component('kpep-group-referral', require('./components/documents/kaiser/KPEPKaiserPermanenteGroupReferral'));
Vue.component('kpep-intensive-treatment-referral', require('./components/documents/kaiser/KPEPKaiserPermanenteIntensiveTreatmentReferral'));
Vue.component('kpep-medication-evaluation-referral', require('./components/documents/kaiser/KPEPKaiserPermanenteMedicationConsultationReferral'));





Vue.component('kp-ref-for-groups-la', require('./components/documents/kaiser/KpReferralForGroupsLa'));
Vue.component('kp-ref-to-hloc-la', require('./components/documents/kaiser/KpHlocLa'));
Vue.component('kp-bhios-wh', require('./components/documents/kaiser/KpBhiosWh'));
Vue.component('document-input', require('./components/documents/partials/DocumentInput.vue'));
Vue.component('document-textarea', require('./components/documents/partials/DocumentTextarea.vue'));
Vue.component('document-select', require('./components/documents/partials/DocumentSelect.vue'));
Vue.component('date-of-service', require('./components/documents/partials/DateOfService.vue'));
Vue.component('document-confirm-dialog', require('./components/documents/partials/DocumentCloseModal.vue'));
Vue.component('send-removal-request', require('./components/SendRemovalRequest.vue'));
Vue.component('provider-cancel-removal-request', require('./components/CancelRemovalRequest.vue'));
Vue.component('cancel-remove-request', require('./components/dashboard/CancelRemoveRequest.vue'));
Vue.component('cancel-unlock-request', require('./components/dashboard/CancelUnlockRequest.vue'));
Vue.component('attach-customer-to-patient', require('./components/dashboard/square/AttachCustomerButton.vue'));
Vue.component('square-customers', require('./components/dashboard/square/Customers.vue'));
Vue.component('kaiser-appointments', require('./components/dashboard/KaiserAppointments.vue'));
Vue.component('patient-form-confidential', require('./components/providers/ConfidentialInformation.vue'));
Vue.component('confirm-telehealth', require('./components/appointments/ConfirmTelehealth.vue'));
Vue.component('details-table', require('./components/dashboard/salary-components/Detail'));
Vue.component('form-datepicker', require('./components/dashboard/partials/DatePicker.vue'));
Vue.component('no-past-appointments', require('./components/appointments/NoPastAppointments.vue'));
Vue.component('start-video-session', require('./components/StartVideoSession'));
Vue.component('diagnoses-multiselect', require('./components/DiagnosesMultiselect'));
Vue.component('insurance-configuration', require('./components/dashboard/insurance/InsuranceConfiguration'));
Vue.component('square-payment-form-optimized', require('./components/forms/partials/SquarePaymentFormOptimized.vue'));
Vue.component('patient-phone', require('./components/chart/PatientPhone'));
Vue.component('patients-language', require('./components/profile/LanguageSelectComponent'));
Vue.component('patients-language-unknown', require('./components/profile/LanguageSelectWhenUnknownComponent'));
Vue.component('ChooseTelehealthSessionChannelModal', require('./components/ChooseTelehealthSessionChannelModal'));

Vue.component('full-calendar', fullCalendar);
Vue.component('modal-week-confirmation', require('./components/ConfirmationModal.vue'));
Vue.component('timesheet-confirmation', require('./components/TimesheetConfirmationModal.vue'));
Vue.component('modal-appointment-notification', require('./components/AppointmentNonficationModal.vue'));
Vue.component('modal-update-notification', require('./components/UpdateNonficationModal.vue'));
Vue.component('patient-forms-collapse', require('./components/PatientFormsCollapse'));
Vue.component('table-logs', require('./components/TableLogs'));
Vue.component('createAppointmentModal', require('./components/appointments/CreateAppointmentModal'));

Vue.component('ehr-appointments', require('./components/dashboard/EHRAppointments.vue'));

Vue.component('ehr-therapists', require('./components/dashboard/therapists/EHRTherapists.vue'));

Vue.component('help', require('./components/Help.vue'));

Vue.component('are-you-still-here-modal', require('./components/AreYouStillHereModal.vue'));
Vue.component('email-unsubscribed-dialog', require('./components/EmailUnsubscribedDialog.vue'));

Vue.component('insurance-authorization-number', require('./components/InsuranceAuthorizationNumber.vue'));

Vue.component('navbar', require('./components/navbar/Navbar.vue'));

Vue.component('call-patient-dialog', require('./components/dialogs/CallPatientDialog.vue'));
Vue.component('call-logs-table', require('./components/CallLogsTable.vue'));
Vue.component('call-result-dialog', require('./components/dialogs/CallResultDialog.vue'));

Vue.component('loading-overlay', require('./components/LoadingOverlay.vue'));

Vue.component('provider-profile-checkbox-group', require('./components/provider-profile/ProviderProfileCheckboxGroup.vue'));

Vue.component('send-sms-to-change-signature-modal', require('./components/SendSmsToChangeSignatureModal.vue'));

const app = new Vue({
    el: '#app',
    store,
    router,
    mixins: [DevelopMode]
});

$(document).ready(function () {

    $(window).resize(function () {
        setSidebarWidth();
    });

    setSidebarWidth();

    function setSidebarWidth() {
        var minHeight = 'auto';

        if (window.innerWidth > 768) {
            minHeight = $("#page-content-wrapper").height() + 'px'
        }

        // $("#page-sidebar").css('min-height', minHeight);

    }

    $('body')
        .on('focusin', '.form-note .form-control, .form-note .dropdown-form-control, .document-diagnoses-multiselect .multiselect__input', function (e) {
            $(this).parents('.form-group').addClass('focus');
        })
        .on('focusout', '.form-note .form-control, .form-note .dropdown-form-control, .document-diagnoses-multiselect .multiselect__input', function (e) {
            $(this).parents('.form-group').removeClass('focus')
            var elementVal = $(this).val();
            if (elementVal && elementVal.trim() !== '') {
                $(this).parents('.form-group').removeClass('error-focus')
            }
        })
        .on('change', '.form-note .checkbox-form-control', function() {
            if($(this).prop('checked') && !$(this).hasClass('disorientation') && !$('.disorientation').prop('checked')) {
                $(this).parents('.form-group').removeClass('error-focus');
            } else {
                let checkedCount = $('.form-note-row div.checkbox-group[data-required="one"] input[type=checkbox]:checked').length;
                let el = $('.form-note-row div.checkbox-group[data-required="one"]');
                console.log(checkedCount);
                if (checkedCount === 0) {
                    $(el).parents('.form-group').removeClass('focus');
                    $(el).parents('.form-group').addClass('error-focus');
                    console.log('error focus added');
                } else {
                    let disorientation = $(el).find('.disorientation');
                    if($(disorientation).prop('checked')) {
                        let ds = $(el).find('.disorientation-status input[type=radio]:checked').length;
                        if(ds === 0) {
                            $(el).parents('.form-group').addClass('error-focus');
                        } else {
                            $(el).parents('.form-group').removeClass('error-focus');
                        }
                    } else {
                        $(el).parents('.form-group').removeClass('error-focus');
                    }
                }
            }

        })
        .on('change', '.checkbox-document-form-control', function(){
            $(this).parents('.form-group').removeClass('error-focus');
        });


    // $('#add-progress-note-modal-close').click(function () {
    //     $('#confirm-closing-modal').modal('show');
    // });

    $('.form-note textarea.form-control').scroll(function() {
        if($(this).scrollTop() > 8) {
            $(this).prev().hide();
        } else {
            $(this).prev().show();
        }

    });

    $('#sync-data').click(function() {
        let data = {
            _token: $('input[name=_token]').val()
        };
        $.ajax({
            type: 'post',
            cache: false,
            url: '/dashboard/parser/run',
            data: data,
            beforeSend: function() {
                $('#sync-btn-container').hide();
                $('#parsing-status-container').show();
            },
            success: function(response) {
                console.log(response);
            },
            error: function(response) {
                console.error(response);
            }
        });
    });

    $('form[data-validator=true]').parsley();

    $('#auth-form').submit(function() {
        console.log(this);
        $(this).find('button[type=submit], input[type=submit]').prop('disabled', true);
    });

    $('[data-submenu]').submenupicker();
});

require('./user-roles');
window.isIE = navigator.userAgent.match(/(MSIE|Trident|Edge)/);
window.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

Vue.filter('formatPhone', function (value) {
    if(value) {
        return value.replace(/(\d{3})(\d{3})(\d{4})/, '($1)-$2-$3');
    }

    return '';
})
