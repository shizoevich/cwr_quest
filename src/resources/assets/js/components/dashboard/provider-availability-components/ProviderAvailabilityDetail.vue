<template>
    <div class="section-detail">
        <div class="section-detail__buttons">
            <slot name="buttons" />

            <button
                type="button"
                class="btn btn-primary"
                data-toggle="collapse"
                :data-target="`#detail-${providerId}`"
                style="width: 92px;"
                @click="initTable"
            >
                Detail
            </button>
        </div>
        
        <br />
        <br />

        <div :id="`detail-${providerId}`" class="collapse">
            <br />
            <h4 class="text-center">Billing Periods History</h4>
            <div class="table-responsive">
                <table :id="`detail-table-${providerId}`" class="statistic-table table table-condenced table-striped table-bordered dataTable">
                    <thead>
                        <tr>
                            <th>Billing Period</th>
                            <th>Minimum Work Hours</th>
                            <th>Availability Hours</th>
                            <th>Total Work Hours</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <br />
        <br />
    </div>
</template>

<script>
export default {
    name: 'ProviderAvailabilityDetail',
    props: {
        providerId: {
            type: [String, Number],
            required: true,
        },
    },

    data: () => ({
        table: null,
    }),
  
    methods: {
        initTable() {
            if (this.table) {
                return;
            }

            this.table = $(`#detail-table-${this.providerId}`).DataTable({
                ajax: {
                    url: `/dashboard/statistic/therapists-availability/${this.providerId}/history`,
                    data: function (d) {
                        d.limit = d.length;
                        d.page = (d.start / d.length) + 1;
                    },
                    dataSrc: function(response) {
                        response.recordsTotal = response.meta.pagination.total;
                        response.recordsFiltered = response.meta.pagination.total;

                        return response.data;
                    }
                },
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                pageLength: 10,
                lengthChange: false,
                columns: [
                    {
                        render: function (data, type, row) {
                            if (type === 'display') {
                                const MONTHLY_PERIOD = 2;
                                return row.type_id === MONTHLY_PERIOD 
                                    ? moment(row.start_date).format('MMMM yyyy')
                                    : `${moment(row.start_date).format('MM/DD/YYYY')} - ${moment(row.end_date).format('MM/DD/YYYY')}`;
                            }
        
                            return data;
                        },
                    },
                    {
                        data: 'minimum_work_hours',
                    },
                    {
                        data: 'total_availability_hours',
                    },
                    {
                        data: 'total_work_hours',
                        render: function (data, type, row) {
                            if (type === 'display') {
                                return `<span style="${row.total_work_hours < row.minimum_work_hours ? 'color:#fb0007;' : 'color:#02a756;'}">${data}</span>`;
                            }
        
                            return data;
                        },
                    },
                ],
                hideEmptyCols: true,
                createdRow: function (row, data) {
                    $(row).addClass(data.rowClass);
                }
            });
        }
    },
};
</script>

<style scoped>
.section-detail__buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
</style>
