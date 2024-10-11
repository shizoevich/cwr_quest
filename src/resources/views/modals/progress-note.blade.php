<div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" id="add-patient-progress-note" tabindex="-1"
     role="dialog" aria-labelledby="add-patient-progress-note-label">
    <div class="modal-dialog modal-dialog-note" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="add-patient-progress-note-label">Progress Note</h4>
            </div>
            <div class="modal-body">
                <div class="section section-add-note">
                    <form class="form-note" id="form-note" novalidate>
                        <div class="row form-note-row">
                            <div class="form-group col-md-4">
                                <label class="control-label">Firstname</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Lastname</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Date of Service</label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>

                        <div class="row form-note-row">
                            {{--row 2--}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Provider Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6 fix-row">
                                <label class="control-label">Provider License No.</label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>

                        <div class="row form-note-row">
                            {{--row 3--}}
                            <div class="form-group col-md-4">
                                <label class="control-label">Facility Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Procedure Code</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Diagnosis and ICD code</label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>

                        <div class="row form-note-row">
                            {{--row 4--}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Long range Treatment Goal</label>
                                <textarea class="form-control no-resize" required></textarea>
                            </div>
                            <div class="form-group col-md-6 fix-row">
                                <label class="control-label">Short term Behavioral Objective(s)</label>
                                <textarea class="form-control no-resize" required></textarea>
                            </div>
                        </div>

                        <div class="row form-note-row">
                            {{--row 5--}}
                            <div class="form-group form-group-bordered col-md-12 fix-row">
                                <label class="control-label">Treatment Modality</label>
                                <div class="checkbox">
                                    <div class="checkbox-group checkbox-group-xs">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control">
                                            Pty-30&#8242;
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control">
                                            Pty-45&#8242;
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control">
                                            Pty-60&#8242;
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control">
                                            Family
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control">
                                            Group
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control">Health &amp;
                                            Behavior x <input type="text" class="form-control form-control-xs with-checkbox" maxlength="3"> 15&#8242; units
                                        </label>
                                        <label class="checkbox-inline checkbox-wo-padding">
                                            Session time:
                                            <input type="text" class="form-control form-control-xs with-checkbox" maxlength="3">
                                            min.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row form-note-row">
                            {{--row 6--}}
                            <div class="form-group form-group-bordered col-md-12 fix-row">
                                <label class="control-label">Current Status</label>
                                <div class="checkbox">
                                    <div class="checkbox-group">
                                        <table class="table borderless">
                                            <tbody>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Depression</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Anxiety</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Anger
                                                        outbursts</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Impaired
                                                        reality</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Disorientation
                                                        T PL P</label></td>
                                            </tr>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Withdrawal</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Panic
                                                        prone</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Verbally
                                                        abusive</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Delusions</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Limited
                                                        self expression</label></td>
                                            </tr>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Disturbed
                                                        sleep</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Worrisome
                                                        thinking</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Physically
                                                        abusive</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Hallucinations,
                                                        vls.</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Limited
                                                        memory</label></td>
                                            </tr>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Disturbed
                                                        eating</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Phobic
                                                        avoidance</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Irritable</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Hallucinations,
                                                        aud.</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Limited
                                                        concentration</label></td>
                                            </tr>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Tearfulness</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Agitated</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Disruptive
                                                        vocalizing</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Danger
                                                        to self</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Limited
                                                        judgment</label></td>
                                            </tr>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Hopelessness</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Restless
                                                        tension</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Interpersonal
                                                        conflict</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Danger
                                                        to others</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Limited
                                                        attention</label></td>
                                            </tr>
                                            <tr>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Flat
                                                        affect</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Fearfulness</label>
                                                </td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Emotionally
                                                        labile</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Disordered
                                                        thinking</label></td>
                                                <td><label class="checkbox-inline"><input type="checkbox"
                                                                                          class="checkbox-form-control">Limited
                                                        info processing</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control form-control-xs">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-xs">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-xs">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-xs">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-xs">
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>{{--/.checkbox-group--}}
                                </div>{{--/.checkbox--}}
                            </div>{{--/.form-group.form-group-bordered--}}
                        </div>


                        <div class="row form-note-row">
                            {{--row 7--}}
                            <div class="form-group col-md-12 fix-row">
                                <label class="control-label">Additional Comments</label>
                                <textarea class="form-control no-resize"></textarea>
                            </div>
                        </div>


                        <div class="row form-note-row">
                            {{--row 8--}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Interventions</label>
                                <textarea class="form-control no-resize"></textarea>
                            </div>
                            <div class="form-group col-md-6 fix-row">
                                <label class="control-label">Progress and Outcome</label>
                                <textarea class="form-control no-resize"></textarea>
                            </div>
                        </div>
                        <div class="form-note-button-block text-right">
                            <div class="row form-note-row">
                                <div class="col md-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <button type="button" id="add-progress-note-modal-close" class="btn btn-default">
                                        Close
                                    </button>
                                </div>


                            </div>
                        </div>


                    </form>
                </div>


            </div>
        </div>
    </div>
</div>