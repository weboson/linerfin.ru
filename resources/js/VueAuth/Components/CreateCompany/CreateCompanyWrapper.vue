<script>
import CreateCompany1 from "./CreateCompany1";
    import CreateCompany1_2 from "./CreateCompany1-2";
import CreateCompany2 from "./CreateCompany2";
import CreateCompany3 from "./CreateCompany3";
import CreateCompany4 from "./CreateCompany4";
import CreateCompany5 from "./CreateCompany5";
import CreateCompany6 from "./CreateCompany6";
import axios from "../../../Vue/mixins/axios";


const MaxStep = 7;

export default {
    name: "CreateCompanyWrapper",
    mixins: [ axios ],
    components: { CreateCompany1, CreateCompany1_2, CreateCompany2, CreateCompany3, CreateCompany4, CreateCompany5, CreateCompany6 },

    data(){
        return {
            step: 0,

            company_name: '',
            company_type: null,
            company_nds_type: null,
            taxation_system: null,
            company_subdomain: '',
            company_inn: '',
            company_kpp: '',
            company_ogrn: '',
            company_address: '',
            company_legal_address: '',

            // stamp
            stamp_id: null,
            stamp_uuid: null,

            company: {},

            checking_account: '',
            bank_bik: '',
            bank_name: '',
            bank_correspondent: '',
            bank_swift: '',
            bank_inn: '',
            bank_kpp: '',
            bank: {},

            // director
            director_position: 'Главный директор',
            director_name: '',
            director_signature_id: null,
            director_signature_uuid: null,

            // accountant
            accountant_position: 'Главный бухгалтер',
            accountant_name: '',
            accountant_signature_id: null,
            accountant_signature_uuid: null,
            accountantExists: true,

            logo_attachment_id: null,
            logo_attachment_uuid: null,

            subdomainExists: false,
            subdomainAffected: false,

            errors: {},

            loading: false
        }
    },

    computed: {

        maxStep(){
            return MaxStep
        },

        progress(){
            return 100 / MaxStep * this.step + '%';
        },

        stepName(){
            const nameStack = [
                'Данные о компании',
                'Система налогообложения',
                'Банковские реквизиты',
                'Выставление счетов',
                'Печать и подписи',
                'Логотип компании',
                'Доменное имя'
            ];
            if(nameStack[this.step])
                return nameStack[this.step];
            return `Шаг ${this.step+1}`;
        },


        // base company info
        dataStep1(){
            return {
                company_name: this.company_name,
                company_type: this.company_type,
                company_subdomain: this.company_subdomain,
                company_inn: this.company_inn,
                company_kpp: this.company_kpp,
                company_ogrn: this.company_ogrn,
                company_address: this.company_address,
                company_legal_address: this.company_legal_address
            }
        },

        // taxation sys
        dataStep1_2(){
            return {
                company_nds_type: this.company_nds_type,
                taxation_system: this.taxation_system,
            }
        },

        // Checking account and bank
        dataStep2(){
            return {
                checking_account: this.checking_account,
                bank_bik: this.bank_bik,
                bank_name: this.bank_name,
                bank_correspondent: this.bank_correspondent,
                bank_swift: this.bank_swift,
                bank_inn: this.bank_inn,
                bank_kpp: this.bank_kpp,
            }
        },

        // Positions
        dataStep3(){
            return {
                director_position: this.director_position,
                director_name: this.director_name,
                accountant_position: this.accountant_position,
                accountant_name: this.accountant_name,
                accountant_exists: this.accountantExists
            }
        },

        // Signature and stamp attachments
        dataStep4(){
            return {
                director_signature_id: this.director_signature_id,
                director_signature_uuid: this.director_signature_uuid,
                accountant_signature_id: this.accountant_signature_id,
                accountant_signature_uuid: this.accountant_signature_uuid,
                stamp_id: this.stamp_id,
                stamp_uuid: this.stamp_uuid
            }
        },

        accountLink(){
            if(!this.company_subdomain) return '#';
            return `https://${this.company_subdomain}.${this.domain}`;
        },

        domain(){
            return window?.DOMAIN || 'linerfin.ru';
        },

        CSRF(){
            let meta = document.head.querySelector('[name="csrf-token"]');
            if(meta)
                return meta.content || '';

            return '';
        }
    },

    methods: {

        createCompany(){
            if(this.step < MaxStep) return;
            this.loading = true;

            let data = {
                _token: this.CSRF,
                company_name: this.company_name,
                company_type: this.company_type,
                company_nds_type: this.company_nds_type,
                taxation_system: this.taxation_system,
                company_subdomain: this.company_subdomain,
                company_inn: this.company_inn,
                company_kpp: this.company_kpp,
                company_address: this.company_address,
                company_legal_address: this.company_legal_address,
                checking_account: this.checking_account,
                bank_name: this.bank_name,
                bank_bik: this.bank_bik,
                bank_inn: this.bank_inn,
                bank_kpp: this.bank_kpp,
                bank_correspondent: this.bank_correspondent,
                bank_swift: this.bank_swift,

                // director
                director_position: this.director_position,
                director_name: this.director_name,
                director_signature_id: this.director_signature_id,
                director_signature_uuid: this.director_signature_uuid,

                // accountant
                accountant_position: this.accountant_position,
                accountant_name: this.accountant_name,
                accountant_signature_id: this.accountant_signature_id,
                accountant_signature_uuid: this.accountant_signature_uuid,

                // stamp
                stamp_id: this.stamp_id,
                stamp_uuid: this.stamp_uuid,

                // logo
                logo_attachment_id: this.logo_attachment_id,
                logo_attachment_uuid: this.logo_attachment_uuid,
            }

            this.errors = {};

            this.axiosPOST('/new-company', data)
                .then(data => {
                    this.loading = false;
                })
                .catch(response => {
                    this.loading = false;
                    this.step = MaxStep - 1;

                    if(response.data.errors) {
                        const errors = response.data.errors;
                        this.errors = errors;
                        for(let key in errors){
                            let errorValues = errors[key]; // array
                            errorValues.forEach(error => {
                                this.errorMsg(error);
                            });
                        }
                    }

                    console.error(response);
                })
        },

        checkSubdomain(){
            if(!this.company_subdomain)
                return;

            this.axiosPOST('/ui/check-subdomain', {subdomain: this.company_subdomain}).then(r => {
                this.subdomainExists = !r.free;
            })
        },

        pushData(data){

            // Debug log
            // console.debug('Step commit', data);

            data.company            && (this.company = data.company);
            data.company_name       && (this.company_name = data.company_name);
            data.company_type       && (this.company_type = data.company_type);
            data.company_nds_type       && (this.company_nds_type = data.company_nds_type);
            data.taxation_system       && (this.taxation_system = data.taxation_system);
            data.company_subdomain  && (this.company_subdomain = data.company_subdomain);
            data.company_inn        && (this.company_inn = data.company_inn);
            data.company_kpp        && (this.company_kpp = data.company_kpp);
            data.company_ogrn       && (this.company_ogrn = data.company_ogrn);
            data.company_address    && (this.company_address = data.company_address);
            data.company_legal_address  && (this.company_legal_address = data.company_legal_address);

            data.checking_account       && (this.checking_account = data.checking_account);
            data.bank_bik           && (this.bank_bik = data.bank_bik);
            data.bank_name          && (this.bank_name = data.bank_name);
            data.bank_correspondent     && (this.bank_correspondent = data.bank_correspondent);
            data.bank_swift         && (this.bank_swift = data.bank_swift);
            data.bank_inn           && (this.bank_inn = data.bank_inn);
            data.bank_kpp           && (this.bank_kpp = data.bank_kpp);
            data.bank               && (this.bank = data.bank);

            // director
            data.director_position  && (this.director_position = data.director_position);
            data.director_name      && (this.director_name = data.director_name);
            data.director_signature_id  && (this.director_signature_id = data.director_signature_id);
            data.director_signature_uuid    && (this.director_signature_uuid = data.director_signature_uuid);

            // accountant
            data.accountant_position    && (this.accountant_position = data.accountant_position);
            data.accountant_name        && (this.accountant_name = data.accountant_name);
            data.accountant_signature_id    && (this.accountant_signature_id = data.accountant_signature_id);
            data.accountant_signature_uuid  && (this.accountant_signature_uuid = data.accountant_signature_uuid);

            // accountant exists
            this.accountantExists = data.accountant_exists || false;

            // stamp
            data.stamp_id    && (this.stamp_id = data.stamp_id);
            data.stamp_uuid  && (this.stamp_uuid = data.stamp_uuid);

            // logo
            data.logo_attachment_id     && (this.logo_attachment_id = data.logo_attachment_id);
            data.logo_attachment_uuid   && (this.logo_attachment_uuid = data.logo_attachment_uuid);

            this.step++;
            if(this.step === MaxStep)
                this.createCompany();
        },

        generateSubdomain(){
            if(!this.company_name || this.subdomainAffected)
                return;

            this.axiosPOST('/ui/generate-subdomain', {
                name: this.company_name
            }).then(r => {
                if(this.subdomainAffected) return;
                this.company_subdomain = r.subdomain || this.company_subdomain;
            });
        }
    },

}
</script>


<template>
    <div id="form">
        <div class="creating-progress bg-primary" :style="`width: ${progress}`"></div>

        <transition name="fade-out">
            <div class="loader" style="background: #FCFCFC;" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
        </transition>

        <transition name="slide">
            <div v-if="step < maxStep">
                <header class="form-title">
                    Новая компания
                    <div class="subtitle">
                        {{ 'Шаг ' + (step+1) + ':' }} {{ stepName }}
                    </div>
                </header>

                <transition name="slide">
                    <create-company-1 v-if="step === 0" :errors="errors" :options="dataStep1" @commit="pushData"/>
                    <create-company-1_2 v-else-if="step === 1" :errors="errors" :options="{ taxation_system, company_nds_type }" @commit="pushData"/>
                    <create-company-2 v-else-if="step === 2" :errors="errors" :options="dataStep2" @commit="pushData"/>
                    <create-company-3 v-else-if="step === 3" :errors="errors" :options="dataStep3" @commit="pushData"/>
                    <create-company-4 v-else-if="step === 4" :errors="errors" :options="dataStep4" :accountant-exists="accountantExists" @commit="pushData"/>
                    <create-company-5 v-else-if="step === 5" :errors="errors" :options="{logo_attachment_id, logo_attachment_uuid}" @commit="pushData"/>
                    <create-company-6 v-else-if="step === 6" :errors="errors" :options="{company_name, company_subdomain}" @commit="pushData"/>
                </transition>
            </div>

            <div v-else>
                <header class="form-title">
                    Готово!
                    <div class="subtitle">Компания {{ company_name }} создана</div>
                </header>

                <a :href="accountLink" class="btn btn-primary w-100">
                    Перейти в аккаунт
                </a>
                <a :href="`https://my.${domain}`" class="text-secondary btn w-100 mt-2 p-0" style="font-size: 14px">
                    К списку компаний
                </a>
            </div>
        </transition>
    </div>
</template>

<style lang="sass">
    #linerfin
        #form
            position: relative

        .creating-progress
            display: block
            position: absolute
            left: 0
            top: 0
            height: 5px
            width: 0
            max-width: 100%
            transition: width 250ms


</style>
