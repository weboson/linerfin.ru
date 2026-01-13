<template>
    <section class="transactions-toolbar">
        <div class="toolbar">
            <div class="left">
                <a href="#" class="btn btn-primary btn-150"
                   @click.prevent="incomePopup">
                    <b-icon-plus class="icon-left"></b-icon-plus>
                    <span>Приход</span>
                </a>
                <!-- [removed] <a href="#" class="btn btn-primary"
                   @click.prevent="transferPopup">
                    <b-icon-arrow-left-right class="icon-left"></b-icon-arrow-left-right>
                    <span>Перевод</span>
                </a>-->
                <a href="#" class="btn btn-primary btn-150"
                   @click.prevent="expensePopup">
                    <b-icon-dash class="icon-left"></b-icon-dash>
                    <span>Расход</span>
                </a>
            </div>
            <div class="right">
                <a href="#" class="btn btn-outline-secondary" @click.prevent="plug">
                    <b-icon-download class="icon-left"></b-icon-download>
                    <span>Импорт</span>
                </a>
                <a href="#" class="btn btn-outline-secondary" @click.prevent="plug">
                    <b-icon-upload class="icon-left"></b-icon-upload>
                    <span>Экспорт</span>
                </a>
            </div>

        </div>

        <!-- Filters -->
        <div class="toolbar filter">
            <div class="content">
                <form class="filters-form" @submit.prevent>
                    <div class="filter-form-row">
                        <div class="toolbar__period-picker">
                            <date-picker type="date" input-class="form-control"
                                         class="w-100" v-model="filterValues.filterPeriod"
                                         :formatter="momentFormatter"
                                         :range="true" :shortcuts="filterPeriodShortcuts"
                                         @change="emitFilters" ref="periodDatePicker"
                                         @open="showFakePlaceholder = false"
                                         @close="showFakePlaceholder = true"
                                         range-separator=" - "/>
                            <div class="fake-placeholder" v-if="fakePlaceholder">
                                <div class="fake-placeholder__content" @click="$refs.periodDatePicker.focus()">
                                    {{ fakePlaceholder }}
                                </div>
                                <div class="fake-placeholder__actions" v-if="isMonthPeriod">
                                    <button @click="prevMonth"><b-icon-chevron-left/></button>
                                    <button @click="nextMonth"><b-icon-chevron-right/></button>
                                </div>
                            </div>
                        </div>

                        <select-ui v-model="filterValues.type"
                                   :options="transactionTypes"
                                   placeholder="Все операции"
                                   @input="emitFilters"
                                   el-class="form-control" class="w-100"/>

                        <input v-model="filterValues.search" type="text"
                               @keyup="emitFilters"
                               class="form-control" placeholder="Контрагент, банк, статья, проект">

                        <a @click.prevent="toggleFilters"
                           href="#" class="show-filters-btn"
                           :class="showAllFilters ? 'opened' : ''">
                            <b-icon-sliders class="icon-left"></b-icon-sliders>
                            <span>Фильтры</span>
                        </a>
                    </div>

                    <transition name="slide">
                        <filter-component v-if="showAllFilters" v-model="filterValues" @input="emitFilters" @close="toggleFilters"/>
                    </transition>

                </form>
            </div>
        </div>
    </section>
</template>

<script>
import moment from "moment";
import IncomeModal from "../modals/CreateIncome";
import CreateExpense from "../modals/CreateExpense";
import CreateTransfer from "../modals/CreateTransfer";
import FilterComponent from "./Filter";
import Formats from '../../../../mixins/formats';

export default {
    name: "Toolbar",
    components: { FilterComponent },
    mixins: [Formats],
    props: {
        value: Object
    },
    data(){
        return {

            showAllFilters: false, // show filters
            showFakePlaceholder: true,

            // Значения фильтра
            filterValues: {
                filterPeriod: [],
                type: null,
                search: ''
            },

            // Пресеты для выбора периода
            filterPeriodShortcuts: [
                { text: "За все время", onClick: () => null },
                {
                    text: "Сегодня", onClick: () => {
                        let now = new Date;
                        return [moment(now).startOf('day').toDate(), moment(now).endOf('day').toDate() ];
                    }
                },
                {
                    text: "Вчера", onClick: () => {
                        let now = new Date;
                        now.setDate(now.getDate() - 1);
                        return [moment(now).startOf('day').toDate(), moment(now).endOf('day').toDate() ];
                    }

                },
                {
                    text: "Неделя", onClick: () => {
                        let now = new Date;
                        return [moment(now).startOf('week').toDate(), moment(now).endOf('week').toDate() ];
                    }
                },
                {
                    text: "Месяц", onClick: () => {
                        let now = new Date;
                        return [moment(now).startOf('month').toDate(), moment(now).endOf('month').toDate() ];
                    }
                },
                {
                    text: "Квартал", onClick: () => {
                        let now = new Date;
                        return [moment(now).startOf('quarter').toDate(), moment(now).endOf('quarter').toDate() ];
                    }
                },
                {
                    text: "Год", onClick: () => {
                        let now = new Date;
                        return [moment(now).startOf('year').toDate(), moment(now).endOf('year').toDate() ];
                    }
                }
            ],

            // Moment.js [unused]
            momentFormatter: {
                //[optional] Date to String
                stringify: (date) => {
                    return date ? moment(date).format('DD.MM.YYYY') : ''
                },
                //[optional]  String to Date
                parse: (value) => {
                    return value ? moment(value).toDate() : null
                },
                //[optional] getWeekNumber
                getWeek: (date) => {
                    return // a number
                }
            },

            // Типы транзакций
            transactionTypes: [
                { id: 'income', name: 'Приход' },
                { id: 'transfer', name: 'Перевод' },
                { id: 'expense', name: 'Расход' }
            ]
        }
    },



    computed: {

        period(){
            return this.value?.filterPeriod || null
        },

        periodFrom(){
            return this.period && this.period.length === 2 ? this.period[0] : null;
        },

        periodTo(){
            return this.period && this.period.length === 2 ? this.period[1] : null;
        },

        fakePlaceholder(){
            if(!this.showFakePlaceholder) return null;

            if(!this.periodFrom || !this.periodTo)
                return '';

            return this.periodString(this.periodFrom, this.periodTo, {
                useCalendar: true
            });
        },

        isMonthPeriod(){
            return this.isPeriod('month');
        },

        isWeekPeriod(){
            return this.isPeriod('week');
        },

        isYearPeriod(){
            return this.isPeriod('year');
        }
    },



    methods: {

        isPeriod(periodType){
            if(!~['week', 'year', 'day', 'month', 'quarter'].indexOf(periodType)){
                console.error('period type ' + periodType + 'invalid');
                return false;
            }

            if(!this.periodFrom || !this.periodTo)
                return false;

            let momentFrom = moment(this.periodFrom);
            let momentTo = moment(this.periodTo);

            return momentFrom.valueOf() === momentFrom.startOf(periodType).valueOf()
                && momentTo.valueOf() === momentFrom.endOf(periodType).valueOf();
        },

        nextMonth(){
            if(!this.isMonthPeriod)
                return;

            const nextMonth = moment(this.periodTo).add(3, 'days');

            let filterValues = Object.assign({}, this.filterValues, {
                filterPeriod: [
                    nextMonth.startOf('month').toDate(),
                    nextMonth.endOf('month').toDate()
                ]
            });

            this.filterValues = filterValues;
            this.emitFilters()
        },

        prevMonth(){
            if(!this.isMonthPeriod)
                return;

            const prevMonth = moment(this.periodFrom).subtract(3, 'days');

            let filterValues = Object.assign({}, this.filterValues, {
                filterPeriod: [
                    prevMonth.startOf('month').toDate(),
                    prevMonth.endOf('month').toDate()
                ]
            });

            this.filterValues = filterValues;
            this.emitFilters()
        },

        emitFilters(){
            this.$emit('input', this.filterValues);
        },

        incomePopup(){
            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                component: IncomeModal,

                onClose: (data) => {
                    console.log('Data received from the vuedal instance', data);
                },

                onDismiss() {
                    console.log('The user dismissed the modal');
                }
            });
        },

        expensePopup(){
            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                component: CreateExpense,

                onClose: (data) => {
                    console.log('Data received from the vuedal instance', data);
                },

                onDismiss() {
                    console.log('The user dismissed the modal');
                }
            });
        },

        transferPopup(){
            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                component: CreateTransfer,

                onClose: (data) => {
                    console.log('Data received from the vuedal instance', data);
                },

                onDismiss() {
                    console.log('The user dismissed the modal');
                }
            });
        },

        applyChanges(data){
            this.filterValues = Object.assign({}, this.filterValues, data);
        },

        toggleFilters(){
            this.showAllFilters = !this.showAllFilters;
        },

        plug(){
            alert('Раздел в разработке');
        }
    },


    created(){
        // get current month
        let now = new Date,
            startMonth, endMonth;

        // for demo-account
        if(this.$store.getters.isDemo){
            startMonth = moment().subtract(1, 'months').startOf('month').toDate();
            endMonth = moment().subtract(1, 'months').endOf('month').toDate();
        }

        // for default account
        else {
            startMonth = moment([now.getFullYear(), now.getMonth()]).toDate();
            endMonth = moment(now).endOf('month').toDate();
        }

        this.filterValues.filterPeriod = [startMonth, endMonth];
        this.emitFilters();
    }
}
</script>
