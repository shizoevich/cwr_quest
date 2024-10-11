<template>
    <div>
        <div
            class="modal fade"
            id="calendar-work-time-add-form-modal"
            data-backdrop="static"
            data-keyboard="false"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Select availability

                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                                @click="closeModal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h5>
                    </div>
                    <div class="modal-body" style="padding-bottom:0;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('date'),
                                    }">
                                    <label for="date">Day:</label>
                                    <select
                                        type="input"
                                        name="date"
                                        id="on"
                                        class="form-control"
                                        :value="on"
                                        v-model="on"
                                        disabled>
                                        <option
                                            value="0"
                                            :selected="isSelectedDay(0)">
                                            Monday
                                        </option>
                                        <option
                                            value="1"
                                            :selected="isSelectedDay(1)">
                                            Tuesday
                                        </option>
                                        <option
                                            value="2"
                                            :selected="isSelectedDay(2)">
                                            Wednesday
                                        </option>
                                        <option
                                            value="3"
                                            :selected="isSelectedDay(3)">
                                            Thursday
                                        </option>
                                        <option
                                            value="4"
                                            :selected="isSelectedDay(4)">
                                            Friday
                                        </option>
                                        <option
                                            value="5"
                                            :selected="isSelectedDay(5)">
                                            Saturday
                                        </option>
                                        <option
                                            value="6"
                                            :selected="isSelectedDay(6)">
                                            Sunday
                                        </option>
                                    </select>
                                    <span
                                        v-show="errors.has('date')"
                                        class="help-block has-error"
                                    >
                                        {{ errors.first("on") }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('time'),
                                    }">
                                    <label for="time">Start time:</label>
                                    <select
                                        :value="at"
                                        name="time"
                                        class="form-control"
                                        id="start_time"
                                        placeholder="At"
                                        @change="(e) => handleStartTimeChange(e.target.value)"
                                    >
                                        <option
                                            v-for="hour in hours"
                                            :value="hour.t"
                                        >
                                            {{ hour.l }}
                                        </option>
                                    </select>
                                    <span
                                        v-show="errors.has('time')"
                                        class="help-block has-error"
                                    >
                                        {{ errors.first("time") }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group position-relative" :class="{ 'has-error': errors.has('length') }">
                                    <label for="length">Number of hours</label>
                                    <select
                                        class="form-control"
                                        name="length"
                                        id="length"
                                        placeholder="Length"
                                        v-model="length"
                                        :disabled="loadingMaxTimeEvent"
                                        @change="errors.remove('length')"
                                    >
                                        <option
                                            v-for="item in totalHoursDropdown"
                                            :key="item.val"
                                            :value="item.val"
                                        >
                                            {{ item.label }}
                                        </option>
                                    </select>
                                    <div v-if="loadingMaxTimeEvent" class="max-time-event-loader-wrapper">
                                        <pageloader />
                                    </div>
                                    <span
                                        v-show="errors.has('length')"
                                        class="help-block has-error"
                                    >
                                        {{ errors.first("length") }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div
                                    class="form-group"
                                    :class="{ 'has-error': errors.has('type') }"
                                    style="margin-top: 31px; margin-bottom: 0"
                                >
                                    <label class="control-label">
                                        <input type="checkbox" v-model="in_person" />
                                        <i class="fa fa-user"></i> In Person
                                    </label>
                                    <label
                                        class="control-label"
                                        style="margin-left: 25px">
                                        <input type="checkbox" v-model="virtual" />
                                        <i class="fa fa-video-camera"></i>
                                        Virtual
                                    </label>
                                    <br />
                                    <!-- <span v-show="errors.has('type')" class="help-block has-error">{{ errors.first('type') }}</span>-->
                                </div>
                            </div>
                        </div>

                        <div class="row has-border-top">
                            <div class="col-sm-12">
                                <div
                                    class="form-group"
                                    :class="{ 'has-error': errors.has('availability_type') }"
                                >
                                    <label class="control-label" style="margin-bottom:0;">
                                        Do you have special instructions?
                                    </label>

                                    <label
                                        v-for="type in availabilityTypes.slice().reverse()"
                                        :key="type.id"
                                        class="radio availability-type-radio"
                                    >
                                        <input type="radio" :value="type.id" v-model="availability_type_id">
                                        {{ type.input_label }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div
                                    v-if="isAdditionalAvailabilityTypeId(availability_type_id)"
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('availability_subtype'),
                                    }"
                                >
                                    <label
                                        for="availability-subtype-select" 
                                        class="control-label" 
                                        id="availability-subtype-label"
                                    >
                                        Instructions
                                    </label>
                                    <select
                                        class="form-control"
                                        name="availability_subtype"
                                        id="availability-subtype-select"
                                        v-model="availability_subtype_id"
                                        v-validate="'required'"
                                    >
                                        <option
                                            v-for="subtype in availabilitySubtypes"
                                            :value="subtype.id"
                                            :key="subtype.id"
                                        >
                                            {{ subtype.type }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-sm-12" v-if="isOtherAvailabilitySubtypeId(availability_subtype_id) || isUnavailableAvailabilitySubtypeId(availability_subtype_id)">
                                <div
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('comment'),
                                    }"
                                >
                                    <label for="comment" class="control-label">Comment</label>
                                    <textarea
                                        id="comment"
                                        placeholder="Type here..."
                                        class="form-control no-resize"
                                        v-model="comment"
                                        style="height: 114px"
                                        v-validate="isOtherAvailabilitySubtypeId(availability_subtype_id) ? 'required' : ''"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            :disabled="loadingMaxTimeEvent"
                            type="button"
                            class="btn btn-primary"
                            @click="saveWorkHour">
                            Save
                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal"
                            @click="closeModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="modal fade"
            id="calendar-work-time-update-form-modal"
            data-backdrop="static"
            data-keyboard="false"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Update availability

                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                                @click="closeModal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h5>
                    </div>
                    <div class="modal-body" style="padding-bottom:0;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('date'),
                                    }">
                                    <label for="date">Day:</label>
                                    <select
                                        type="input"
                                        name="date"
                                        id="on"
                                        class="form-control"
                                        :value="on"
                                        v-model="on"
                                        disabled>
                                        <option
                                            value="0"
                                            :selected="isSelectedDay(0)">
                                            Monday
                                        </option>
                                        <option
                                            value="1"
                                            :selected="isSelectedDay(1)">
                                            Tuesday
                                        </option>
                                        <option
                                            value="2"
                                            :selected="isSelectedDay(2)">
                                            Wednesday
                                        </option>
                                        <option
                                            value="3"
                                            :selected="isSelectedDay(3)">
                                            Thursday
                                        </option>
                                        <option
                                            value="4"
                                            :selected="isSelectedDay(4)">
                                            Friday
                                        </option>
                                        <option
                                            value="5"
                                            :selected="isSelectedDay(5)">
                                            Saturday
                                        </option>
                                        <option
                                            value="6"
                                            :selected="isSelectedDay(6)">
                                            Sunday
                                        </option>
                                    </select>
                                    <span
                                        v-show="errors.has('date')"
                                        class="help-block has-error"
                                    >
                                        {{ errors.first("on") }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('time'),
                                    }">
                                    <label for="time">Start time:</label>
                                    <select
                                        :value="at"
                                        name="time"
                                        class="form-control"
                                        id="start_time"
                                        placeholder="At"
                                        @change="(e) => handleStartTimeChange(e.target.value)"
                                    >
                                        <option
                                            v-for="hour in hours"
                                            :value="hour.t"
                                        >
                                            {{ hour.l }}
                                        </option>
                                    </select>
                                    <span
                                        v-show="errors.has('time')"
                                        class="help-block has-error"
                                    >
                                        {{ errors.first("time") }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group position-relative" :class="{ 'has-error': errors.has('length') }">
                                    <label for="length">Number of hours</label>
                                    <select
                                        class="form-control"
                                        name="length"
                                        id="length"
                                        placeholder="Length"
                                        v-model="length"
                                        :disabled="loadingMaxTimeEvent"
                                        @change="errors.remove('length')"
                                    >
                                        <option
                                            v-for="item in totalHoursDropdown"
                                            :key="item.val"
                                            :value="item.val"
                                        >
                                            {{ item.label }}
                                        </option>
                                    </select>
                                    <div v-if="loadingMaxTimeEvent" class="max-time-event-loader-wrapper">
                                        <pageloader />
                                    </div>
                                    <span
                                        v-show="errors.has('length')"
                                        class="help-block has-error"
                                    >
                                        {{ errors.first("length") }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div
                                    class="form-group"
                                    :class="{ 'has-error': errors.has('type') }"
                                    style="margin-top: 31px; margin-bottom: 0"
                                >
                                    <label class="control-label">
                                        <input
                                            type="checkbox"
                                            v-model="in_person" />
                                        <i class="fa fa-user"></i> In Person
                                    </label>
                                    <label
                                        class="control-label"
                                        style="margin-left: 25px">
                                        <input
                                            type="checkbox"
                                            v-model="virtual" />
                                        <i class="fa fa-video-camera"></i>
                                        Virtual
                                    </label>
                                    <br />
                                    <!-- <span v-show="errors.has('type')" class="help-block has-error">{{ errors.first('type') }}</span>-->
                                </div>
                            </div>
                        </div>

                        <div class="row has-border-top">
                            <div class="col-sm-12">
                                <div
                                    class="form-group"
                                    :class="{ 'has-error': errors.has('availability_type') }"
                                >
                                    <label class="control-label" style="margin-bottom:0;">
                                        Do you have special instructions?
                                    </label>

                                    <label
                                        v-for="type in availabilityTypes.slice().reverse()"
                                        :key="type.id"
                                        class="radio availability-type-radio"
                                    >
                                        <input type="radio" :value="type.id" v-model="availability_type_id">
                                        {{ type.input_label }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div
                                    v-if="isAdditionalAvailabilityTypeId(availability_type_id)"
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('availability_subtype'),
                                    }"
                                >
                                    <label
                                        for="availability-subtype-select" 
                                        class="control-label" 
                                        id="availability-subtype-label"
                                    >
                                        Instructions
                                    </label>
                                    <select
                                        class="form-control"
                                        name="availability_subtype"
                                        id="availability-subtype-select"
                                        v-model="availability_subtype_id"
                                        v-validate="'required'"
                                    >
                                        <option
                                            v-for="subtype in availabilitySubtypes"
                                            :value="subtype.id"
                                            :key="subtype.id"
                                        >
                                            {{ subtype.type }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-sm-12" v-if="isOtherAvailabilitySubtypeId(availability_subtype_id) || isUnavailableAvailabilitySubtypeId(availability_subtype_id)">
                                <div
                                    class="form-group"
                                    :class="{
                                        input: true,
                                        'has-error': errors.has('comment'),
                                    }"
                                >
                                    <label for="comment" class="control-label">Comment</label>
                                    <textarea
                                        id="comment"
                                        placeholder="Type here..."
                                        class="form-control no-resize"
                                        v-model="comment"
                                        style="height: 114px"
                                        v-validate="isOtherAvailabilitySubtypeId(availability_subtype_id) ? 'required' : ''"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-danger pull-left"
                            @click="showDeleteWorkHour">
                            Delete
                        </button>
                        <button
                            :disabled="loadingMaxTimeEvent"
                            type="button"
                            class="btn btn-primary"
                            @click="checkUpdateWorkHour">
                            Save
                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal"
                            @click="closeModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="modal fade"
            data-backdrop="static"
            data-keyboard="false"
            id="calendar-work-time-delete-form-modal"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span
                                v-if="
                                    eventToEdit.item_source &&
                                    eventToEdit.item_source.repeat > 0
                                "
                                >Delete Recurring Event</span
                            >
                            <span v-else>Delete Event</span>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                                @click="closeModal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div
                            class="row"
                            v-if="
                                eventToEdit.item_source &&
                                eventToEdit.item_source.repeat > 0
                            ">
                            <div class="col-sm-12">
                                <div>
                                    <label>
                                        <input
                                            type="radio"
                                            v-model="edit_event_mode"
                                            value="0" />
                                        This event
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input
                                            type="radio"
                                            v-model="edit_event_mode"
                                            value="1" />
                                        This and following events
                                    </label>
                                </div>
                            </div>
                        </div>
                        <span v-else>
                            Are you sure you want to delete the event "{{
                                deleteTimeLabel
                            }}"?
                        </span>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="deleteWorkTime">
                            Delete
                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal"
                            @click="closeModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="modal modal-vertical-center fade"
            data-backdrop="static"
            data-keyboard="false"
            id="calendar-work-time-edit-event-form-modal"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit recurring event
                            <button
                                type="button"
                                class="close"
                                aria-label="Close"
                                @click="closeEditRecurringEventModal()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div>
                                    <label>
                                        <input
                                            type="radio"
                                            v-model="edit_event_mode"
                                            value="0" />
                                        This event
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input
                                            type="radio"
                                            v-model="edit_event_mode"
                                            value="1" />
                                        This and following events
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="saveRecurringEvent()">
                            Save
                        </button>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="closeEditRecurringEventModal()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade in" :class="showBackdrop"></div>
    </div>
</template>

<script>
import {
    REGULAR_AVAILABILITY_TYPE_ID,
    ADDITIONAL_AVAILABILITY_TYPE_ID,
    RESCHEDULING_AVAILABILITY_SUBTYPE_ID,
    OTHER_AVAILABILITY_SUBTYPE_ID,
    ENCINO_OFFICE_ID,
    COFFEE_BEANS_OFFICE_ROOM_ID,
    UNAVAILABLE_AVAILABILITY_SUBTYPE_ID,
} from '../../settings/index';

export default {
    props: [
        "workTime",
        "workTimeId",
        "deleteWorkTimeId",
        "editWorkTimeId",
        "eventNeedsAction",
        "changeEventCallback",
        "eventToEdit",
    ],
    data() {
        return {
            office_id: ENCINO_OFFICE_ID,
            office_room_id: COFFEE_BEANS_OFFICE_ROOM_ID,
            on: null,
            date: null,
            at: null,
            length: 60,
            repeat: 0,
            edit_event_mode: 0,
            in_person: false,
            virtual: true,
            availability_type_id: null,
            availability_subtype_id: null,
            comment: null
        };
    },
    computed: {
        maxTimeEvent() {
            return this.$store.state.maxTimeEvent;
        },

        loadingMaxTimeEvent() {
            return this.$store.state.loadingMaxTimeEvent;
        },

        totalHoursDropdown() {
            let hours = [];
            const startTime = moment(this.at, "HH:mm:ss");
            const endTime = moment(this.maxTimeEvent, "HH:mm:ss");
            const duration = moment.duration(endTime.diff(startTime));
            const period = parseInt(duration.asHours());

            for (let i = 1; i <= period; i++) {
                const item = {};
                item.val = 60 * i;
                item.label = i + ` hour${i > 1 ? 's' : ''}`;

                hours.push(item);
            }

            return hours;
        },

        showCreateModal() {
            return this.workTime != null;
        },
        showUpdateModal() {
            return this.workTimeId != null && this.editWorkTimeId != false
                ? "show"
                : "fade";
        },
        showBackdrop() {
            return this.workTime == null && this.workTimeId == null
                ? "hide"
                : "";
        },
        showDeleteWorkTimeModal() {
            return this.workTimeId != null && this.deleteWorkTimeId != false
                ? "show"
                : "hide";
        },
        offices() {
            return this.$store.state.offices;
        },
        office_rooms() {
            if (this.office_id == null) {
                return [];
            } else {
                return this.$store.state.officesRooms.filter((item) => {
                    return item.office_id == this.office_id;
                });
            }
        },
        defTime() {
            // this.on = this.workTime != null ? moment(this.workTime).format('YYYY-MM-DD') : moment().format('YYYY-MM-DD');

            return this.on;
        },
        hours() {
            const event_start_time = (this.date && this.at)
                ? moment(this.date + " " + this.at)
                : moment();

            const edit_start_time = moment().add(24, "hours");

            let hours = [];
            let start_time = event_start_time.clone()
                .set("hour", 7)
                .set("minute", 0)
                .set("second", 0);
            let end_time = event_start_time.clone()
                .set("hour", 21)
                .set("minute", 30)
                .set("second", 0);
            while (start_time.isSameOrBefore(end_time)) {
                if (edit_start_time.isBefore(start_time) || start_time.isSame(event_start_time)) {
                    hours.push({
                        t: start_time.format("HH:mm:ss"),
                        l: start_time.format("h:mm A"),
                    });
                }

                start_time = start_time.add("30", "m");
            }

            return hours;
        },
        deleteTimeLabel() {
            if (this.workTimeId != null && this.deleteWorkTimeId != false) {
                let workTime = this.eventToEdit.item_source;
                if (workTime && workTime.length) {
                    return (
                        " " +
                        moment(this.eventToEdit.start).format(
                            "ddd MM/DD HH:mm"
                        ) +
                        " - " +
                        moment(this.eventToEdit.end).format("HH:mm") +
                        ", " +
                        workTime.office_room.name +
                        ", " +
                        workTime.office.office +
                        " "
                    );
                } else {
                    return "2";
                }
            } else {
                return "1";
            }
        },
        availabilityTypes() {
            return this.$store.state.availabilityTypes;
        },
        availabilitySubtypes() {
            return this.$store.state.availabilitySubtypes;
        }
    },
    methods: {
        saveRecurringEvent() {
            let data = null;
            if (this.eventNeedsAction) {
                data = this.eventNeedsAction;
            } else {
                data = this.eventToEdit;
            }

            data.edit_event_mode = this.edit_event_mode;
            this.$emit(this.changeEventCallback, data);
            this.closeEditRecurringEventModal();
        },

        closeEditRecurringEventModal() {
            this.closeModal();
            this.$emit("clearEditRecurringEventForm");
        },

        closeModal() {
            this.edit_event_mode = 0;
            this.repeat = 0;
            this.office_id = ENCINO_OFFICE_ID;
            this.office_room_id = COFFEE_BEANS_OFFICE_ROOM_ID;
            this.on = null;
            this.date = null;
            this.at = null;
            this.in_person = false;
            this.virtual = true;
            this.length = 60;
            this.availability_type_id = null;
            this.availability_subtype_id = null;
            this.comment = null;
            this.$emit("clearSelectTime");
            this.errors.remove("length");
            this.errors.remove("availability_type");
            this.errors.remove("availability_subtype");
            this.errors.remove("comment");
            this.$validator.reset();
        },
        saveWorkHour() {
            this.$validator.validateAll().then((result) => {
                if (!this.length) {
                    this.errors.add({
                        field: "length",
                        msg: "Please choose a number of hours",
                    });
                    return;
                } else {
                    this.errors.remove("length");
                }
                
                if (!this.in_person && !this.virtual) {
                    this.errors.add({
                        field: "type",
                        msg: "You should select at least one checkbox",
                    });
                    return;
                } else {
                    this.errors.remove("type");
                }

                if (!this.availability_type_id) {
                    this.errors.add({
                        field: "availability_type",
                        msg: "Please choose type of availability",
                    });
                    return;
                } else {
                    this.errors.remove("availability_type");
                }

                if (this.isAdditionalAvailabilityTypeId(this.availability_type_id) && !this.availability_subtype_id) {
                    this.errors.add({
                        field: "availability_subtype",
                        msg: "Please choose subtype of availability",
                    });
                    return;
                } else {
                    this.errors.remove("availability_subtype");
                }

                if (this.isOtherAvailabilitySubtypeId(this.availability_subtype_id) && !this.comment) {
                    this.errors.add({
                        field: "comment",
                        msg: "Please fill in the comment field",
                    });
                    return;
                } else {
                    this.errors.remove("comment");
                }

                if (result) {
                    this.$store
                        .dispatch("saveProviderAvailabilityCalendarWorkHours", {
                            office_id: this.office_id,
                            office_room_id: this.office_room_id,
                            length: this.length,
                            repeat: this.repeat,
                            start_date: this.roundWorkTime(this.workTime),
                            in_person: this.in_person,
                            virtual: this.virtual,
                            availability_type_id: this.availability_type_id,
                            availability_subtype_id: this.availability_subtype_id,
                            comment: this.comment
                        })
                            .then((response) => {
                                this.$emit("refreshCalendar");
                                this.closeModal();
                                if (!response.data.success) {
                                    let errorMessage = response.data.message;
                                    const errors = response.data.errors
                                        ? Object.values(response.data.errors).reduce((prev, curr) => prev.concat(curr))
                                        : [];
                                    if (errors.length) {
                                        errorMessage = errors[0];
                                    }
                                    if (errorMessage) {
                                        this.$emit("showErrorMessage", errorMessage);
                                    }
                                }
                            });

                    return;
                }
            });
        },
        checkUpdateWorkHour() {
            this.$validator.validateAll().then((result) => {
                if (!this.length) {
                    this.errors.add({
                        field: "length",
                        msg: "Please choose a number of hours",
                    });
                    return;
                } else {
                    this.errors.remove("length");
                }

                if (!this.in_person && !this.virtual) {
                    this.errors.add({
                        field: "type",
                        msg: "You should select at least one checkbox",
                    });
                    return;
                } else {
                    this.errors.remove("type");
                }

                if (!this.availability_type_id) {
                    this.errors.add({
                        field: "availability_type",
                        msg: "Please choose type of availability",
                    });
                    return;
                } else {
                    this.errors.remove("availability_type");
                }

                if (this.isAdditionalAvailabilityTypeId(this.availability_type_id) && !this.availability_subtype_id) {
                    this.errors.add({
                        field: "availability_subtype",
                        msg: "Please choose subtype of availability",
                    });
                    return;
                } else {
                    this.errors.remove("availability_subtype");
                }

                if (this.isOtherAvailabilitySubtypeId(this.availability_subtype_id) && !this.comment) {
                    this.errors.add({
                        field: "comment",
                        msg: "Please fill in the comment field",
                    });
                    return;
                } else {
                    this.errors.remove("comment");
                }
                
                if (result) {
                    if (this.repeat != this.eventToEdit.item_source.repeat) {
                        this.eventToEdit.item_source.office_id = this.office_id;
                        this.eventToEdit.item_source.office_room_id =
                            this.office_room_id;
                        this.eventToEdit.item_source.repeat = this.repeat;
                        
                        return this.updateWorkHours("change_repeat");
                    }
                    this.eventToEdit.item_source.office_id = this.office_id;
                    this.eventToEdit.item_source.office_room_id =
                        this.office_room_id;
                    if (this.eventToEdit.item_source.repeat > 0) {
                        this.$emit("changeEditWorkTimeId", false);
                        this.$emit(
                            "changeEventCallback",
                            "updateProviderAvailabilityCalendarWorkHours"
                        );
                    } else {
                        return this.updateWorkHours();
                    }
                    return;
                }
            });
        },
        updateWorkHours(action = "update") {
            this.$store.dispatch("updateProviderAvailabilityCalendarWorkHours", {
                    id: this.workTimeId,
                    office_id: this.office_id,
                    office_room_id: this.office_room_id,
                    on: this.eventToEdit.item_source.day_of_week,
                    at: this.at,
                    length: this.length,
                    start_date: this.eventToEdit.start.format(),
                    end_date: this.eventToEdit.end
                        ? this.eventToEdit.end.format()
                        : null,
                    action: action,
                    in_person: this.in_person,
                    virtual: this.virtual,
                    availability_type_id: this.availability_type_id,
                    availability_subtype_id: this.availability_subtype_id,
                    comment: this.comment
                })
                .then((response) => {
                    this.$emit("refreshCalendar");
                    this.closeModal();
                    if (!response.data.success) {
                        let errorMessage = response.data.message;
                        const errors = response.data.errors
                            ? Object.values(response.data.errors).reduce((prev, curr) => prev.concat(curr))
                            : [];
                        if (errors.length) {
                            errorMessage = errors[0];
                        }
                        if (errorMessage) {
                            this.$parent.showErrorMessage(errorMessage);
                        }
                    }
                });
        },
        roundWorkTime(workTime) {
            if (moment(workTime).format("mm") === "15" || moment(workTime).format("mm") === "45") {
                return moment(workTime)
                    .add(-15, "minute")
                    .format("YYYY-MM-DD HH:mm:ss");
            }
            
            return moment(workTime).format("YYYY-MM-DD HH:mm:ss");
        },
        isSelectedDay(day) {
            var selectDay = moment(this.workTime).format("E") - 1;

            if (selectDay === day) {
                this.on = day;
            }
            return selectDay === day;
        },

        checkNeedDisabledTimeLength() {
            var t_date = moment()
                .add(this.on - (moment().format("E") - 1), "day")
                .format("YYYY-MM-DD");

            var thisDayAnotherWorkTimes =
                this.$store.state.availabilityCalendar.workHours.filter(
                    (item) => {
                        return item.day_of_week === this.on;
                    }
                );

            $("#calendar-work-time-update-form-modal #length option").each(
                (i, item) => {
                    var time = $(item).val();
                    var momentTime = moment(t_date + " " + this.at).add(
                        time,
                        "minute"
                    );

                    var dayWorkTimesAfter = thisDayAnotherWorkTimes.filter(
                        (item) => {
                            return (
                                momentTime >
                                    moment(t_date + " " + item.start_time) &&
                                moment(t_date + " " + this.at) <
                                    moment(t_date + " " + item.start_time).add(
                                        item.length,
                                        "minute"
                                    )
                            );
                        }
                    );

                    $(item).attr("disabled", dayWorkTimesAfter.length > 1);
                }
            );
        },

        deleteWorkTime() {
            this.$store
                .dispatch("deleteProviderAvailabilityCalendarWorkHours", {
                    id: this.workTimeId,
                    start_date: this.eventToEdit.start,
                    edit_event_mode: this.edit_event_mode,
                })
                .then(() => {
                    this.$emit("refreshCalendar");
                    this.closeModal();
                });
        },
        showDeleteWorkHour() {
            this.$emit("showDeleteWorkHour");
        },
        isRegularAvailabilityTypeId(id) {
            return id === REGULAR_AVAILABILITY_TYPE_ID;
        },
        isAdditionalAvailabilityTypeId(id) {
            return id === ADDITIONAL_AVAILABILITY_TYPE_ID;
        },
        isReschedulingAvailabilitySubtypeId(id) {
            return id === RESCHEDULING_AVAILABILITY_SUBTYPE_ID;
        },
        isOtherAvailabilitySubtypeId(id) {
            return id === OTHER_AVAILABILITY_SUBTYPE_ID;
        },
        isUnavailableAvailabilitySubtypeId(id) {
            return id === UNAVAILABLE_AVAILABILITY_SUBTYPE_ID;
        },
        handleStartTimeChange(value) {
            this.at = value;

            const payload = {
                date: this.date,
                time: this.at,
            };

            if (this.eventToEdit) {
                payload.event_id = this.eventToEdit.publicId;
            }

            this.$store.dispatch("getMaxTimeEvent", payload);
        },
    },
    
    watch: {
        in_person() {
            if (this.in_person || this.virtual) {
                this.errors.remove("type");
            }
        },

        virtual() {
            if (this.in_person || this.virtual) {
                this.errors.remove("type");
            }
        },

        availability_type_id(val) {
            if (val) {
                this.errors.remove("availability_type");

                if (this.isRegularAvailabilityTypeId(val)) {
                    this.availability_subtype_id = null;
                    this.comment = null;
                }
            }
        },

        availability_subtype_id(val) {
            if (val) {
                this.errors.remove("availability_subtype");
                this.errors.remove("comment");

                if (this.isReschedulingAvailabilitySubtypeId(val)) {
                    this.comment = null;
                }
            }
        },

        comment(val) {
            if (val) {
                this.errors.remove("comment");
            }
        },

        changeEventCallback: function (val) {
            if (val) {
                $("#calendar-work-time-edit-event-form-modal").modal("show");
            } else {
                $("#calendar-work-time-edit-event-form-modal").modal("hide");
            }
        },

        showCreateModal: function (val) {
            if (val) {
                if (
                    $('#calendar-work-time-add-form-modal select[name="length"] option[value="60"]:disabled').length
                ) {
                    this.length = 30;
                } else {
                    this.length = 60;
                }

                this.at = moment(this.roundWorkTime(this.workTime)).format("HH:mm:ss");

                $("#calendar-work-time-add-form-modal").modal("show");
            } else {
                $("#calendar-work-time-add-form-modal").modal("hide");
            }

            this.on = this.workTime != null
                ? moment(this.workTime).format("E")
                : moment().format("E");
            this.date = this.workTime != null
                ? moment(this.workTime).format("YYYY-MM-DD")
                : moment().format("YYYY-MM-DD");
        },

        showDeleteWorkTimeModal: function (val) {
            window.setTimeout(function () {
                $("#calendar-work-time-delete-form-modal").modal(val);
            }, 500);
        },

        showUpdateModal: function (val) {
            let element = this;
            if (val === "show") {
                let workTime = this.eventToEdit.item_source;
                if (workTime.length) {
                    this.office_id = workTime.office_id;
                    this.office_room_id = workTime.office_room_id;
                    setTimeout(() => {
                        this.office_room_id = workTime.office_room_id;
                    }, 10);
                    setTimeout(() => {
                        this.on = workTime.day_of_week;
                        $("#calendar-work-time-update-form-modal #date").val(
                            workTime.day_of_week
                        );

                        this.checkNeedDisabledTimeLength();
                    }, 10);
                    this.on = workTime.day_of_week;
                    this.date = this.eventToEdit.start.format("YYYY-MM-DD");
                    this.at = workTime.start_time;
                    this.length = workTime.length;
                    this.repeat = workTime.repeat;
                    this.virtual = workTime.virtual;
                    this.in_person = workTime.in_person;
                    this.availability_type_id = workTime.availability_type_id;
                    this.availability_subtype_id = workTime.availability_subtype_id;
                    this.comment = workTime.comment;
                }
                $("#calendar-work-time-update-form-modal").modal("show");
            } else {
                $("#calendar-work-time-update-form-modal").modal("hide");
            }
        },

        totalHoursDropdown() {
            const isCurrentLengthInvalid = this.date
                && !this.loadingMaxTimeEvent && this.totalHoursDropdown.map(item => item.val).indexOf(this.length) === -1;

            if (isCurrentLengthInvalid) {
                this.length = 0;
            }
        }
    },
};
</script>

<style scoped>
.modal {
    text-align: center;
    padding: 0 !important;
}

.modal:before {
    content: "";
    display: inline-block;
    height: 100%;
    vertical-align: middle;
    margin-right: -4px;
}

.modal-dialog {
    display: inline-block;
    text-align: left;
    vertical-align: middle;
}

/* .btn-group {
    border: 1px solid rgb(163, 173, 187);
    border-radius: 5px;
} */

/* .selected-availability-type {
    color: white;
} */

/* .availability-type {
    color: black;
} */

/* .has-error #availability-type-select {
    border: 1px solid #a94442 !important;
} */

.row.has-border-top {
    padding-top: 15px;
    border-top: 1px solid #e5e5e5;
}

.availability-type-radio {
    width: fit-content;
    padding-left:20px;
    font-weight:400;
}
.max-time-event-loader-wrapper {
    width: 20px;
    position: absolute;
    right: 25px;
    top: 33px;
}
</style>
