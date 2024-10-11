<template>
  <div class="appointments-table-container" v-if="notes">
    <div class="appointments-wrapper clearfix">
      <div class="show-selected row">
        <div class="col-xs-3" @click.prevent="bulkChangeFilters">
          <label>
            <input type="checkbox" :checked="allTimelineFiltersSelected" />
            <i class="fa" :class="getBadgeColor({ type: 'All' })">
              <img class="circle-icon" :src="getBadgeIcon({ type: 'All' })" />
            </i>
            All ({{ patient_chart_data_count.all_patient_data_count }})
          </label>
        </div>
        <div class="col-xs-3">
          <label>
            <input type="checkbox" v-model="timelineFilters.PatientComment" />
            <i class="fa" :class="getBadgeColor({ type: 'PatientComment' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientComment' })"
              />
            </i>
            Comments ({{ patient_chart_data_count.comments_count }})
          </label>
        </div>
        <div class="col-xs-3" v-if="is_admin">
          <label>
            <input type="checkbox" v-model="timelineFilters.PatientPrivateComment" />
            <i class="fa" :class="getBadgeColor({ type: 'PatientPrivateComment' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientComment' })"
              />
            </i>
            Private Comments ({{ patient_chart_data_count.private_comments_count }})
          </label>
        </div>
        <div class="col-xs-3">
          <label>
            <input type="checkbox" v-model="timelineFilters.PatientAlert" />
            <i class="fa" :class="getBadgeColor({ type: 'PatientAlert' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientAlert' })"
              />
            </i>
            Alerts ({{ patient_chart_data_count.alerts_count }})
          </label>
        </div>
        <div class="col-xs-3">
          <label>
            <input
              type="checkbox"
              v-model="timelineFilters.InitialAssessment"
            />
            <i class="fa" :class="getBadgeColor({ type: 'PatientDocument' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientDocument' })"
              />
            </i>
            Initial Assessments ({{ patient_chart_data_count.initial_assessments_count }})
          </label>
        </div>
        <div class="col-xs-3">
          <label>
            <input type="checkbox" v-model="timelineFilters.PatientDocument" />
            <i class="fa" :class="getBadgeColor({ type: 'PatientDocument' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientDocument' })"
              />
            </i>

            Documents ({{ patient_chart_data_count.documents_count }})
          </label>
        </div>
        <div class="col-xs-3" v-if="is_admin">
          <label>
            <input
              type="checkbox"
              v-model="timelineFilters.PatientPrivateDocument"
            />
            <i
              class="fa"
              :class="getBadgeColor({ type: 'PatientPrivateDocument' })"
            >
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientDocument' })"
              />
            </i>

            Private Documents ({{ patient_chart_data_count.private_docs_count }})
          </label>
        </div>
        <div class="col-xs-3">
          <label>
            <input type="checkbox" v-model="timelineFilters.PatientNote" />
            <i class="fa" :class="getBadgeColor({ type: 'PatientNote' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'PatientNote' })"
              />
            </i>
            Progress Notes ({{ patient_chart_data_count.progress_notes_count }})
          </label>
        </div>

        <div class="col-xs-3">
          <label>
            <input type="checkbox" v-model="timelineFilters.CallLog" />
            <i class="fa" :class="getBadgeColor({ type: 'CallLog' })">
              <img
                class="circle-icon"
                :src="getBadgeIcon({ type: 'CallLog' })"
              />
            </i>

            Call Logs ({{ patient_chart_data_count.call_logs_count }})
          </label>
        </div>
        <div class="col-xs-3">
          <label>
            <input
              type="checkbox"
              v-model="timelineFilters.TelehealthSession"
            />
            <i
              class="fa"
              :class="getBadgeColor({ type: 'TelehealthSession' })"
            ></i>

            Telehealth Sessions ({{ patient_chart_data_count.telehealth_sessions_count }}) 
          </label>
        </div>
      </div>

      <ul class="timeline col-xs-12">
        <template v-for="(item, index) in timelineItems">
          <li
            v-if="
              paginationChart &&
              paginationChart.next_page &&
              index === timelineItems.length - 1
            "
            class="load-more"
            style="color: transparent;"
          >
            <div
              v-observe-visibility="
                paginationChart.next_page && !chartScrolled ? loadMore : false
              "
            ></div>
            +
          </li>
          <li :class="getTimeLineItemClass(item)">
            <timeline-date
                v-if="item.type === 'date'"
                :item="item"
                :isScrollLoading="isScrollLoading"
                :getFormattedDateWithDayOfWeek="getFormattedDateWithDayOfWeek"
                :scrollDropdown="scrollDropdown"
            />
            <i
              class="fa"
              :class="getBadgeColor(item)"
              v-if="item.type !== 'date'"
            >
              <img
                v-if="item.type !== 'TelehealthSession'"
                class="circle-icon"
                :src="getBadgeIcon(item)"
              />
            </i>

            <div
              v-if="item.type === 'PatientAlert'"
              :id="item.type + item.model.id"
            >
              <timeline-alert :note="item.model" />
            </div>

            <div
              v-if="item.type === 'PatientComment'"
              :id="item.type + item.model.id"
            >
              <timeline-comment
                :note="item.model"
                @deleteCommentConfirmation="
                  $emit('deleteCommentConfirmation', $event)
                "
              />
            </div>
            <div
              v-if="
                item.type === 'PatientDocument' ||
                item.type === 'PatientAssessmentForm' ||
                item.type === 'PatientPrivateDocument'
              "
            >
              <timeline-document :note="item.model" />
            </div>
            <div v-if="item.type === 'PatientNote'" :id="item.type + item.model.id">
              <timeline-note :note="item.model" />
            </div>
            <div v-if="item.type === 'CallLog'" :id="item.type + item.model.id">
              <timeline-alert :note="item.model" />
            </div>
            <div v-if="item.type === 'PatientElectronicDocument'" :id="item.type + item.model.id">
              <timeline-electronic-document :note="item.model" />
            </div>
            <div
              v-if="item.type === 'TelehealthSession'"
              :id="item.type + item.model.id"
            >
              <timeline-telehealth-session :note="item.model" />
            </div>
          </li>
          <li
            v-if="item.type === 'date'"
            :id="item.type + item.model.id + 'placeholder'"
            style="margin-bottom: 0px;"
          ></li>
        </template>
      </ul>
      <div class="text-center" v-if="isLoading">
        <pageloader add-classes="timeline-loader"></pageloader>
      </div>
    </div>
  </div>
</template>

<script>
import TimelineAlert from "./timeline/TimelineAlert";
import TimelineComment from "./timeline/TimelineComment";
import TimelineDocument from "./timeline/TimelineDocument";
import TimelineNote from "./timeline/TimelineNote";
import TimelineElectronicDocument from "./timeline/TimelineElectronicDocument";
import TimelineTelehealthSession from "./timeline/TimelineTelehealthSession";
import TimelineDate from "./timeline/TimelineDate";

import DatetimeFormated from "./../../mixins/datetime-formated";
import ProviderInfo from "./../../mixins/provider-info";
import FileInfo from "./../../mixins/file-info";

export default {
    name: "timeline",
    mixins: [
        DatetimeFormated, 
        ProviderInfo, 
        FileInfo
    ],
    components: {
        TimelineAlert,
        TimelineComment,
        TimelineDocument,
        TimelineNote,
        TimelineElectronicDocument,
        TimelineTelehealthSession,
        TimelineDate,
    },
    props: {
        scrollTo: {
            type: String,
        },
    },

    data() {
        return {
            timeline: {
                show_date: [],
            },
            chartScrolled: false,
            patient_chart_data_count: {
                alerts_count: '0',
                all_patient_data_count: '0',
                call_logs_count: '0',
                comments_count:'0',
                private_comments_count:'0',
                documents_count: '0',
                initial_assessments_count:'0',
                private_docs_count:'0',
                progress_notes_count:'0',
                telehealth_sessions_count: '0',
            },
            scrollDropdown: [
                {
                    id: 1,
                    label: 'Today',
                    action: () => this.scrollToItem(moment().format('dddd, D MMM. YYYY'))
                },
                {
                    id: 2,
                    label: 'The very beginning',
                    action: () => this.scrollToFirstItem(moment().subtract(50, 'years').format('dddd, D MMM. YYYY'))
                },
                {
                    id: 3,
                    label: 'Specific date',
                    action: (value) => this.scrollToItem(this.getFormattedDateWithDayOfWeek(value))
                },
            ],
            isScrollLoading: false,
            scrolledDate: null,
            isScrollToFirst: false,

            scrollItemFromParams: null,
        };
    },

    computed: {
        timelineFilters() {
            return this.$store.state.timelineFilters;
        },

        allTimelineFiltersSelected() {
            return this.$store.state.allTimelineFiltersSelected;
        },

        is_admin() {
            return this.$store.state.isUserAdmin;
        },

        notes() {
            return this.$store.state.currentPatientNotes;
        },
        is_read_only_mode() {
            return this.$store.state.is_read_only_mode;
        },
        timelineItems() {
            this.timeline.show_date = [];
            let items = [];

            if (this.$store.state.currentPatientNotes !== null) {
                this.$store.state.currentPatientNotes.forEach((note) => {
                    if (this.needShowDate(note.created_at)) {
                        items.push({
                            type: "date",
                            model: note,
                        });
                    }

                    items.push({
                        type:
                        note.model === "PatientDocument" && note.only_for_admin == 1
                            ? "PatientPrivateDocument"
                            : note.model,
                        model: note,
                    });
                });
            }

            return items;
        },

        paginationChart() {
            return this.$store.state.paginationChart;
        },

        isLoading() {
            return this.$store.state.chart.isLoading;
        },
    },

    watch: {
        scrollTo() {
            this.initScrollItemFromParams();
        },

        timelineFilters: {
            handler(val) {
                let selectedAll = true;
                for (let i in val) {
                    if (!val[i]) {
                        selectedAll = false;
                        break;
                    }
                }
                this.$store.commit("setTimelineFiltersSelectedAll", selectedAll);
                this.$store.commit("setTimelineFilters", val);
                this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {
                    id: this.$route.params.id,
                });
            },
            deep: true,
        },

        timelineItems: {
            handler(newNotes, oldNotes) {
                if (newNotes.length === oldNotes.length) {
                    this.isScrollLoading = false;
                    return;
                }

                if (this.scrollItemFromParams) {
                    window.setTimeout(() => {
                        this.scrollToItemFromParams();
                    }, 100);
                } else if (this.scrolledDate || this.isScrollToFirst) {
                    window.setTimeout(() => {
                        this.scrollToItem(this.scrolledDate, this.isScrollToFirst);
                    }, 100);
                } else {
                    this.isScrollLoading = false;
                }
            },
            deep: true
        }
    },

    created() {
        this.patientChartDataCountInit();
    },

    mounted() {
        this.initScrollItemFromParams();
    },

    methods: {
        initScrollItemFromParams() {
            this.scrollItemFromParams = this.scrollTo;
            if (this.scrollTo) {
                this.scrollToItemFromParams();
            }
        },
        scrollToItemFromParams() {
            if (!this.scrollItemFromParams) {
                return;
            }

            const element = document.getElementById(this.scrollItemFromParams);

            if (element) {
                const elementRect = element.getBoundingClientRect();
                const absoluteElementTop = elementRect.top + window.pageYOffset;
                const top = absoluteElementTop - 130;

                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });

                this.isScrollLoading = false;
                this.scrollItemFromParams = null;
            } else if (this.paginationChart.next_page) {
                this.isScrollLoading = true;
                this.$store.dispatch('getPatientTimeline', {
                    id: this.$route.params.id,
                    page: this.paginationChart.next_page
                });
            } else {
                this.isScrollLoading = false;
                this.scrollItemFromParams = null;
            }
        },

        scrollToFirstItem(date) {
            this.isScrollToFirst = true;
            this.scrollToItem(date);
        },
        scrollToItem(date) {
            this.scrolledDate = date;
            
            const items = this.timelineItems.filter(item => item.type === 'date');
            const targetItem = items.find(item => this.getFormattedDateWithDayOfWeek(item.model.created_at) === this.scrolledDate);
            const lastItem = items.find(item => item.model.created_at === items[items.length - 1].model.created_at);

            if (items && this.scrolledDate === moment().format('dddd, D MMM. YYYY')) {
                this.scrollIntoView(items[0]);
            } else if (targetItem) {
                this.scrollIntoView(targetItem);
            } else if (!this.paginationChart.next_page && (this.isScrollToFirst || moment(this.scrolledDate).isBefore(lastItem.model.created_at, 'day'))) {
                this.scrollIntoView(lastItem);
            } else if (moment(lastItem.model.created_at).isBefore(this.scrolledDate, 'day')) {
                this.scrollToClosestDate(this.scrolledDate, items);
            } else if (this.paginationChart.next_page) {
                this.isScrollLoading = true;
                this.$store.dispatch('getPatientTimeline', {
                    id: this.$route.params.id,
                    page: this.paginationChart.next_page
                });
            }
        },
        scrollToClosestDate(targetDate, items) {
            const formattedDates = items.map(item => ({
                date: this.getFormattedDateWithDayOfWeek(item.model.created_at),
                item
            }));
            const lastDate = items[items.length - 1];

            if (formattedDates.length === 0) {
                return;
            }

            const closest = formattedDates.reduce((prev, curr) => {
                const currDateDifference = Math.abs(moment(curr.date, 'dddd, D MMM. YYYY') - moment(targetDate, 'dddd, D MMM. YYYY'));
                const prevDateDifference = Math.abs(moment(prev.date, 'dddd, D MMM. YYYY') - moment(targetDate, 'dddd, D MMM. YYYY'));

                return currDateDifference < prevDateDifference ? curr : prev;
            });

            if (closest && closest.item) {
                this.scrollIntoView(closest.item);
            } else {
                this.scrollIntoView(lastDate);
            }
        },
        scrollIntoView(item) {
            const element = document.getElementById(item.type + item.model.id + 'placeholder');
            if (element) {
                const elementRect = element.getBoundingClientRect();
                const absoluteElementTop = elementRect.top + window.pageYOffset;
                const top = absoluteElementTop - 100;

                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });
            }

            this.isScrollLoading = false;
            this.scrolledDate = null;
            this.isScrollToFirst = false;
        },

        patientChartDataCountInit() {
            const id = this.$route.params.id;
            this.$store
                .dispatch("getPatientChartDataCount", { patientId: id })
                .then((response) => {
                    let data = response.data;
                    this.patient_chart_data_count.alerts_count= response.data.alerts_count;
                    this.patient_chart_data_count.all_patient_data_count= response.data.all_patient_data_count;
                    this.patient_chart_data_count.call_logs_count= response.data.call_logs_count;
                    this.patient_chart_data_count.comments_count= response.data.comments_count;
                    this.patient_chart_data_count.private_comments_count= response.data.private_comments_count;
                    this.patient_chart_data_count.documents_count= response.data.documents_count;
                    this.patient_chart_data_count.initial_assessments_count= response.data.initial_assessments_count;
                    this.patient_chart_data_count.private_docs_count= response.data.private_docs_count;
                    this.patient_chart_data_count.progress_notes_count= response.data.progress_notes_count;
                    this.patient_chart_data_count.telehealth_sessions_count= response.data.telehealth_sessions_count;
                })
                .catch((error) => {
                    console.error("An error occurred:", error);
                });
        },

        bulkChangeFilters() {
            for (let i in this.timelineFilters) {
                this.timelineFilters[i] = !this.allTimelineFiltersSelected;
            }
        },

        needShowDate(date) {
            let date_string = this.getFormattedDate(date);
            let need = this.timeline.show_date.indexOf(date_string) === -1;
            if (need) {
                this.timeline.show_date.push(date_string);
            }

            return need;
        },

        getTimeLineItemClass(item) {
            let classes = "";

            switch (item.type) {
                case "date":
                classes += " time-label";

                break;
                default:
                // classes += ' hidden';
                break;
            }

            return classes;
        },
        getBadgeColor(item) {
            let color = "";
            switch (item.type) {
                case "date":
                color = " ";
                break;
                case "PatientDocument":
                color = " bg-blue";
                break;
                case "PatientPrivateDocument":
                color = " bg-black";
                break;
                case "PatientAlert":
                color = "bg-red";
                break;
                case "PatientComment":
                color = " bg-yellow";
                break;
                case "PatientPrivateComment":
                color = " bg-black";
                break;
                case "PatientNote":
                color = " bg-aqua";
                break;
                case "PatientAssessmentForm":
                color = " bg-blue";
                break;
                case "All":
                case "CallLog":
                color = " bg-green";
                break;
                case "TelehealthSession":
                color = " fa-video-camera bg-green";
                break;
                default:
                color = "bg-blue";
                break;
            }

            return color;
        },
        getBadgeIcon(item) {
            let icon = "/images/icons/";
            switch (item.type) {
                case "date":
                icon += " ";
                break;
                case "PatientDocument":
                icon += "document.png";
                break;
                case "PatientPrivateDocument":
                icon += "document.png";
                break;
                case "PatientAlert":
                icon += "alert.png";
                break;
                case "PatientComment":
                icon += "comment.png";
                break;
                case "PatientNote":
                icon += "progress-note.png";
                break;
                case "PatientAssessmentForm":
                icon += "document.png";
                break;
                case "PatientElectronicDocument":
                icon += "document.png";
                break;
                case "CallLog":
                icon += "phone.png";
                break;
                case "All":
                icon += "checkmark.png";
                break;
                default:
                icon += "comment.png";
                break;
            }

            return icon;
        },

        loadMore(isVisible, entry) {
            if (!isVisible) {
                return;
            }

            this.chartScrolled = true;
            this.$emit("loadChart");
            this.chartScrolled = false;
        },
    },
};
</script>

<style scoped>
.bg-black {
    background: #999999 !important;
}

.timeline-loader {
    width: 100px;
    height: 100px;
    margin-bottom: 60px;
}
.fa-fax {
    background-color: #d2691e;
    color: #fff8dc;
}
</style>
