<template>
    <div class="graph__content loader-wrapper">
        <div class="loader loader__white-bg centered" v-if="loading">
            <b-spinner variant="primary" label="Загрузка"></b-spinner>
        </div>
        <div class="graph__insufficient-data-label" v-if="insufficientData && !loading">
            <span>Недостаточно данных для отображения</span>
        </div>
        <apexchart type="donut" :series="series" :options="options" height="300" width="80%"/>
    </div>
</template>

<script>
import Axios from '../../../../../mixins/axios';
import {EventBus} from "../../../../../index";
import moment from "moment";

export default {
    name: "GraphPie",
    mixins: [Axios],
    props: {
        filters: Object,
        type: {
            type: String,
            default: 'income'
        },
        category: {
            type: String,
            default: 'budget-items'
        }
    },

    data(){
        return {
            loading: false,
            seriesData: {
                income: [],
                expense: []
            },
            labelsData: {},
            options: {
                labels: [],
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        donut: {
                            size: '85%',
                            background: 'transparent',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 400,
                                    color: undefined,
                                    offsetY: 40
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 400,
                                    color: undefined,
                                    offsetY: -8,
                                    formatter: function (value) {
                                        let startVal = value;
                                        value = ~~`${value}`;
                                        value /= 1000;
                                        if(!value) return 0;

                                        if(startVal % 1)
                                            return startVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " ₽";

                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " тыс.";
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: false,
                                    label: 'Всего',
                                    fontSize: '16px',
                                    fontWeight: 400,
                                    color: '#808080',
                                    formatter: function (w) {
                                        let sum = w.globals.seriesTotals.reduce((a, b) => {
                                            return a + b
                                        }, 0);

                                        let startVal = sum;
                                        sum = ~~`${sum}`;
                                        sum /= 1000;
                                        if(!sum) return 0;

                                        if(startVal % 1)
                                            return startVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " ₽";

                                        return sum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " тыс.";
                                    }
                                }
                            }
                        },
                    }
                }
            },
        }
    },

    computed: {
        series(){
            if(this.type && this.seriesData[this.type])
                return this.seriesData[this.type];
            return [];
        },

        insufficientData() {
            return this.series.length < 1;
        }
    },

    methods: {
        getData(){
            this.loading = true;
            let url = '/ui/transaction-graph/pie/' + this.category;

            // prepare period
            if(this.filters?.filterPeriod && this.filters.filterPeriod.length > 1){
                let queryParams = {
                    'period-from': moment(this.filters.filterPeriod[0]).startOf('day').valueOf(),
                    'period-to': moment(this.filters.filterPeriod[1]).endOf('day').valueOf()
                }

                url += "?" + this.queryString(queryParams);
            }
            this.axiosGET(url).then(data => {
                this.seriesData = data.series || [];
                this.labelsData = data.labels || [];
            }).finally(() => {
                this.loading = false;
            });
        },

        updateLabels(){
            let options = Object.assign({}, this.options);
            if(this.type && this.labelsData[this.type])
                options.labels = this.labelsData[this.type];
            else
                options.labels = [];
            this.options = options;
        }
    },

    watch: {
        labelsData(){ this.updateLabels() },
        category(){
            this.getData()
        }
    },

    created(){
        this.getData();
        EventBus.$on('transactions:update', this.getData);
    },
    destroyed() {
        EventBus.$off('transactions:update', this.getData);
    }
}
</script>
