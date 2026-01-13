<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            {{ data ? 'Редактирование прихода' : 'Приход' }}
        </div>
        <div class="vuedal-content">

            <!-- Set as scheduled -->
<!--            <div class="form-group">
                <b-form-checkbox
                    v-model="plannedOperation">
                    Сделать плановой операцией
                </b-form-checkbox>
            </div>-->

            <!-- Transaction Amount -->
            <div class="form-group">
                <label>Сумма</label>
                <div class="input-group">
                    <input type="number" class="form-control" v-model="amount">
                    <div class="input-group-append">
                        <select-ui v-model="currency" :options="currencies" :required="true" el-class="form-control"></select-ui>
                    </div>
                    <error-label :errors="errors" field="amount"></error-label>
                </div>

<!--                <b-form-checkbox v-model="cash">
                    Наличные средства
                </b-form-checkbox>-->
            </div>

            <!-- Choose checking account -->
            <div class="form-group">
                <label>Счет</label>
                <select-ui v-model="checkingAccount"
                           :required="true"
                           el-class="form-control"
                           class="d-block"
                           :options="$store.state.checkingAccounts"
                           subtitle-key="num"
                           placeholder="Выберите счёт"
                           right-title-key="balance" right-title-type="price"
                           @input="clearError('date')"/>
                <error-label :errors="errors" field="to_ca_id"></error-label>
            </div>


            <!-- For scheduled -->
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
                                 format="DD.MM.YYYY HH:mm" @input="clearError('date')"
                                 input-class="form-control" placeholder="Выбрать дату"></date-picker>
                    <error-label :errors="errors" field="date"></error-label>
                </div>
            </template>


            <div class="form-group">
                <label>Контрагент</label>
                <input-autocomplete v-model="counterparty_name" type="counterparty"
                                    placeholder="Введите ИНН или название для поиска"
                                    title-key="value" :subtitle-key="item => 'ИНН ' + item.data.inn"
                                    strict-mod chevron def-placeholder
                                    @choose="chooseCounterparty"/>
                <error-label :errors="errors" field="counterparty_id"></error-label>
            </div>


            <!--
            <input-autocomplete v-model="counterparty_name" type="counterparty" @choose="choosingCounterparty"
                                                                    title-key="value" :subtitle-key="item => `ИНН ${item.data.inn}`" strict-mod chevron
                                                                    placeholder="Введите ИНН или название компании" def-placeholder/>
                                                                    -->
            <div class="form-group">
                <label>Направление</label>
                <input-autocomplete type="project" v-model="project_name" strict-mod chevron def-placeholder
                                    placeholder="Выбрать направление"
                                    value-key="id" title-key="name"
                                    @choose="chooseProject"/>
                <error-label :errors="errors" field="project_id"></error-label>
                <a href="#" @click.prevent="projectsPopup" class="form-action">
                    <b-icon-plus></b-icon-plus>
                    <span>Добавить направление</span>
                </a>
            </div>

            <div class="form-group">
                <label>Статья</label>
                <input-autocomplete type="budget-item-income" strict-mod def-placeholder chevron
                                    v-model="budget_item_name" value-key="id" title-key="name"
                                    placeholder="Выбрать статью"
                                    @choose="chooseBudgetItem"/>
                <error-label :errors="errors" field="budget_item_id"></error-label>
                <a href="#" @click.prevent="budgetItemsPopup" class="form-action">
                    <b-icon-plus></b-icon-plus>
                    <span>Добавить свою статью</span>
                </a>
            </div>

            <div class="form-group">
                <label>Описание</label>
                <textarea class="form-control" placeholder="Предоплата за..." v-model="description"></textarea>
                <error-label :errors="errors" field="description"></error-label>
            </div>




        </div>
        <modal-buttons v-on:save="save" v-on:cancel="cancel"></modal-buttons>
    </div>
</template>

<script>

import Mixin from "./mixin";

export default {
    name: "CreateIncome",
    mixins: [ Mixin ],
    data(){
        return {
            type: 'income'
        }
    },
    methods: {

    },
    computed: {

        baseFormData(){
            return {
                to_ca_id: this.checkingAccount
            }
        }
    }
}
</script>
