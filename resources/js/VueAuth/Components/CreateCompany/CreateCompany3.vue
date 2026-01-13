<template>
    <div class="form-wrapper">

        <div class="form-group">
            <input-ui v-model="director_position" placeholder="Должность директора"/>
        </div>

        <div class="form-group">
            <input-ui v-model="director_name" placeholder="ФИО директора"/>
        </div>

        <template v-if="!accountant_disabled">
            <div class="form-group">
                <input-ui v-model="accountant_position" placeholder="Должность бухгалтера"/>
            </div>

            <div class="form-group">
                <input-ui v-model="accountant_name" placeholder="ФИО бухгалтера"/>
            </div>
        </template>

        <div class="mb-3 d-flex justify-content-between">
            <div class="form-group m-0 text-muted">
                <b-form-checkbox v-model="accountant_disabled" size="sm">Бухгалтер не предусмотрен</b-form-checkbox>
            </div>
            <div class="text-muted small" style="display: inline-block"
                 v-b-tooltip.hover title="Введенные данные будут использованы при выставлении счетов в качестве значений по-умолчанию">
                Для чего эти данные <b-icon-question-circle class="ml-1"/>
            </div>
        </div>


        <button class="btn btn-primary w-100" @click.prevent="commitData" :disabled="!dataValid">Продолжить</button>
        <button class="text-secondary btn w-100 mt-2 p-0" style="font-size: 14px"
                @click.prevent="$emit('commit', {})">
            Пропустить
        </button>
    </div>
</template>

<script>
import CreateCompanyMixin from "./CreateCompanyMixin";
export default {
    name: "CreateCompany3",
    props: {
        options: Object,
    },
    mixins: [ CreateCompanyMixin ],

    data(){
        return {
            director_position: '',
            director_name: '',
            accountant_position: '',
            accountant_name: '',
            accountant_disabled: false,
        }
    },


    computed: {
        dataValid(){
            return !(
                !!this.director_name && !this.director_name.match(/^[a-zA-Zа-яА-Я .-]+$/)
                ||
                !!this.accountant_name && !this.accountant_name.match(/^[a-zA-Zа-яА-Я .-]+$/)
            );
        }
    },


    methods: {
        commitData(){
            if(!this.dataValid) return;

            this.$emit('commit', {
                director_position: this.director_position,
                director_name: this.director_name,
                accountant_position: this.accountant_disabled ? '' : this.accountant_position,
                accountant_name: this.accountant_disabled ? '' : this.accountant_name,
                accountant_exists: !this.accountant_disabled
            });
        }
    },

    created(){
        this.director_position = this.options?.director_position || '';
        this.director_name = this.options?.director_name || '';
        this.accountant_position = this.options?.accountant_position || '';
        this.accountant_name = this.options?.accountant_name || '';
        this.accountant_disabled = !this.options?.accountant_exists;
    }
}
</script>
