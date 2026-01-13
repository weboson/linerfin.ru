<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            {{ data ? 'Редактирование перевода' : 'Перевод' }}
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
                <label>Со счёта</label>
<!--                <select-ui v-model="from_ca_id"
                           :required="true"
                           el-class="form-control"
                           class="d-block"
                           :options="checkingAccounts"
                           subtitle-key="num"
                           placeholder="Выберите счёт"/>-->
                <input-autocomplete type="checking-account" v-model="from_ca_id"
                                    value-key="id" title-key="name" subtitle-key="num"
                                    placeholder="Выберите счёт" :where="excludeAccounts"
                                    strict-mod def-placeholder chevron/>
            </div>
            <div class="form-group">
                <label>На счёт</label>
                <input-autocomplete type="checking-account" v-model="to_ca_id"
                                    value-key="id" title-key="name" subtitle-key="num"
                                    placeholder="Выберите счёт" :where="excludeAccounts"
                                    strict-mod def-placeholder chevron/>
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
            </template>

            <template v-else>
                <div class="form-group">
                    <label>Дата</label>
                    <date-picker v-model="date" type="date" class="d-block w-100"
                                 format="DD.MM.YYYY"
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
                                    placeholder="Выбрать направление" @choose="chooseProject"
                                    value-key="id" title-key="name"/>
                <a href="#" @click.prevent="projectsPopup" class="form-action">
                    <b-icon-plus></b-icon-plus>
                    <span>Добавить направление</span>
                </a>
            </div>

            <div class="form-group">
                <label>Статья</label>
                <input-autocomplete type="budget-item-transfer" strict-mod def-placeholder chevron
                                    v-model="budget_item_name" value-key="id" title-key="name"
                                    @choose="chooseBudgetItem"
                                    placeholder="Выбрать статью"/>
                <a href="#" @click.prevent="budgetItemsPopup" class="form-action">
                    <b-icon-plus></b-icon-plus>
                    <span>Добавить статью</span>
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

import Mixin from './mixin';

export default {
    name: "CreateTransfer",
    mixins: [ Mixin ],
    data(){
        return {
            type: 'transfer',
            to_ca_id: null,
            from_ca_id: null,
        }
    },
    methods: {

    },
    computed: {
        baseFormData(){
            return {
                from_ca_id: this.from_ca_id,
                to_ca_id: this.to_ca_id
            }
        },

        excludeAccounts(){
            let exclude_ids = [];
            if(this.from_ca_id) exclude_ids.push(this.from_ca_id);
            if(this.to_ca_id) exclude_ids.push(this.to_ca_id);

            return exclude_ids.length ? { exclude: exclude_ids.join(',') } : null;
        }

        /*checkingAccounts(){
            let list = this.$store.state.checkingAccounts;
            return list.filter(account => !~[this.from_ca_id, this.to_ca_id].indexOf(account.id))
            return list;
        }*/
    }
}
</script>
