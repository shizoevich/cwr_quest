<template>
  <div class="container">
    <div class="row">
      <div v-if="is_loading_page" class="text-center page-loader-wrapper">
          <pageloader add-classes="page-loader"></pageloader>
      </div>
      <div v-else class="vue-wrapper">
        <h2 class="text-center">Salary Quota Calculator</h2>

        <div class="panel panel-default">
          <div class="panel-body salary-calculator__panel">
            <div class="salary-calculator__form">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Therapist Name</label>
                    <select class="form-control" v-model="formData.provider_id" @change="handleSelectProvider">
                      <option v-for="provider in providers" :value="provider.id">{{ provider.provider_name }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Billing Period</label>
                    <select class="form-control" v-model="formData.billing_period_id" name="billing_period_id">
                      <option :value="period.id" v-for="period in billing_periods">{{ getFormattedDate(period.start_date) }} - {{ getFormattedDate(period.end_date) }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Weeks count to check</label>
                    <input v-model="formData.weeks_count" type="number" class="form-control">
                  </div>
                </div>
              </div>

              <div style="margin-bottom: 15px;">
                <span>Therapist Tariff Plan: {{ provider_tariff_plan }}</span>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Work Hours Per Week</label>
                    <input v-model="formData.work_hours_per_week" type="number" class="form-control">
                  </div>
                </div>

                <div class="col-md-5">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Visits Per Billing Period For Incentive</label>
                    <input v-model="formData.visits_per_billing_period_for_incentive" type="number" class="form-control" style="width: 283.33px;">
                  </div>
                </div>
              </div>

              <div
                  v-for="(value, key) in formData.prices"
                  :key="key"
                  class="row"
              >
                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Regular Price ({{ value.visit_length }} min.)</label>
                    <input v-model="value.regular" type="number" class="form-control">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Incentive Price ({{ value.visit_length }} min.)</label>
                    <input v-model="value.incentive" type="number" class="form-control">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Reduced Price ({{ value.visit_length }} min.)</label>
                    <input v-model="value.reduced" type="number" class="form-control">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group inline-block salary-calculator__form-group">
                    <label>Quota</label>

                    <el-checkbox
                        v-model="formData.with_active"
                        :checked="!!formData.with_active"
                        class="salary-calculator__checkbox"
                    >
                      Active
                    </el-checkbox>
                    <el-checkbox
                        v-model="formData.with_visits"
                        class="salary-calculator__checkbox"
                        :checked="!!formData.with_visits"
                    >
                      Visits
                    </el-checkbox>
                    <el-checkbox
                        v-model="formData.with_cancelled"
                        class="salary-calculator__checkbox"
                        :checked="!!formData.with_cancelled"
                    >
                      Cancelled
                    </el-checkbox>
                    <el-checkbox
                        v-model="formData.with_availability"
                        class="salary-calculator__checkbox"
                        :checked="!!formData.with_availability"
                    >
                      Availability for new pt.
                    </el-checkbox>
                  </div>
                </div>

                <div class="col-md-8">
                  <div class="form-group">
                    <label>KPI</label>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Total Cancelled Appts. Percent</label>
                          <input v-model="formData.total_cancelled_percentage_kpi.value" type="number" class="form-control salary-calculator__form-group">
                          <label style="margin-top: 10px;">Restriction:</label>
                          <div v-for="option in kpi_options" :key="option.id">
                            <label class="radio salary-calculator__radio_kpi">
                              <input type="radio" :value="option.value" v-model="formData.total_cancelled_percentage_kpi.kpi">
                              {{ option.label }}
                            </label>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Cancelled By Provider Appts. Percent</label>
                          <input v-model="formData.cancelled_by_provider_percentage_kpi.value" type="number" class="form-control salary-calculator__form-group">
                          <label style="margin-top: 10px;">Restriction:</label>
                          <div v-for="option in kpi_options" :key="option.id">
                            <label class="radio salary-calculator__radio_kpi">
                              <input type="radio" :value="option.value" v-model="formData.cancelled_by_provider_percentage_kpi.kpi">
                              {{ option.label }}
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="salary-calculator__btn-calculate-wrap">
                <el-button
                    :loading="is_loading_calculate"
                    type="primary"
                    @click.prevent="handleCalculate"
                >
                  Calculate
                </el-button>
              </div>
            </div>

            <div v-if="is_loaded_statistics" class="salary-calculator__block-divider"/>

            <div v-if="is_loaded_statistics" class="row">
              <div class="col-md-5" style="padding: 10px 0 0 15px;">
                <div>
                  <label>Date Range ({{ statistics.weeks_count }} weeks):</label>
                  <span>{{ statistics.start_date }} - {{ statistics.end_date }}</span>
                </div>
                <div>
                  <label>All Avg:</label>
                  <span :class="get_statistics_total_avg_class">{{ statistics.total_hours_avg }}</span>
                </div>
                <div>
                  <label>Visits Avg:</label>
                  <span>{{ statistics.visits_avg }}</span>
                </div>
                <div>
                  <label>Used Price:</label>
                  <span>{{ statistics.used_quota }}</span>
                </div>
                <div style="margin-top: 10px;">
                  <label>Selected Billing Period:</label>
                  <span>{{ statistics.billing_period }}</span>
                </div>
                <div>
                  <label>Visits count for billing period:</label>
                  <span>{{ statistics.visits_count_for_billing_period }}</span>
                </div>
                <div>
                  <label>Payout for visits (old rules):</label>
                  <span>${{ statistics.old_salary }}</span>
                </div>
                <div>
                  <label>Payout for visits (new rules, without incentive):</label>
                  <span>${{ statistics.new_salary }}</span>
                </div>
                <div>
                  <label>Payout diff (new minus old):</label>
                  <span>${{ salary_diff }}</span>
                </div>
                <div v-if="statistics.overtime_count">
                  <label>Overtime visits count:</label>
                  <span>{{ statistics.overtime_count }}</span>
                </div>
                <template v-if="statistics.incentive_bonus">
                    <div>
                        <label>"Incentive" visits count for billing period:</label>
                        <span>{{ statistics.incentive_visits_count }}</span>
                    </div>
                    <div>
                        <label>Incentive bonus for billing period:</label>
                        <span>${{ statistics.incentive_bonus }}</span>
                    </div>
                    <div>
                        <label>Payout for visits (new rules, with incentive):</label>
                        <span>${{ statistics.new_salary + statistics.incentive_bonus }}</span>
                    </div>
                    <div>
                        <label>Payout diff (new minus old):</label>
                        <span>${{ payout_diff }}</span>
                    </div>
                </template>
                <template v-if="statistics.reduced_payout">
                  <div>
                    <label>Reduced payout:</label>
                    <span>${{ statistics.reduced_payout }}</span>
                  </div>
                </template>

              </div>

              <div class="col-md-7">
                <table class="statistic-table table table-condenced table-bordered dataTable">
                  <thead>
                  <tr>
                    <td style="width: 240px;">Work Hour Type</td>
                    <td style="width: 33px;">Total</td>
                    <td style="width: 33px;">Avg.</td>
                    <td style="width: 70px;">Percentage</td>
                    <td style="width: 33px;">KPI</td>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="(value, key) in statistics.work_hours_data" :key="key">
                    <td>{{value.label}}</td>
                    <td>{{value.count}}</td>
                    <td>{{value.avg}}</td>
                    <td :class="getStatisticsPercentageClass(key, value)">{{value.percentage}}%</td>
                    <td>{{getStatisticsKPI(key)}}</td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    name: "SalaryQuotaCalculator",
    data() {
        return {
            formData: {
                provider_id: null,
                billing_period_id: null,
                work_hours_per_week: 20,
                visits_per_billing_period_for_incentive: 40,
                weeks_count: 13,
                prices: {
                    visit_length_60: {
                        visit_length: 60,
                        regular: 70,
                        incentive: 80,
                        reduced: 60,
                    },
                    visit_length_45: {
                        visit_length: 45,
                        regular: 52.5,
                        incentive: 60,
                        reduced: 45,
                    },
                    visit_length_30: {
                        visit_length: 30,
                        regular: 35,
                        incentive: 40,
                        reduced: 30,
                    },
                },

                with_active: 0,
                with_visits: 1,
                with_cancelled: 0,
                with_availability: 0,

                total_cancelled_percentage_kpi: {
                    value: 30,
                    kpi: "remove_incentive",
                },
                cancelled_by_provider_percentage_kpi: {
                    value: 10,
                    kpi: "remove_incentive",
                },
            },

            providers: [],
            billing_periods: [],
            is_loaded_providers: false,
            is_loaded_billing_periods: false,

            kpi_options: [
                {
                    value: "reduce_price",
                    label: "Reduce Price",
                },
                {
                    value: "remove_incentive",
                    label: "Remove Incentive",
                },
            ],
          
            is_loading_calculate: false,

            is_loaded_statistics: false,
            statistics: {
                start_date: "",
                end_date: "",
                weeks_count: null,
                work_hours_per_week: null,
                total_hours_avg: null,
                visits_avg: null,
                used_quota: "",
                billing_period: "",
                visits_count_for_billing_period: null,
                new_salary: null,
                old_salary: null,
                incentive_bonus: null,
                incentive_visits_count: null,
                reduced_payout: null,
                overtime_count: null,
                total_cancelled_percentage: null,
                cancelled_by_provider_percentage: null,
                work_hours_data: {},
            }
        }
    },
    mounted() {
        this.fetchProviders();
        this.fetchBillingPeriods();
    },
    computed: {
        is_loading_page() {
            return !this.is_loaded_providers || !this.is_loaded_billing_periods;
        },
        selected_provider() {
            if (!this.formData.provider_id) {
                return {};
            }

            return this.providers.find(item => item.id === this.formData.provider_id);
        },
        salary_diff() {
            return Math.abs(this.statistics.new_salary - this.statistics.old_salary);
        },
        payout_diff() {
            return (this.statistics.new_salary + this.statistics.incentive_bonus - this.statistics.old_salary).toFixed(2);
        },
        provider_tariff_plan() {
            return this.selected_provider.tariff_plan || "-";
        },
        get_statistics_total_avg_class() {
            return this.statistics.total_hours_avg >= this.statistics.work_hours_per_week
                ? "text-green"
                : "text-red";
        },
    },
    methods: {
        fetchProviders() {
            this.$store.dispatch("getProvidersForSalaryQuota")
                .then(response => {
                    this.providers = response.data.providers.map(item => ({
                        id: item.id,
                        provider_name: item.provider_name,
                        work_hours_per_week: item.work_hours_per_week,
                        tariff_plan: item.tariff_plan && item.tariff_plan.length
                            ? item.tariff_plan[0].name
                            : "-",
                    }));
                })
                .finally(() => {
                    this.is_loaded_providers = true;
                });
        },
        fetchBillingPeriods() {
            this.$store.dispatch("getBillingPeriodList")
                .then(response => {
                    this.billing_periods = response.data.billing_periods.bi_weekly;
                })
                .finally(() => {
                    this.is_loaded_billing_periods = true;
                });
        },
        handleSelectProvider() {
            this.formData.work_hours_per_week = this.selected_provider.work_hours_per_week;
            this.formData.visits_per_billing_period_for_incentive = this.formData.work_hours_per_week * 2;
        },
        handleCalculate() {
            this.is_loading_calculate = true;

            this.$store.dispatch("calculateSalary", this.formData)
                .then(response => {
                    this.statistics = response.data;
                    this.is_loaded_statistics = true;
                })
                .finally(() => {
                    this.is_loading_calculate = false;
                });
        },
        getFormattedDate(date) {
            return this.$moment(date).format("MM/DD/YYYY");
        },
        getStatisticsPercentageClass(key, value) {
            if (key === "cancelled") {
                return value.percentage >= this.statistics.total_cancelled_percentage ? "text-red" : "text-green";
            } else if (key === "cancelled_by_provider") {
              return value.percentage >= this.statistics.cancelled_by_provider_percentage ? "text-red" : "text-green";
            } else {
               return "";
            }
        },
        getStatisticsKPI(key) {
            if (key === "cancelled") {
                return this.statistics.total_cancelled_percentage + "%";
            } else if (key === "cancelled_by_provider") {
                return this.statistics.cancelled_by_provider_percentage + "%";
            } else {
                return "-";
            }
        },
    },
}
</script>

<style scoped>
.page-loader-wrapper {
  height: 100vh;
}
.page-loader-wrapper:before {
  display: inline-block;
  vertical-align: middle;
  content: " ";
  height: 100%;
}

.page-loader {
  max-width: 200px;
  max-height: 200px;
}

.salary-calculator__panel {
    width: 950px;
    margin: 0 auto;
}

.salary-calculator__form {
  padding-left: 10px;
}

.salary-calculator__form-group {
    width: 100%;
}

.salary-calculator__checkbox {
    display: block;
    margin-left: 10px;
}

.salary-calculator__label_radio {
    margin-bottom: 0;
}

.salary-calculator__radio_kpi {
    margin: 10px 0 10px 25px;
}

.salary-calculator__btn-calculate-wrap {
    text-align: right;
}

.salary-calculator__block-divider {
    border-top: 2px solid #e7e7e7;
    margin: 30px 0;
}
</style>