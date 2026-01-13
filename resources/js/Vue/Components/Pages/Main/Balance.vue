<template>
    <section class="overall-balance">
        <div class="row total">
            <div class="col-5 total-sum">
                <div class="title">
                        <span>
                            На всех рублевых счетах
                        </span>
                    <a href="#" @click.prevent="updateBalance">
                        <!-- animation="spin" -->
                        <b-icon-arrow-repeat/>
                    </a>
                </div>
                <price-format-ui class="balance" :amount="$store.state.totalBalance"></price-format-ui>
            </div>
            <div class="col month-total">
                <div class="month-name">
                    {{ filterPeriod }}
                </div>
                <div class="month-transactions d-flex flex-row">
                    <div class="income">
                        <span class="type-name">
                            Приход
                        </span>
                        <price-format-ui class="sum" :amount="monthIncome" :show-plus="true"></price-format-ui>
                    </div>
                    <div class="expense">
                        <span class="type-name">
                            Расход
                        </span>
                        <price-format-ui class="sum" :amount="monthExpense" :show-plus="true"></price-format-ui>
                    </div>
                    <div class="total">
                        <span class="type-name">
                            Баланс
                        </span>
                        <price-format-ui class="sum" :amount="monthBalance" :show-plus="true"></price-format-ui>
                    </div>
                </div>
            </div>
        </div>

        <div class="bill-balance" :class="showBills ? 'opened' : ''">
            <transition name="slide">
                <div v-if="showBills" class="row bill-list">

                    <div v-for="account in $store.state.checkingAccounts" :class="account.type"
                         class="col-6 bill-item">

                        <div class="bill-icon"></div>
                        <div class="bill-desc">
                            <div class="name">{{ account.name }}, ₽</div>
                            <div class="num">{{ account.num }}</div>
                        </div>
                        <price-format-ui class="bill-sum" :amount="account.balance"></price-format-ui>
                    </div>

                </div>
            </transition>
            <footer>
                <a href="#" @click.prevent="showBills = !showBills">
                    <b-icon-chevron-up class="icon-left"></b-icon-chevron-up>
                    <span v-if="showBills">Скрыть счета</span>
                    <span v-else>Показать счета</span>
                </a>
            </footer>
        </div>
    </section>
</template>

<script>

/**
 * Компонент баланса по счетам
 */

/*
 * Здесь, думаю, нужно будет поработать над анимацией slide
 */

import {EventBus} from "../../../index";
import formats from "../../../mixins/formats";

export default {
    name: "Balance",
    mixins: [ formats ],
    props: {
        filters: null
    },

    data(){
        return {
            totalAmount: 0,
            monthIncome: 0,
            monthExpense: 0,
            currentMonth: new Date().getMonth(),

            showBills: true // показывать счета
        }
    },

    methods: {
        updateBalance(){
            EventBus.$emit('app:update');
        },


        updateBalances({income, expense}){
            this.monthIncome = income || 0
            this.monthExpense = expense || 0
        }
    },

    computed: {

        filterPeriod(){

            let period = this.filters?.filterPeriod;
            if(!period || !period[0] || !period[1] )
                return '';

            return this.periodString(period[0], period[1], {
                fullMonthPrefix: 'за '
            });
        },

        // баланс за месяц
        monthBalance(){
            return this.monthIncome - this.monthExpense;
        }
    },

    mounted(){
        EventBus.$on('updateMonthBalance', this.updateBalances)
    },

    destroy() {
        EventBus.$off('updateMonthBalance', this.updateBalances)
    }
}
</script>
