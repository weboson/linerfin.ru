<template>
    <div class="graph__content">
        <div class="loader loader__white-bg centered" v-if="loading">
            <b-spinner variant="primary" label="Загрузка"></b-spinner>
        </div>
        <div class="graph__insufficient-data-label" v-if="insufficientData && !loading">
            <span>Недостаточно данных для отображения</span>
        </div>
        <apexchart type="line" :series="series" :options="options" height="300"/>
    </div>
</template>

<script>

import Axios from '../../../../../mixins/axios';
import {EventBus} from "../../../../../index";
const ApexRu = require("apexcharts/dist/locales/ru.json");
import moment from 'moment';

import Utils from '../../../../../mixins/utils';


export default {
    name: "GraphLine",
    mixins: [Axios, Utils],
    props: {
        filters: Object
    },
    data(){
        return {
            loading: false,
            graphData: [],
            graphFutureData: [],
            options: {
                legend: {
                    show: false
                },

                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeUTC: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (value, e) {
                            let startVal = value;
                            value = ~~`${value}`;
                            value /= 1000;
                            if(!value) return 0;

                            if(startVal % 1)
                                return startVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " ₽";

                            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " тыс.";
                        }
                    },
                },
                colors: ['#229DCA', '#169D14'],
                stroke: {
                    width: [4, 4],
                    curve: 'smooth'
                },

                // annotations
                annotations: {
                    xaxis: [
                        {
                            x: moment().endOf('day').toDate().getTime(),
                            borderColor: "#E0E0E0",
                            label: {
                                borderColor: "#FCFCFC",
                                style: {
                                    color: "#9f9f9f",
                                    background: "#FCFCFC"
                                },
                                text: 'Сегодня'
                            }
                        }
                    ]
                },

                /*fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.9,
                        stops: [0, 90, 100]
                    }
                },*/

                // Grid style
                grid: {
                    borderColor: "#efefef"
                },


                // Localization
                chart: {
                    locales: [ApexRu],
                    defaultLocale: "ru",
                    toolbar: {
                        show: false,
                        autoSelected: 'pan'
                    }
                },
            },


            // demo series
            demoSeries: [
                { data: [], name: 'Прогноз'},
                { data: [], name: 'Баланс'},
            ]
        }
    },


    computed: {
        series(){

            // demo data
            if(this.insufficientData)
                return this.demoSeries;

            return [
                { data: this.graphFutureData, name: 'Прогноз' },
                { data: this.graphData, name: 'Баланс' }
            ];
        },

        insufficientData(){
            return this.graphData.length < 2 && this.graphFutureData.length < 2;

        }
    },

    methods: {
        getData(){

            let url = '/ui/transaction-graph';

            // prepare period
            if(this.filters?.filterPeriod && this.filters.filterPeriod.length > 1){
                let periods = this.filters.filterPeriod;
                if(!!periods[0] && !!periods[1]){
                    let queryParams = {
                        'period-from': moment(this.filters.filterPeriod[0]).startOf('day').valueOf(),
                        'period-to': moment(this.filters.filterPeriod[1]).endOf('day').valueOf()
                    }
                    url += "?" + this.queryString(queryParams);
                }
            }

            this.loading = true;

            this.axiosGET(url).then(data => {

                let transactions = data?.transactions,
                    now = new moment().endOf('day').valueOf(),
                    finalData = [],
                    finalFutureData = [];

                let lastAmount = null,
                    lastDay = null;


                // [debug part]
                /*if(transactions) transactions.forEach(transaction => {

                    let date = new Date(transaction.date).getTime(),
                        balance = parseFloat(transaction.total_balance);

                    finalData.push({x: date, y: balance});
                });

                this.graphData = finalData;

                return;*/

                if(transactions) transactions.forEach(transaction => {

                    if(!transaction.date || transaction.total_balance === null || isNaN(transaction.total_balance))
                        return;

                    let date = new Date(transaction.date).getTime(),
                        balance = parseFloat(transaction.total_balance);

                    // push balance chains
                    if(date <= now)
                        finalData.push({x: date, y: balance});
                    else {
                        finalFutureData.push({x: date, y: balance});
                    }

                    // update last amount
                    lastAmount = balance;
                });

                // start point of future data
                if(finalData.length && finalFutureData.length) {
                    let lastNode = Object.assign({}, finalData.slice(-1)[0]);
                    console.log({lastNode});
                    lastNode['x'] = (lastNode?.x || 0) + 1000;
                    finalFutureData.unshift(lastNode);
                }

                this.graphData = finalData;
                this.graphFutureData = finalFutureData;

            }).finally(() => {
                this.loading = false;
            })
        },


        generateDemoData(){
            let periodFrom, periodTo;
            if(this.filters?.filterPeriod && this.filters.filterPeriod.length > 1) {
                periodFrom = moment(this.filters.filterPeriod[0]).startOf('day');
                periodTo = moment(this.filters.filterPeriod[1]).endOf('day');
            }
            else{
                periodFrom = moment().startOf('month');
                periodTo = moment().endOf('month');
            }

            let generatedData = [];

            for(let i = 0; i < 32; i++) // limited
            {
                if(periodFrom.valueOf() >= periodTo.valueOf())
                    break;

                generatedData.push([periodFrom.valueOf(), this.randomInteger(100000, 400000)]);
                periodFrom.add(1, 'days');
            }

            this.demoSeries = [
                { data: [], name: 'Demo 2' },
                { data: generatedData, name: 'Demo' },
            ];
        }

    },

    created(){
        this.generateDemoData();
        this.getData();
        EventBus.$on('transactions:update', this.getData);
    },
    destroyed() {
        EventBus.$off('transactions:update', this.getData);
    }
}
</script>
