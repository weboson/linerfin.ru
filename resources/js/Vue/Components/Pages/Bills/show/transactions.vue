<template>
    <section class="transactions">
        <!-- Transactions table -->
        <div class="table-type-1 transaction-list">
            <header></header>

            <div class="table-type-1__table transaction-list__table loader-wrapper">

                <header :class="existSelect ? 'exists-selected' : ''">
                    <div class="cell select">
                        <b-form-checkbox v-model="allSelected" :indeterminate="isPartitionSelected" />
                    </div>
                    <template v-if="!existSelect">
                        <div class="cell date">
                            <span>Дата</span>
                            <b-icon-sort-down class="icon-right"></b-icon-sort-down>
                        </div>
                        <div class="cell sum">
                            <span>Сумма</span>
                        </div>
                        <div class="cell budget-item">
                            <span>Статья и описание</span>
                        </div>
                        <div class="cell project">
                            <span>Проект</span>
                        </div>
                        <div class="cell counterparty">
                            <span>Контрагенты</span>
                        </div>
                        <div class="cell checking-account">
                            <span>Счёт</span>
                        </div>
                    </template>
                    <div v-else class="cell select-toolbar">
                        <span class="select-count">{{ selectedTransactions.length }}</span>
                        <!-- <a href="#" @click.prevent="editTransaction" class="edit-btn">Редактировать</a> -->
                        <a href="#" @click.prevent="removeTransactions" class="remove-btn">Удалить</a>
                    </div>
                </header>

                <transition name="fade-out">
                    <div class="loader" v-if="loader">
                        <b-spinner variant="primary" label="Загрузка"></b-spinner>
                    </div>
                </transition>


                <div v-for="transaction in transactions" v-if="transactions.length"
                     class="table-type-1__table-row transaction-item"
                     :class="transaction.type">

                    <div class="table-type-1__table-row-header">

                        <!-- Checkbox -->
                        <div class="cell select">
                            <b-form-checkbox v-model="selectedTransactions" :key="transaction.id" :value="transaction.id"/>
                        </div>

                        <!-- Transaction Date -->
                        <div class="cell date" @click.prevent="toggleTransactionDetails(transaction)">
                            {{ transaction.date ? formatDate(transaction.date) : "" }}
                        </div>

                        <!-- Amount -->
                        <div class="cell sum" @click.prevent="toggleTransactionDetails(transaction)">
                            <price-format-ui :amount="transaction.type === 'expense' ? transaction.amount * -1 : transaction.amount"
                                             :show-plus="transaction.type === 'income'"></price-format-ui>
                        </div>

                        <!-- Budget item -->
                        <div class="cell budget-item" @click.prevent="toggleTransactionDetails(transaction)">
                            <div class="budget-item" v-if="transaction.budget_item">
                                {{ transaction.budget_item.name }}
                            </div>
                            <div class="desc" v-if="transaction.description">
                                {{ transaction.description }}
                            </div>
                        </div>

                        <!-- Project -->
                        <div class="cell project" @click.prevent="toggleTransactionDetails(transaction)">
                            {{ transaction.project ? transaction.project.name : '' }}
                        </div>

                        <!-- Counterparty -->
                        <div class="cell counterparty" @click.prevent="toggleTransactionDetails(transaction)">
                            <template v-if="transaction.counterparty">
                                <div class="counterparty">
                                    {{ transaction.counterparty.name }}
                                </div>
                                <div class="category" v-if="transaction.counterparty.category">
                                    {{ transaction.counterparty.category.name }}
                                </div>
                            </template>
                        </div>

                        <!-- Checking account -->
                        <div class="cell checking-account" @click.prevent="toggleTransactionDetails(transaction)">
                            <div v-for="checkingAccount in getCheckingAccount(transaction)"  class="checking-account__item">
                                {{ checkingAccount.name }}
                            </div>
                        </div>

                    </div>
                    <div class="table-type-1__table-row-content" v-if="showDetails === transaction.id">
                        <div>
                            <template v-if="transaction.counterparty">
                                <p><strong>Реквизиты контрагента</strong></p>
                                <table>
                                    <tr>
                                        <td class="text-secondary">ИНН</td>
                                        <td>{{ transaction.counterparty.inn }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">КПП</td>
                                        <td>{{ transaction.counterparty.kpp }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Расчетный счёт</td>
                                        <td>{{ transaction.counterparty.checking_account }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">БИК Банка</td>
                                        <td>{{ transaction.counterparty.bank_bik }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Банк</td>
                                        <td>{{ transaction.counterparty.bank }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary">Кор. счёт Банка</td>
                                        <td>{{ transaction.counterparty.bank_checking_account }}</td>
                                    </tr>
                                </table>
                            </template>

                            <p><strong>История операций</strong></p>
                            <p>
                                    <span class="text-secondary" v-if="transaction.created_at">
                                        {{ formatDate(transaction.created_at, true) }}
                                    </span>
                                создана
                            </p>
                            <p>
                                    <span class="text-secondary" v-if="transaction.updated_at">
                                        {{ formatDate(transaction.updated_at, true) }}
                                    </span>
                                изменена
                            </p>


                        </div>

                        <footer>
                            <a href="#" @click.prevent="toggleTransactionDetails(transaction)">
                                <b-icon-chevron-up class="icon-left"/>
                                <span>Скрыть</span>
                            </a>
                        </footer>
                    </div>
                </div>

                <div v-if="!transactions.length" class="table-type-1__nothing">
                    Ничего не найдено
                </div>
            </div>
        </div>
    </section>
</template>

<script>

import Utils from "~@vueMixins/utils";
import AxiosMix from "~@vueMixins/axios";
import PopupMix from "~@vueMixins/popup-mix";
import {EventBus} from "~@vue/index";

export default {
    name: "transactions",
    mixins: [Utils, PopupMix, AxiosMix],

    data(){
        return {
            allSelected: false,
            selectedTransactions: [],
        }
    },

    props: {
        transactions: Array,
        loader: Boolean
    },



    methods: {
        toggleTransactionDetails(){},
        getCheckingAccount(transaction){
            let accounts = [];
            if(transaction.to_checking_account)
                accounts.push(transaction.to_checking_account);
            return accounts
        },
        showDetails(){},


        removeTransactions(){
            if(!this.selectedTransactions.length)
                return this.notify('Не выбрано ни одной операции для удаления')

            const selected = this.selectedTransactions;
            this.confirmPopup('Подтвердите удаление выбранных операций')
                .then(() => {
                    this.selectedTransactions = [];
                    this.removeTransactionsHandler(selected)
                })
                .catch(() => { /* nothing*/ })
        },

        removeTransactionsHandler(selected){

            if(!selected || !Array.isArray(selected))
                selected = [];

            this.$emit('load-status', true);

            this.axiosPOST('/ui/transactions/remove', {
                ids: selected.join(',')
            })
                // complete
                .then(() => {
                    EventBus.$emit('app:update');
                    this.$emit('bill:update');
                })

                // on error
                .catch(() => {
                    this.errorNotify('Не удалось отменить выбранные операции')
                })

                .finally(() => {
                    this.$emit('load-status', false);
                })
        }
    },

    computed: {
        // If all selected
        isAllSelected(){
            if(this.selectedTransactions.length === this.transactions.length)
                return true;
            return false;
        },

        // If partition selected
        isPartitionSelected(){
            if(this.selectedTransactions.length && !this.isAllSelected)
                return true;
            return false;
        },

        // If select exists
        existSelect(){
            return !!this.selectedTransactions.length;
        }
    },

    // Watchers
    watch: {


        allSelected: function(){
            if(this.allSelected){
                this.selectedTransactions = [];
                for(let option of this.transactions){
                    this.selectedTransactions.push(option.id);
                }
            }
            else{
                this.selectedTransactions = [];
            }
        },


        selectedTransactions: function(){
            if(this.isAllSelected)
                this.allSelected = true;
            else if(!this.selectedTransactions || !this.selectedTransactions.length)
                this.allSelected = false;
        }
    }

}
</script>
