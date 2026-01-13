<template>
    <div class="form-wrapper">

        <!-- Taxation sys -->
        <div class="form-group">
            <input-autocomplete v-model="taxation_name" :options="taxationSystems" placeholder="Система налогообложения"
                                value-key="name" title="name" @choose="i => taxation_system = i.id" chevron disable-timeout strict-mod/>
        </div>

        <!-- NDS Type -->
        <div class="form-group">
            <!--            <select-ui v-model="company_nds_type" :options="companyNDS" el-class="form-control" class="w-100"
                                   placeholder="Тип НДС"
                                   required/>-->
            <input-autocomplete v-model="company_nds_type_title" :options="companyNDS" placeholder="Тип НДС"
                                value-key="name" title="name" @choose="i => company_nds_type = i.id" chevron disable-timeout strict-mod/>

            <!-- <small v-if="errors.company_nds_type" class="form-text text-danger">
                {{ error('company_nds_type') }}
            </small> -->
        </div>

        <small class="form-text text-muted">
            Эти данные необходимы для корректного отображения отчетов
        </small>

        <br>

        <button @click.prevent="commitData" class="btn btn-primary w-100" :disabled="!dataValid">Продолжить</button>
        <button class="text-secondary btn w-100 mt-2 p-0" style="font-size: 14px"
                @click.prevent="$emit('commit', {})">
            Пропустить
        </button>
    </div>
</template>

<script>
export default {
    name: "CreateCompany1-2",
    props: {
        options: Object
    },

    data(){
        return {
            companyNDS: [],
            taxationSystems: [],

            company_nds_type: null,
            company_nds_type_title: '',

            taxation_system: null,
            taxation_name: null
        }
    },

    computed: {

        dataValid(){
            // need to choose taxation and VAT
            return this.company_nds_type && this.taxation_system;
        }


    },

    methods: {
        commitData(){
            if(!this.dataValid) return;
            this.$emit('commit', {
                company_nds_type: this.company_nds_type,
                taxation_system: this.taxation_system,
            });
        },

    },


    created() {
        this.companyNDS = window.companyNDS || [];
        this.taxationSystems = window.taxationSystems || [];

        this.company_nds_type = this.options?.company_nds_type || null;
        this.taxation_system = this.options?.taxation_system || null;
    }
}
</script>
