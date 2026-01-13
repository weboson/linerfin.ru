<template>
    <div class="form-wrapper">

        <div class="form-group with-tip">
            <input-autocomplete type="bank" v-model="bank_name" placeholder="Банк" strict-mod
                                @choose="autocompleteBank" value-key="value"
                                title-key="value"
                                :subtitle-key="item => `БИК ${item.data.bic}`"/>

            <div class="form-tip text-primary" v-b-tooltip.hover title="Введите БИК Банка или название для поиска">
                <b-icon-question-circle/>
            </div>
        </div>

        <transition name="fade">
            <div v-if="bank.value">
                <table class="custom-table th-150 small mb-3">
                    <tr>
                        <th>БИК</th>
                        <td>{{ bank_bik }}</td>
                    </tr>
                    <tr>
                        <th>Корр. счёт</th>
                        <td>{{ bank_correspondent }}</td>
                    </tr>
                    <tr>
                        <th>SWIFT</th>
                        <td>{{ bank_swift }}</td>
                    </tr>
                    <tr>
                        <th>ИНН</th>
                        <td>{{ bank_inn }}</td>
                    </tr>
                    <tr>
                        <th>КПП</th>
                        <td>{{ bank_kpp }}</td>
                    </tr>
                </table>
            </div>
        </transition>

        <div class="form-group">
            <input-ui type="number"
                v-model="checking_account" placeholder="Расчетный счет"/>
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
    name: "CreateCompany2",
    mixins: [ CreateCompanyMixin ],
    props: {
        options: Object,
    },

    data(){
        return{
            checking_account: '',
            bank_bik: '',
            bank_name: '',
            bank_correspondent: '',
            bank_swift: '',
            bank_inn: '',
            bank_kpp: '',

            bank: {}, // from autocomplete
        }
    },


    computed: {
        dataValid(){
            return !(!this.bank_bik || !this.checking_account || !this.bank_name);
        }
    },

    methods: {
        autocompleteBank(item){
            this.bank = item;
            this.bank_name          = item?.value || '';
            this.bank_bik           = item?.data?.bic || '';
            this.bank_correspondent = item?.data?.correspondent_account || '';
            this.bank_swift         = item?.data?.swift || '';
            this.bank_inn           = item?.data?.inn || '';
            this.bank_kpp           = item?.data?.kpp || '';
        },

        commitData(){
            if(!this.dataValid) return;
            this.$emit('commit', {
                checking_account: this.checking_account,
                bank_bik: this.bank_bik,
                bank_name: this.bank_name,
                bank_correspondent: this.bank_correspondent,
                bank_swift: this.bank_swift,
                bank_inn: this.bank_inn,
                bank_kpp: this.bank_kpp,
            });
        }
    },

    created(){
        this.checking_account = this.options?.checking_account || '';
        this.bank_bik = this.options?.bank_bik || '';
        this.bank_name = this.options?.bank_name || '';
        this.bank_correspondent = this.options?.bank_correspondent || '';
        this.bank_swift = this.options?.bank_swift || '';
        this.bank_inn = this.options?.bank_inn || '';
        this.bank_kpp = this.options?.bank_kpp || '';
        this.bank = this.options?.bank || {};
    }
}
</script>
