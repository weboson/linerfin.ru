<template>
    <div class="transactions__filter-popup">
        <header>
            <a href="#" class="close-btn" @click.prevent="$emit('close')"><b-icon-x/></a>
        </header>
        <div class="row">

            <!-- sum input -->
            <div class="col-8">
                <div class="form-group">
                    <label>Сумма</label>
                    <div class="input-group">
                        <input v-model="filter.sum_from" type="text" placeholder="от" class="form-control">
                        <input v-model="filter.sum_to" type="text" placeholder="до" class="form-control">
                        <div class="input-group-append">
                            <select-ui @input="emitFilters" v-model="filter.currency_id" :required="true" :options="$store.state.currencies" el-class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end sum input -->


            <!-- planned type -->
            <div class="col-4">
                <div class="form-group">
                    <label>План и факт</label>
                    <select-ui @input="emitFilters" v-model="filter.scheduledType" placeholder="Все операции"
                               :options="scheduledTypes"
                               class="w-100" el-class="form-control"/>
                </div>
            </div>

            <!-- budget item -->
            <div class="col-4">
                <div class="form-group">
                    <label>Статья</label>
                    <select-ui @input="emitFilters" v-model="filter.budget_item_id" :options="$store.state.budgetItems" el-class="form-control" class="w-100" placeholder="Любая статья"/>
                </div>
            </div>

            <!-- project -->
            <div class="col-4">
                <div class="form-group">
                    <label>Проект</label>
                    <select-ui @input="emitFilters" v-model="filter.project_id" :options="$store.state.projects" el-class="form-control" class="w-100" placeholder="Любой проект"/>
                </div>
            </div>

            <!-- account -->
            <div class="col-4">
                <div class="form-group">
                    <label>Счёт</label>
                    <select-ui @input="emitFilters" v-model="filter.checking_account_id" :options="$store.state.checkingAccounts" el-class="form-control" class="w-100" placeholder="Любой счёт"/>
                </div>
            </div>

            <!-- counterparty -->
            <div class="col-4">
                <div class="form-group">
                    <label>Контрагент</label>
                    <select-ui @input="emitFilters" v-model="filter.counterparty_id" :options="$store.state.counterparties" el-class="form-control" class="w-100" placeholder="Любой контрагент"/>
                </div>
            </div>

            <!-- counterparty's Category -->
            <div class="col-4">
                <div class="form-group">
                    <label>Категория контрагента</label>
                    <select-ui @input="emitFilters" v-model="filter.counterparty_category_id" :options="$store.state.counterpartyCategories" el-class="form-control" class="w-100" placeholder="Любая категория"/>
                </div>
            </div>

            <!-- counterparty's Type -->
            <div class="col-4">
                <div class="form-group">
                    <label>Тип контрагента</label>
                    <select-ui @input="emitFilters" v-model="filter.counterparty_type_id" :options="$store.state.OPFTypes" el-class="form-control" class="w-100" placeholder="Любой тип"/>
                </div>
            </div>
        </div>
        <footer>
            <a href="#" @click.prevent="resetAll">
                <b-icon-x class="icon-left"/>
                <span>Сбросить фильтры</span>
            </a>
        </footer>
    </div>
</template>

<script>
export default {

    props: {
        value: Object
    },
    data(){
        return {
            filter: {
                currency_id: '0',
                sum_from: null,
                sum_to: null,
                operation_type: null,
                budget_item_id: null,
                project_id: null,
                checking_account_id: null,
                counterparty_id: null,
                counterparty_category_id: null,
                counterparty_type_id: null,

                scheduledType: null,
            },
            scheduledTypes: [
                { id: '1', name: 'Запланированное'},
                { id: '2', name: 'Факт'}
            ]
        }
    },

    methods: {
        resetAll(){
            let resetValues = Object.assign({}, this.filter, {
                currency_id: '0',
                sum_from: null,
                sum_to: null,
                operation_type: null,
                budget_item_id: null,
                project_id: null,
                checking_account_id: null,
                counterparty_id: null,
                counterparty_category_id: null,
                counterparty_type_id: null,
                scheduledType: null,
            });
            this.$emit('input', resetValues);
        },

        emitFilters(){
            this.$emit('input', this.filter);
        }
    },

    watch: {
        value: function(){
            this.filter = this.value || {};
        }
    },

    created(){
        this.filter = this.value || {};
    }
}
</script>
