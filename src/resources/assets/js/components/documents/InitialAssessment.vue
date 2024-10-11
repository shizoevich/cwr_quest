<template>
    <div>
        <div class="modal modal-vertical-center fade" id="initial-assessment" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <!--<div class="black-layer" v-if="statuses && statuses.saving">-->
                        <!--<pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>-->
                    <!--</div>-->
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" id="add-patient-progress-note-label">
                            Assessment/Diagnostic Interview
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note" id="form-note" novalidate>

                                <div class="row form-note-row">
                                    <div class="form-group col-lg-12">
                                        <label for="patients_name" class="control-label">Patient’s Name</label>
                                        <input type="text" id="patients_name" class="form-control fix-row">
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-sm-6">
                                        <label for="session_date" class="control-label">Session date</label>
                                        <input type="text" id="session_date" class="form-control fix-row" data-provide="datepicker">
                                    </div>
                                    <div class="form-group form-group-bordered col-sm-6">
                                        <label class="control-label">Session time</label>
                                        <div class="group">
                                            <label class="checkbox-inline wo-pl">
                                                From
                                                <input readonly id="session_time_from" type="text"
                                                       class="form-control form-control-xs text-sm with-checkbox">
                                            </label>

                                            <label class="checkbox-inline wo-pl">
                                                To
                                                <input readonly id="session_time_to" type="text"
                                                       class="form-control form-control-xs text-sm with-checkbox">
                                            </label>
                                        </div>

                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-lg-12">
                                        <label for="patients_name2" class="control-label">Patient’s Name</label>
                                        <input type="text" id="patients_name2" class="form-control fix-row">
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group input-container">
                                        <div class="row">
                                            <div class="col-lg-2 col-sm-3">
                                                <label for="patients_name3" class="control-label input-label">Patient’s Test 1</label>
                                            </div>
                                            <div class="col-lg-10 col-sm-9">
                                                <input type="text" id="patients_name3" class="form-control fix-row input-element">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group input-container">
                                        <div class="row">
                                            <div class="col-sm-3 col-lg-2">
                                                <label for="patients_name4" class="control-label input-label">Patient’s Test 1</label>
                                            </div>
                                            <div class="col-sm-9 col-lg-10">
                                                <input type="text" id="patients_name4" class="form-control fix-row input-element">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-note-row input-row">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="patients_name5" class="control-label input-label">Patient’s Test 2</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" id="patients_name5" class="form-control fix-row">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-note-row input-row">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <label for="patients_name6" class="control-label input-label">Patient’s Test 2</label>
                                            </div>
                                            <div class="col-sm-9">
                                                <input type="text" id="patients_name6" class="form-control fix-row">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-note-row">
                                    <div class="form-group col-lg-12 input-container">
                                        <label for="patients_ability" class="control-label input-label">
                                            Describe Patient’s ability to engage and benefit from psychotherapy
                                        </label>
                                        <textarea data-autosize="true" id="patients_ability"
                                                  class="form-control vertical-resize fix-row input-element"
                                                  rows="5"
                                        >
                                        </textarea>
                                    </div>
                                </div>

                                <div class="form-note-button-block">
                                    <div class="row form-note-row">
                                        <div class="hidden-md hidden-lg col-xs-12 text-center" style="padding-right:0;margin-bottom:15px;">
                                            <!--<span class="text-red validation-error-msg" v-if="statuses.noErrors === false && !statuses.invalid_date_of_service">-->
                                                <!--Please make sure you have filled all the required fields.-->
                                            <!--</span>-->
                                            <!--<span class="text-red validation-error-msg" v-if="statuses.noErrors === false && statuses.invalid_date_of_service">-->
                                                <!--Invalid date format. (Example MM/DD/YYYY)-->
                                            <!--</span>-->
                                        </div>
                                        <div class="col-xs-2 col-md-1" style="padding-left:0;">
                                            <!--<button v-if="!isUserAdmin && currentNote && (!currentNote.is_finalized || currentNote.is_finalized == 0)"-->
                                                    <!--type="button" class="btn btn-danger" @click="confirmClearingNote">-->
                                                <!--Delete-->
                                            <!--</button>-->
                                        </div>

                                        <div class="col-xs-10 col-md-11 text-right" style="padding-right:0;">
                                            <div class="hidden-xs hidden-sm col-md-6" style="padding-right:0;">
                                                <!--<span class="text-red validation-error-msg" v-if="statuses.noErrors === false && !statuses.invalid_date_of_service">-->
                                                    <!--Please make sure you have filled all the required fields.-->
                                                <!--</span>-->
                                                <!--<span class="text-red validation-error-msg" v-if="statuses.noErrors === false && statuses.invalid_date_of_service">-->
                                                    <!--Invalid date format. (Example MM/DD/YYYY)-->
                                                <!--</span>-->
                                            </div>

                                            <div class="col-xs-12 col-md-6" style="padding-right:0;">
                                                <button type="submit" class="btn btn-primary">Finalize this note
                                                </button>
                                                <!--<button type="submit" class="btn btn-primary">Update-->
                                                <!--</button>-->
                                                <button type="button" class="btn btn-default">
                                                    Save and finish later
                                                </button>
                                                <!--<button type="button" class="btn btn-default">-->
                                                    <!--Close-->
                                                <!--</button>-->
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
    export default {
//        data(){
//        },
//
        mounted() {
            let self = this;
            window.setTimeout(() => {
                $('#initial-assessment').on('shown.bs.modal', function() {
                    $('body').addClass('custom-modal');
                    $('input#session_time_from').timepicker().on('focusin', function() {
                        $(this).timepicker('setTime', self.start_time);
                        $(this).timepicker('showWidget');
                    }).on('changeTime.timepicker', function(e) {
//                        self.start_time = e.time.value;
//                        self.statuses.end_time.h = e.time.hours;
//                        self.statuses.end_time.m = e.time.minutes;
//                        self.statuses.end_time.meridian = e.time.meridian;
                    });

                    $('input#session_time_to').timepicker({
                        defaultTime: self.start_time
                    }).on('focusin', function(e) {
                        $(this).timepicker('showWidget');
                    }).on('changeTime.timepicker', function(e) {
//                        self.end_time = e.time.value;
                    });

                    $('#session_date').datepicker({});

                    autosize($('#initial-assessment').find('textarea'));

                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
//
//        computed: {
//        },
//
//        methods: {
//        },
//
//        watch: {
//        }
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
