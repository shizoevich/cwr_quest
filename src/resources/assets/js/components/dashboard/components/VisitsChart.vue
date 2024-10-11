<template>
    <div class="panel panel-default panel-visits-chart">
        <div class="panel-heading">
            <h4>Visits</h4>
        </div>
        <div class="panel-body">
            <div class="visits-chart-wrapper" >
                <div class="visits-chart" style="width:75%;">
                    <canvas id="visits-chart" style="height:350px"></canvas>
                </div>
                <div class="visits-chart-total" v-if="total">
                    <h1>{{total.visits}}</h1>
                    <p>TOTAL VISITS</p>
                    <h1>
                        {{getFormattedMoney(total.pay, false)}}
                    </h1>
                    <p>TOTAL PAY</p>
                </div>

                <div class="chart-panel-loader-container" v-show="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PatientBalance from '../../../mixins/patient-balance';
    export default {

        data() {
            return {
                total: null,
                loading: false,
            };
        },

        mixins: [PatientBalance],

        mounted() {
            this.loading = true;
            this.$store.dispatch('getVisitsDatasetForChart').then(response => {
                this.loading = false;
                if(response.status === 200) {
                    this.total = response.data.total;
                    let areaChartCanvas = $('#visits-chart').get(0).getContext('2d')

                    let chartData = {
                        labels  : response.data.labels,
                        datasets: [
                            {
                                backgroundColor: 'rgba(48, 151, 209, 1)',
                                data: response.data.data,
                            },
                        ]
                    };

                    let chartOptions = {
                        datasetFill: true,
                        responsive: true,
                        legend: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: 'Sales: ' + response.data.date_from + ' - ' + response.data.date_to,
                            fontSize: 16,
                            fontFamily: 'Arial',
                            fontStyle: 'bold',
                            fontColor: '#000000',
                        },
                        scales: {
                            xAxes: [{
                                ticks: {
                                    beginAtZero: true
                                },
//                                gridLines: {
//                                    display: false
//                                },
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
//                                    stepSize: 1,
                                },
//                                gridLines: {
//                                    display: false
//                                },
                            }],
                        }
                    };

                    let areaChart = new Chart(areaChartCanvas, {
                        type: 'line',
                        data: chartData,
                        options: chartOptions,
                    });
                }
            });
        },
    }
</script>

<style scoped>
    .panel-visits-chart .panel-heading {
        border-bottom: 0;
    }
    .panel-visits-chart .panel-heading h4 {
        margin-top: 0;
        font-weight: bold;
    }
</style>