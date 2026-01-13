<template>
    <div class="form-wrapper">

        <!-- INN -->
        <div class="form-group">

            <input-autocomplete v-model="company_inn" type="counterparty" name="company_inn" input-autofocus
                                @choose="autocompleteCounterparty"
                                :value-key="item => item.data.inn"
                                title-key="value" :subtitle-key="item => 'ИНН ' + item.data.inn"
                                placeholder="ИНН"/>

            <small class="form-text text-muted">
                Для поиска введите ИНН или название компании
            </small>
            <small v-if="errors.company_inn" class="form-text text-danger">
                {{ error('company_inn') }}
            </small>
        </div>


        <!-- Company name -->
        <div class="form-group">

            <input-autocomplete id="company_name_input" v-model="company_name" type="counterparty" name="company_name"
                                @choose="autocompleteCounterparty" value-key="value" :title-key="item => item.data.address.value"
                                placeholder="Название компании"/>

            <small v-if="errors.company_name" class="form-text text-danger">
                {{ error('company_name') }}
            </small>
        </div>




        <div class="form-group">

            <input-ui type="text" v-model="company_kpp"
                      placeholder="КПП" name="company_kpp"/>

            <small v-if="errors.company_kpp" class="form-text text-danger">
                {{ error('company_kpp') }}
            </small>
        </div>


<!--        <div class="form-group">
            <input-ui type="text" name="company_address"
                   placeholder="Почтовый адрес"
                   v-model="company_address"/>

            <small v-if="errors.company_address" class="form-text text-danger">
                {{ error('company_address') }}
            </small>
        </div>-->


        <div class="form-group">
            <input-ui type="text" name="company_legal_address" required
                   v-model="company_legal_address" placeholder="Юридический адрес"/>

            <small v-if="errors.company_legal_address" class="form-text text-danger">
                {{ error('company_legal_address') }}
            </small>
        </div>


        <button @click.prevent="commitData" class="btn btn-primary w-100" :disabled="!dataValid">Продолжить</button>

    </div>
</template>

<script>
import axios from "../../../Vue/mixins/axios";
import utils from "../../../Vue/mixins/utils";
import CreateCompanyMixin from "./CreateCompanyMixin";

export default {
    name: "CreateCompany",
    mixins: [ utils, axios, CreateCompanyMixin ],
    props: {
        options: Object,
        errors: Object
    },

    data(){
        return {
            companyTypes: [],
            company_type: null,

            company_name: '',
            company_subdomain: '',
            company_inn: '',
            company_kpp: '',
            company_ogrn: '',
            company_address: '',
            company_legal_address: '',
            director_name: '',
            director_position: '',

            // autocomplete object
            company: {},
        }
    },

    methods: {


        autocompleteCounterparty(party){
            this.company = party;
            this.company_name =         party?.value || '';
            this.company_type =         this.searchOPFID(party);
            this.company_inn =          party?.data?.inn || '';
            this.company_kpp =          party?.data?.kpp || '';
            this.company_ogrn =         party?.data?.ogrn || '';
            this.company_legal_address = party?.data?.address?.value || '';

            // Company Management
            this.director_name = party.data?.management?.name || '';
            let position = party.data?.management?.post;
            if(position) {
                position = position.toLowerCase();
                let block = "\\w\\u0400-\\u04FF",
                    rx = new RegExp("([^" + block + "]|^)([" + block + "])", "g");

                position = position.replace(rx, function ($0, $1, $2) {
                    return $1 + $2.toUpperCase();
                });
                this.director_position = position || '';
            }
        },

        // search organization OPF type
        searchOPFID(party){
            let opfFromParty = party?.data?.opf;
            if(!this.companyTypes.length || !opfFromParty) return;

            let type = this.companyTypes.find(type => {
                return (''+type.code) === (''+opfFromParty.code)
                    || type.short_name === opfFromParty.short;
            })

            if(!type)
                console.info('OPF type not found', { type: opfFromParty });


            return type ? type.id : null;
        },

        commitData(){
            if(!this.dataValid) return;
            this.$emit('commit', {
                company_name: this.company_name,
                company_type: this.company_type,
                company_subdomain: this.company_subdomain,
                company_inn: this.company_inn,
                company_kpp: this.company_kpp,
                company_ogrn: this.company_ogrn,
                company_address: this.company_address,
                company_legal_address: this.company_legal_address,
                director_name: this.director_name,
                director_position: this.director_position,
                company: this.company,
            });
        }



    },


    computed: {
        dataValid(){

            // inn and name required
            if( !this.company_inn || !this.company_name)
                return false;

            // inn and kpp format
            if( !this.company_inn.match(/^\d+$/) || (this.company_kpp && !this.company_kpp.match(/^\d+$/)) )
                return false;


            return true;
        },
    },



    created(){
        this.companyTypes = window.companyTypes || [];
        this.getOlds();

        this.company_name = this.options?.company_name || '';
        this.company_type = this.options?.company_type || null;
        this.company_subdomain = this.options?.company_subdomain || '';
        this.company_inn = this.options?.company_inn || '';
        this.company_kpp = this.options?.company_kpp || '';
        this.company_ogrn = this.options?.company_ogrn || '';
        this.company_address = this.options?.company_address || '';
        this.company_legal_address = this.options?.company_legal_address || '';
        this.director_name = this.options?.director_name || '';
        this.director_position = this.options?.director_position || '';
    }
}
</script>
