<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            Новый контрагент
        </div>

        <div class="vuedal-content">

            <div class="form-group">
                <label>ИНН</label>
                <!--                <input-mask class="form-control" v-model="inn" regex="^[\d+]{10,12}$"/>-->
                <input-autocomplete v-model="inn" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.inn"
                                    :subtitle-key="item => 'ИНН ' + item.data.inn" title-key="value"/>
                <small class="form-text text-muted">
                    Введите ИНН или название компании для поиска
                </small>
            </div>

            <div class="form-group">
                <label>Тип компании</label>
                <select-ui :options="$store.state.OPFTypes" v-model="opf_type" required
                           el-class="form-control" class="d-block"/>
            </div>

            <div class="form-group">
                <label>Наименование</label>
<!--                <input type="text" class="form-control" v-model="name">-->
                <input-autocomplete v-model="name" type="counterparty" @choose="autocomplete"
                                    title-key="value" :subtitle-key="item => item.data.inn"/>
            </div>

            <div class="form-group">
                <label>Категория</label>
                <select-ui v-model="category" :options="$store.state.counterpartyCategories"
                           el-class="form-control" class="d-block" placeholder="Без категории"/>
            </div>


            <div class="form-group">
                <label>ОГРН</label>
<!--                <input-mask class="form-control" v-model="ogrn" regex="^\d{13}$"/>-->

                <input-autocomplete v-model="ogrn" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.ogrn"
                                    :subtitle-key="item => 'ОГРН ' + item.data.ogrn"
                                    title-key="value"/>
            </div>

            <div class="form-group">
                <label>КПП</label>
<!--                <input-mask class="form-control" v-model="kpp" regex="^\d{4}[\dA-Z][\dA-Z]\d{3}$"/>-->

                <input-autocomplete v-model="kpp" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.kpp"
                                    title-key="value"
                                    :subtitle-key="item => 'КПП ' + item.data.kpp" />
            </div>

            <div class="form-group">
                <label>Почтовый адрес</label>
                <input type="text" class="form-control" v-model="address">
            </div>

            <div class="form-group">
                <label>Юр. адрес</label>
<!--                <input type="text" class="form-control" v-model="legal_address">-->
                <input-autocomplete v-model="legal_address" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.address.value"
                                    title-key="value"
                                    :subtitle-key="item => item.data.address.value"/>
            </div>


            <!-- Contacts -->
            <p class="font-weight-bold mt-3">
                Контакты
            </p>

            <div class="contacts">
                <div class="contact border-box mb-2" v-for="(contact, index) in contacts">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" class="form-control sm" required v-model="contacts[index].name">
                    </div>
                    <div class="form-group">
                        <label>Фамилия</label>
                        <input type="text" class="form-control sm"
                               v-model="contacts[index].surname">
                    </div>
                    <div class="form-group">
                        <label>Отчество</label>
                        <input type="text" class="form-control sm"
                               v-model="contacts[index].patronymic">
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input-mask class="form-control sm" v-model="contacts[index].phone"
                                    regex="^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$"/>
                    </div>
                    <div class="form-group">
                        <label>E-Mail</label>
                        <input type="email" class="form-control sm"
                               v-model="contacts[index].email">
                    </div>
                    <div class="form-group">
                        <b-form-checkbox value="1" unchecked-value="0"
                            v-model="contacts[index].main_contact"
                            :disabled="existMainContact && !contacts[index].main_contact">
                            Основной контакт
                        </b-form-checkbox>
                    </div>

                    <a @click="removeContact(index)" href="#" class="text-danger">Удалить контакт</a>
                </div>
            </div>

            <a href="#" @click.prevent="addContact">
                <b-icon-plus class="icon-left"/>
                Добавить контакт
            </a>

            <!-- Checking accounts -->
            <p class="font-weight-bold mt-3">
                Банковские счета
            </p>

            <div class="checking-accounts">
                <div class="account border-box mb-2" v-for="(account, index) in accounts">
                    <div class="form-group">
                        <label>БИК Банка</label>
                        <input-autocomplete type="bank" v-model="account.bank_bik" el-class="sm" strict
                                            :subtitle-key="bank => bank.data.bic"
                                            :value-key="bank => bank.data.bic"
                                            title-key="value"
                                            @choose="autocompleteBank"
                                            :state="index"/>
                    </div>
                    <div class="bank-description">
                        <p v-if="account.bank_name">{{ account.bank_name }}</p>
                        <p v-if="account.bank_correspondent">Кор. счёт банка {{ account.bank_correspondent }}</p>
                        <p v-if="account.bank_inn">ИНН {{ account.bank_inn }}</p>
                        <p v-if="account.bank_kpp">КПП {{ account.bank_kpp }}</p>
                        <p v-if="account.bank_swift">SWIFT {{ account.bank_swift }}</p>
                    </div>
                    <div class="form-group">
                        <label>Номер счёта</label>
                        <input-mask class="form-control sm"
                                    v-model="accounts[index].checking_num" regex="\d{5,25}"/>
                    </div>
                    <div class="form-group">
                        <b-form-checkbox v-model="accounts[index].main_account" value="1" unchecked-value="0">
                            Основной счёт
                        </b-form-checkbox>
                    </div>
                    <a @click="removeAccount(index)" href="#" class="text-danger">Удалить счёт</a>
                </div>
            </div>

            <a href="#" @click.prevent="addAccount">
                <b-icon-plus class="icon-left"/>
                Добавить счёт
            </a>

        </div>

        <modal-buttons @save="save" @cancel="closePopup"></modal-buttons>
    </div>
</template>

<script>

import ModalButtons from "../../UI/Modals/modal-buttons";
import Axios from "../../../mixins/axios";
import {EventBus} from "../../../index";

export default {
    name: "create-popup",
    mixins: [ Axios ],
    components: { ModalButtons },
    data(){
        return {
            name: '',
            inn: '',
            ogrn: '',
            kpp: '',
            address: '',
            legal_address: '',
            opf_type: '',
            category: '',

            contacts: [],
            accounts: []
        }
    },

    methods: {

        autocomplete(party){
            party.value && (this.name = party.value);
            if(party.data){
                let data = party.data;

                data.inn && (this.inn = data.inn);
                data.kpp && (this.kpp = data.kpp);
                data.ogrn && (this.ogrn = data.ogrn);
                if(data?.address?.value)
                    this.legal_address = data.address.value;
            }
        },

        autocompleteBank({ item, state }){

            if(!this.accounts[state])
                return;

            let account = this.accounts[state];
            account.bank_name = item.value || '';
            account.bank_bik = item?.data?.bic || '';
            account.bank_inn = item?.data?.inn || '';
            account.bank_kpp = item?.data?.kpp || '';
            account.bank_swift = item?.data?.swift || '';
            account.bank_correspondent = item?.data?.correspondent_account || '';
        },

        // Добавить контакт
        addContact(){
            this.contacts.push({
                name: '',
                surname: '',
                patronymic: '',
                phone: '',
                email: '',
                main_contact: 0
            })
        },

        // Удалить контакт
        removeContact(index){
            this.contacts.splice(index, 1);
        },


        // Добавить счёт
        addAccount(){
            this.accounts.push({
                bank_id: null,
                checking_num: '',
                main_account: 0,
            });
        },

        // Удалить счёт
        removeAccount(index){
            this.accounts.splice(index, 1);
        },



        // Сохранить контрагента
        save(){
            let data = {
                name: this.name,
                inn: this.inn,
                ogrn: this.ogrn,
                kpp: this.kpp,
                address: this.address,
                legal_address: this.legal_address,
                category_id: this.category,
                opf_type_id: this.opf_type,
                contacts: this.contacts,
                accounts: this.accounts
            };

            this.axiosPOST('/ui/counterparties/create', data).then(r => {
                if(r.success){
                    this.$emit('vuedals:close');
                    EventBus.$emit('update:counterparties');
                }
            })
        },

        closePopup(){
            this.$emit('vuedals:close');
        }

    },

    computed: {
        existMainContact(){
            for(let i in this.contacts){
                if(this.contacts[i].main_contact)
                    return true;
            }
            return false;
        }
    }
}
</script>
