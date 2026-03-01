<template>
    <div class="table-type-1 transaction-list">
        <header>
            <div class="income">
                <span class="desc">
                    {{ incomeCount }} поступлений
                </span>
                <price-format-ui :amount="incomeSum" show-plus />
            </div>
            <div class="expense">
                <span class="desc">
                    {{ expenseCount }} списаний
                </span>
                <price-format-ui :amount="expenseSum * -1" show-plus />
            </div>
        </header>

        <!-- Transactions table -->
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
                    <!-- Новая колонка "Назначение платежа" -->
                    <div class="cell purpose">
                        <span>Назначение платежа</span>
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
                    <a href="#" @click.prevent="editTransaction" class="edit-btn">Редактировать</a>
                    <a href="#" @click.prevent class="translate-btn">Перевод</a>
                    <a href="#" @click.prevent class="remove-btn">Удалить</a>
                </div>
            </header>

            <transition name="fade-out">
                <div class="loader" v-if="loader">
                    <b-spinner variant="primary" label="Загрузка"></b-spinner>
                </div>
            </transition>

            <!-- Transaction -->
            <div v-for="transaction in transactions" v-if="transactions.length"
                class="table-type-1__table-row transaction-item" :class="transaction.type">

                <div class="table-type-1__table-row-header">
                    <!-- Checkbox -->
                    <div class="cell select">
                        <b-form-checkbox v-model="selectedTransactions" :key="transaction.id" :value="transaction.id" />
                    </div>

                    <!-- Transaction Date -->
                    <div class="cell date" @click.prevent="toggleTransactionDetails(transaction)">
                        {{ transaction.date ? formatDate(transaction.date) : "" }}
                    </div>

                    <!-- Amount -->
                    <div class="cell sum" @click.prevent="toggleTransactionDetails(transaction)">
                        <price-format-ui
                            :amount="transaction.type === 'expense' ? transaction.amount * -1 : transaction.amount"
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

                    <!-- Purpose column (Назначение платежа) -->
                    <div class="cell purpose" @click.prevent="toggleTransactionDetails(transaction)">
                        <div class="purpose-text" v-if="transaction.purpose">
                            {{ truncatePurpose(transaction.purpose) }}
                        </div>
                        <span v-else class="text-muted">—</span>
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
                        <div v-for="checkingAccount in getCheckingAccount(transaction)" class="checking-account__item">
                            {{ checkingAccount.name }}
                        </div>
                    </div>
                </div>

                <!-- Transaction details (разворачиваемая часть) -->
                <div class="table-type-1__table-row-content" v-if="showDetails === transaction.id">
                    <div>
                        <!-- Показываем назначение платежа в деталях, если есть -->
                        <template v-if="transaction.purpose">
                            <p><strong>Назначение платежа</strong></p>
                            <p class="purpose-detail">{{ transaction.purpose }}</p>
                        </template>

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
                            <b-icon-chevron-up class="icon-left" />
                            <span>Скрыть</span>
                        </a>
                    </footer>
                </div>
            </div>

            <div v-if="!transactions.length" class="table-type-1__nothing">
                Ничего не найдено
            </div>
        </div>

        <footer>
            <div class="pagination">
                <template v-if="pages.length > 1">
                    <a v-for="pageNum in pages" href="#" class="pageLink" v-on:click.prevent="choosePage(pageNum)"
                        :class="pageNum.toString() === page.toString() ? 'current' : ''">
                        {{ pageNum }}
                    </a>
                    <a v-if="morePages" href="#" class="pageLink show-more">Еще {{ morePages }}</a>
                </template>
            </div>
            <div class="per-page-count">
                <span>
                    Количество записей
                </span>
                <select-ui :options="per_page_counts" v-model="perPageCount" :required="true" @input="choosePPC" />
            </div>
        </footer>
    </div>
</template>

<script>
import utils from "../../../mixins/utils";
import paginator from "../../../mixins/paginator";
import { EventBus } from "../../../index";
import axios from "../../../mixins/axios";

import IncomeModal from "./modals/CreateIncome";
import ExpenseModal from "./modals/CreateExpense";
import TransferModal from "./modals/CreateTransfer";

export default {
    name: "Transactions",
    mixins: [paginator, utils, axios],

    props: {
        filters: {
            type: Object
        }
    },

    data() {
        return {
            transactions: [],
            allSelected: false,

            selectedTransactions: [],
            showDetails: null,

            loader: false,

            // Counters
            incomeCount: 0,
            incomeSum: 0,

            expenseCount: 0,
            expenseSum: 0
        }
    },

    methods: {
        // метод для обрезки длинного "назначения платежа"
        truncatePurpose(purpose) {
            if (!purpose) return '';
            if (purpose.length > 50) {
                return purpose.substring(0, 60) + '...';
            }
            return purpose;
        },

        getTransactions() {
            this.loader = true;

            let params = {
                page: this.page,
                ppc: this.perPageCount
            };

            if (this.filters)
                params.filters = Object.assign({}, this.filters);

            let period = this.filters?.filterPeriod;
            if (period && period.length > 1) {
                params.filters.filterPeriod = [
                    period[0] ? period[0].getTime() / 1000 : null,
                    period[1] ? period[1].getTime() / 1000 : null,
                ];
            }

            params = this.queryString(params);

            this.axiosGET('/ui/transactions?' + params).then(response => {
                this.loader = false;

                this.transactions = response.transactions || [];
                this.lastPage = Number.parseInt(response.last_page || 1);

                this.incomeCount = response.income_count || 0;
                this.incomeSum = response.income_sum || 0;
                this.expenseCount = response.expense_count || 0;
                this.expenseSum = response.expense_sum || 0;

                EventBus.$emit('updateMonthBalance', {
                    income: this.incomeSum,
                    expense: this.expenseSum
                });
            });
        },

        editTransaction() {
            if (!this.existSelect) return;

            let transaction_id = this.selectedTransactions.shift();
            let transaction = this.transactions.find(t => t.id === transaction_id);

            if (!transaction) return;

            let handlerComponent;
            if (transaction.type === 'expense')
                handlerComponent = ExpenseModal;
            else if (transaction.type === 'income')
                handlerComponent = IncomeModal;
            else if (transaction.type === 'transfer')
                handlerComponent = TransferModal;
            else {
                this.errorNotify('Неверный тип операции', "Редактирование операции");
                return;
            }

            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                component: handlerComponent,
                props: { data: transaction },
                onClose: () => { },
                onDismiss: () => { }
            });
        },

        choosePage(page) {
            this.page = page;
            this.getTransactions();
        },

        choosePPC() {
            this.getTransactions();
        },

        toggleTransactionDetails(transaction) {
            let id = transaction.id;
            this.showDetails = this.showDetails === id ? null : id;
        },

        getCheckingAccount(transaction) {
            switch (transaction.type) {
                case 'income':
                    if (transaction.to_checking_account)
                        return [transaction.to_checking_account];
                    break;
                case 'expense':
                    if (transaction.from_checking_account)
                        return [transaction.from_checking_account];
                    break;
                case 'transfer':
                    let ca = [];
                    if (transaction.from_checking_account)
                        ca.push(transaction.from_checking_account);
                    if (transaction.to_checking_account)
                        ca.push(transaction.to_checking_account);
                    return ca;
            }
            return [];
        }
    },

    watch: {
        allSelected: function () {
            if (this.allSelected) {
                this.selectedTransactions = this.transactions.map(t => t.id);
            } else {
                this.selectedTransactions = [];
            }
        },

        selectedTransactions: function () {
            if (this.isAllSelected)
                this.allSelected = true;
            else if (!this.selectedTransactions || !this.selectedTransactions.length)
                this.allSelected = false;
        }
    },

    computed: {
        isAllSelected() {
            return this.selectedTransactions.length === this.transactions.length;
        },

        isPartitionSelected() {
            return !!(this.selectedTransactions.length && !this.isAllSelected);
        },

        existSelect() {
            return !!this.selectedTransactions.length;
        }
    },

    created() {
        EventBus.$on('transactions:update', () => {
            this.getTransactions();
        });
    },

    beforeDestroy() {
        EventBus.$off('transactions:update');
    }
}
</script>

<style scoped>
/* Стили для колонки "Назначение платежа" - только самое необходимое */
/* остальные стили в resources/sass/pages/main-page/transactions.sass */
.cell.purpose {
    width: calc(32% - 135px);
}

.purpose-text {
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.4;
    word-break: break-word;
}

.purpose-detail {
    background-color: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
    margin: 10px 0 15px 0;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.95rem;
}
</style>