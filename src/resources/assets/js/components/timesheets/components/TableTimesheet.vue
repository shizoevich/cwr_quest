<template>
  <div class="timesheet-tables" v-loading.fullscreen.lock="isLoading">
    <div class="salary-table-filter">
      <el-checkbox @change="handleShowOnlyChanges" v-model="showOnlyChanges">
        Show Only Therapist Changes
      </el-checkbox>
    </div>
    <div class="salary-table-item" v-for="tableData in tablesData">
      <div class="salary-table-title">
        {{ tableData.title }}
      </div>
      <el-table
        class="salary-table"
        :data="tableData.data"
        :summary-method="getSummaries"
        show-summary
        border
        style="width: 100%"
      >
        <el-table-column type="index" width="50">
          <template slot-scope="scope">
            <div class="column-content-center">
              {{ scope.$index + 1 }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="date" label="Date" width="200">
        </el-table-column>
        <el-table-column prop="patient_name" label="Patient" min-width="150">
          <template slot-scope="scope">
            <div class="patient-cell">
              <el-link
                :href="`/chart/${scope.row.patient_id}`"
                type="primary"
                target="_blank"
              >
                {{ scope.row.patient_name }}
              </el-link>
              <div
                class="patient-cell__summary"
                v-if="tableData.name === 'visits'"
              >
                {{ missingProgressNote(scope.row) }}
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          v-if="tableData.isAmount"
          prop="amount"
          label="Amount"
          header-align="center"
          width="120"
        >
          <template slot-scope="scope">
            <div class="column-content-center">
              ${{ tableData.data[scope.$index].amount }}
            </div>
          </template>
        </el-table-column>
        <el-table-column
          v-if="tableData.isOvertime"
          prop="is_overtime"
          label="Overtime"
          header-align="center"
          width="120"
        >
          <template slot-scope="scope">
            <div class="column-content-center">
              {{ overtimeText(tableData.data[scope.$index].is_overtime) }}
            </div>
          </template>
        </el-table-column>
       <el-table-column
          v-if="tableData.isAmount"
          header-align="center"
          width="120"
        >
          <template slot-scope="scope">
            <div class="column-content-center">
             <el-button
              @click="deleteLateCancelation(tableData.data[scope.$index].id)"
                type="danger"
                icon="el-icon-delete"
                size="mini"
                class="delete-btn"></el-button>     
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Action" header-align="center" width="200">
          <template slot-scope="scope">
            <div class="column-content-center">
              <template v-if="tableData.data[scope.$index].is_custom_created">
                <template
                  v-if="
                    tableData.data[scope.$index].accepted_at === null &&
                    tableData.data[scope.$index].declined_at === null
                  "
                >
                  <el-popconfirm
                    @confirm="
                      changeTimesheet(
                        {
                          id: tableData.data[scope.$index].id,
                          name: tableData.name,
                        },
                        'acceptedTimesheet'
                      )
                    "
                    title="Are you sure to Accept this?"
                  >
                    <el-button
                      :disabled="isDisabled"
                      class="button-accept"
                      type="success"
                      icon="el-icon-check"
                      slot="reference"
                      plain
                      circle
                    />
                  </el-popconfirm>
                  <el-popconfirm
                    @confirm="
                      changeTimesheet(
                        {
                          id: tableData.data[scope.$index].id,
                          name: tableData.name,
                        },
                        'declinedTimesheet'
                      )
                    "
                    title="Are you sure to decline this?"
                  >
                    <el-button
                      :disabled="isDisabled"
                      type="danger"
                      icon="el-icon-close"
                      slot="reference"
                      plain
                      circle
                    />
                  </el-popconfirm>
                </template>
                <template v-else>
                  <div class="action-text">
                    <p>
                      {{
                        tableData.data[scope.$index].accepted_at !== null
                          ? "Accepted"
                          : "Declined"
                      }}
                      at
                    </p>
                    <p v-if="tableData.data[scope.$index].accepted_at">
                      {{ changeDate(tableData.data[scope.$index].accepted_at) }}
                    </p>
                    <p v-else>
                      {{ changeDate(tableData.data[scope.$index].declined_at) }}
                    </p>
                  </div>
                </template>
              </template>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
</template>

<script>
export default {
  name: "TableTimesheet",
  props: {
    initTablesData: {
      type: Array,
      default() {
        return [];
      },
    },
    isDisabled: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isLoading: false,
      tablesData: [],
      showOnlyChanges: false,
    };
  },
  watch: {
    initTablesData() {
      this.initializationTablesData();
    },
  },
  methods: {
    initializationTablesData() {
      this.tablesData = _.cloneDeep(this.initTablesData);
    },
    overtimeText(isOvertime) {
      if (isOvertime) {
        return "Yes";
      }
      return "No";
    },
    missingProgressNote(row) {
      if (row.is_progress_note_missing) {
        return row.is_initial ? '(Missing Initial Assessment)' : '(Missing Progress Note)';
      }
      return "";
    },
    handleShowOnlyChanges() {
      this.$emit("changeTable", this.showOnlyChanges);
    },
    changeDate(date) {
      return moment(date).format("MM/DD/YYYY");
    },
    changeTimesheet(payload, dispatchName) {
      this.isLoading = true;
      this.$store
        .dispatch(dispatchName, payload)
        .then(() => {
          this.$emit("changeTable", this.showOnlyChanges);
        })
        .catch(() => {
          this.$message({
            type: "error",
            message: "Oops, something went wrong!",
            duration: 10000,
          });
        })
        .finally(() => (this.isLoading = false));
    },
    getSummaries({ columns, data }) {
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 1) {
          sums[index] = "Total";
          return;
        }
        let count = 0,
          totalAmountCount = 0,
          amount = 0;
        data.forEach((item) => {
          if (item.is_overtime) {
            count++;
            sums[3] = count;
          } else if (item.is_overtime !== undefined) {
            //code before
            //sums[3] = 0;
            sums[3] = count;
          }
          if (item.amount) {
            amount += Number(item.amount);
            sums[3] = `$${amount.toFixed(2)}`;
          }
          if (this.showOnlyChanges && item.is_custom_created) {
            totalAmountCount++;
          } else if (!this.showOnlyChanges) {
            totalAmountCount = data.length;
          }
        });
        sums[2] = totalAmountCount;
      });
      return sums;
    },
     deleteLateCancelation(payload)
     {
       this.$store
       .dispatch('deleteLateCancelation', payload)
       .then(() => {
          this.$emit("changeTable", this.showOnlyChanges);
        })
        .catch(() => {
          this.$message({
            type: "error",
            message: "Oops, something went wrong!"
          });
        })
        .finally(() => (this.isLoading = false));
     },
  },
  mounted() {
    this.initializationTablesData();
  },
};
</script>

<style lang="scss">
.timesheet-tables {
  .el-table__row {
    &.is-hidden {
      display: none;
    }
  }

  .button-accept {
    margin-right: 10px;
  }
  .delete-btn{
    margin:2px;
  }
  
}
</style>
