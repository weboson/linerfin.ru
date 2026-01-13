<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            {{ data ? 'Редактирование расхода' : 'Расход' }}
        </div>
        <div class="vuedal-content">

<!--            <div class="form-group">
                <b-form-checkbox
                    v-model="plannedOperation">
                    Сделать плановой операцией
                </b-form-checkbox>
            </div>-->

            <div class="form-group">
                <label>Сумма</label>
                <div class="input-group">
                    <input type="number" class="form-control" v-model="amount">
                    <div class="input-group-append">
                        <select-ui v-model="currency" :options="currencies" :required="true" el-class="form-control"></select-ui>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Счет</label>
                <select-ui v-model="checkingAccount"
                           :required="true"
                           el-class="form-control"
                           class="d-block"
                           :options="$store.state.checkingAccounts"
                           subtitle-key="num"
                           placeholder="Выберите счёт"
                           right-title-key="balance" right-title-type="price"/>
            </div>


            <template v-if="plannedOperation">
                <div class="form-group">
                    <label>Дата первого поступления</label>
                    <date-picker v-model="dateFirstIncome" type="date" class="d-block w-100"
                                 format="DD.MM.YYYY"
                                 input-class="form-control" placeholder="Выбрать дату"></date-picker>
                </div>
                <div class="form-group">
                    <label>Дата последнего поступления</label>
                    <date-picker v-model="dateLastIncome" type="date" class="d-block w-100"
                                 format="DD.MM.YYYY"
                                 input-class="form-control" placeholder="Выбрать дату"></date-picker>
                </div>
                <div class="form-group">
                    <label>Периодичность</label>
                    <select-ui v-model="periodicity" :required="true"
                               class="d-block" el-class="form-control"
                               :options="periodicityStack" value-key="value"/>
                </div>
            </template>

            <template v-else>
                <div class="form-group">
                    <label>Дата</label>
                    <date-picker v-model="date" type="datetime" class="d-block w-100"
                                 format="DD.MM.YYYY HH:mm"
                                 input-class="form-control" placeholder="Выбрать дату"></date-picker>
                </div>
            </template>


            <div class="form-group">
                <label>Контрагент</label>
                <input-autocomplete v-model="counterparty_name" type="counterparty"
                                    placeholder="Введите ИНН или название для поиска"
                                    title-key="value" :subtitle-key="item => 'ИНН ' + item.data.inn"
                                    strict-mod chevron def-placeholder required
                                    @choose="chooseCounterparty"/>
            </div>

            <div class="form-group">
                <label>Направление</label>
                <input-autocomplete type="project" v-model="project_name" strict-mod chevron def-placeholder
                                    @choose="chooseProject"
                                    placeholder="Выбрать направление"
                                    value-key="id" title-key="name"/>
                <a href="#" @click.prevent="projectsPopup" class="form-action">
                    <b-icon-plus></b-icon-plus>
                    <span>Добавить направление</span>
                </a>
            </div>

            <div class="form-group">
                <label>Статья</label>
                <input-autocomplete type="budget-item-expense" strict-mod def-placeholder chevron
                                    v-model="budget_item_name" value-key="id" title-key="name"
                                    @choose="chooseBudgetItem"
                                    placeholder="Выбрать статью"/>
                <a href="#" @click.prevent="budgetItemsPopup" class="form-action">
                    <b-icon-plus></b-icon-plus>
                    <span>Добавить свою статью</span>
                </a>
            </div>

            <div class="form-group">
                <label>Описание</label>
                <textarea v-model="description" class="form-control"
                          placeholder="Предоплата за..."></textarea>
            </div>




        </div>
        <modal-buttons v-on:save="save" v-on:cancel="cancel"></modal-buttons>
    </div>
</template>

<script>

import Mixin from "./mixin";

export default {
    name: "CreateExpense",
    mixins: [ Mixin ],
    data(){
        return {
            type: 'expense'
        }
    },
    methods: {

    },

    computed: {
        baseFormData(){
            return {
                from_ca_id: this.checkingAccount
            }
        }
    }
}
</script>
