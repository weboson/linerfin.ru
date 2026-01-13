<template>
    <div class="top-header-wrapper reports reports__pnl">
        <div class="top-header" :class="topHeaderOffset">
            <div class="breadcrumbs-wrapper">
                <a href="#" class="back-link"
                   @click.prevent="$router.push('/reports')">
                    <b-icon-arrow-left class="icon-left"/>
                    Назад
                </a>
                <ul class="breadcrumbs">
                    <li>Отчёты</li>
                    <li>Отчёт о движении денежных средств</li>
                </ul>
            </div>
            <header class="title">
                <img src="/assets/images/icons/reports.svg">
                <span>Отчёт о движении денежных средств</span>
            </header>
        </div>



        <div class="container-fluid top-header__content">


            <div class="toolbar">
                <div class="left">
                    <div class="reports__calendar" :class="periodType + '-type'">
                        <button class="prev btn" @click="prevPeriod">
                            <b-icon-chevron-left/>
                        </button>
                        <div class="reports__calendar-content">
                            <span class="half-year" v-if="periodType === 'month'">
                                {{ halfYear === 1 ? 'I полугодие' : 'II полугодие'}}
                            </span>
                            {{ year }}
                        </div>
                        <button class="next btn" @click="nextPeriod">
                            <b-icon-chevron-right/>
                        </button>
                    </div>

                    <select-ui value-key="value" :options="periodTypes" v-model="periodType" class="period-type-select"
                               @change="updateData"
                               required/>

                </div>
                <div class="right">
                    <a @click.prevent
                       href="#" class="btn btn-outline-secondary disabled">
                        <b-icon-upload class="icon-left"/>
                        Экспорт
                    </a>
                </div>
            </div>


            <div>
                <div class="table-responsive loader-wrapper">
                    <div class="loader centered loader__white-bg" v-if="loading">
                        <b-spinner variant="primary" label="Загрузка"></b-spinner>
                    </div>
                    <table class="pnl-table fof-table">
                        <thead>
                        <tr><th v-for="th in tableHeader">{{ th }}</th></tr>
                        </thead>

                        <tbody>

                        <tr v-for="row in data" :class="row.type || ''">
                            <th>{{ row.name }}</th>
                            <td v-for="sum in row.data">
                                <price-format-ui :amount="sum"
                                                 :show-plus="row.type !== 'header balance'"
                                                 :define-class="row.type !== 'header balance'" />
                            </td>
                        </tr>
                        <!-- profitability -->
<!--                        <tr class="profitability-row">
                            <th>Рентабельность</th>
                            <td v-for="profitability in profitabilityStack"><price-format-ui :amount="profitability" show-plus define-class/></td>
                            <th><price-format-ui :amount="sumArray(profitabilityStack)" show-plus define-class/></th>
                        </tr>-->
                        <!-- end profitability -->


                        </tbody>
                    </table>
                </div>
            </div>


            <!--            <button class="btn btn-secondary" @click="getData">update</button>
                        <a href="#" @click.prevent="showDebug = !showDebug">toggle</a>
                        <transition name="slide">
                            <pre v-if="showDebug">{{ data }}</pre>
                        </transition>-->
        </div>
    </div>
</template>


<style lang="sass">
.reports .fof-table
    thead, tbody
        td, th
            &:nth-last-child(2)
                border-right: none

    tbody
        tr.header
            td, th
                background: lighten($c-blue-light, 24)
                border-top: 1px solid $c-grey-light
                border-bottom: 1px solid $c-grey-light

            &.balance
                th, td
                    background: lighten($c-green, 65%)
                    font-weight: bold
</style>

<script>
import moment from "moment";
import Axios from '../../../mixins/axios';
import PageOffsetMixin from "../../../mixins/page-offset";

export default {
    mixins: [Axios, PageOffsetMixin],
    data(){
        return {
            data: null,
            showDebug: false,
            loading: false,
            updateTimer: null,

            periods: [],


            // period config
            periodType: 'month',
            periodTypes: [
                {value: 'month', name: 'По месяцам'},
                {value: 'quarter', name: "По кварталам"}
            ],

            year: null,
            halfYear: null,

        }
    },

    computed: {

        proceedSum(){
            let x = 0;
            return this.proceeds.map(i=>x+=i, x=0).reverse()[0]
        },

        expensesPeriodSum(){
            let sumStack = [];
            this.expenses.forEach(expenseStack => {
                let data = expenseStack.data || [];
                data.forEach((expense, i) => {
                    if(sumStack[i] === null || sumStack[i] === undefined) sumStack[i] = 0;
                    sumStack[i] += expense;
                })
            })
            return sumStack;
        },

        profitStack(){
            let profitStack = [];
            this.proceeds.forEach((proceed, i) => {
                if(!this.expensesPeriodSum[i]) {
                    profitStack[i] = proceed;
                    return;
                }
                profitStack[i] = proceed + this.expensesPeriodSum[i];
            })

            return profitStack;
        },

        profitabilityStack(){
            let profitabilityStack = [];
            this.profitStack.forEach((profit, i) => {
                if(this.proceeds[i] === 0) {
                    profitabilityStack.push(0);
                    return;
                }
                profitabilityStack.push((profit / this.proceeds[i] * 100));
            });
            return profitabilityStack;
        },

        tableHeader(){
            if(!this.periods.length) return [];

            let tableHeader = [" "];

            if(this.periodType === "month") {
                this.periods.forEach(period => {
                    tableHeader.push(moment(period[0]).format('MMMM YYYY'));
                });
            }
            else{
                let quarters = ['', 'I', 'II', 'III', 'IV'];
                this.periods.forEach(period => {
                    let quarter = moment(period[0]).quarter();
                    tableHeader.push(quarters[quarter] + "." + this.year);
                })
            }
            // tableHeader.push("Итого")

            return tableHeader;
        },


    },

    methods: {
        getData(){
            let params = {
                period_type: this.periodType,
                year: this.year,
                half_year: this.halfYear
            };
            this.loading = true;
            this.axiosPOST('/ui/reports/fof', params).then(data => {
                this.data = data.data || [];
                this.periods = data.periods || [];
            }).finally(() => {
                this.loading = false;
            })
        },

        updateData(){
            clearTimeout(this.updateTimer);
            this.loading = true;
            this.updateTimer = setTimeout(this.getData, 500);
        },

        sumArray(array){
            let x = 0;
            return array.map(i=>x+=i, x=0).reverse()[0];
        },

        exportToXLS(){
            // https://habr.com/ru/post/353996/
        },

        prevPeriod(){
            this.updateData();
            let isQuarter = this.periodType === 'quarter';
            if(!isQuarter){
                if(this.halfYear > 1) {
                    this.halfYear = 1;
                    return;
                }
                this.halfYear = 2;
            }
            this.year--;
        },
        nextPeriod(){
            this.updateData();
            let isQuarter = this.periodType === 'quarter';
            if(!isQuarter){
                if(this.halfYear < 2) {
                    this.halfYear = 2;
                    return;
                }
                this.halfYear = 1;
            }
            this.year++;
        }
    },

    created(){
        let nowMoment = moment();
        this.year = nowMoment.format('YYYY');
        this.halfYear = Number.parseInt(nowMoment.format('MM')) > 5 ? 2 : 1;

        this.getData();
    }
}
</script>
