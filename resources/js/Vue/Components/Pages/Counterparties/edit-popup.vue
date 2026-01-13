<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            Редактировать
        </div>

        <div class="vuedal-content">

            <div class="form-group">
                <label>ИНН</label>
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
                <input-autocomplete v-model="name" type="counterparty" @choose="autocomplete"
                                    title-key="value"
                                    :subtitle-key="item => item.data.inn"/>
            </div>

            <div class="form-group">
                <label>Категория</label>
                <select-ui v-model="category" :options="$store.state.counterpartyCategories"
                           el-class="form-control" class="d-block" placeholder="Без категории"/>
            </div>


            <div class="form-group">
                <label>ОГРН</label>
                <input-autocomplete v-model="ogrn" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.ogrn"
                                    :subtitle-key="item => 'ОГРН ' + item.data.ogrn" title-key="value"/>
            </div>

            <div class="form-group">
                <label>КПП</label>
                <input-autocomplete v-model="kpp" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.kpp"
                                    :subtitle-key="item => 'КПП ' + item.data.kpp" title-key="value"/>
            </div>

            <div class="form-group">
                <label>Почтовый адрес</label>
                <input type="text" class="form-control" v-model="address">
            </div>

            <div class="form-group">
                <label>Юр. адрес</label>
                <input-autocomplete v-model="legal_address" type="counterparty" @choose="autocomplete"
                                    :value-key="item => item.data.address.value"
                                    :subtitle-key="item => item.data.address.value" title-key="value"/>
            </div>


            <!-- Contacts -->
            <p class="font-weight-bold mt-3">
                Контакты
            </p>

            <div class="contacts">
                <div class="contact border-box mb-2" v-for="(contact, index) in contacts">
                    <template v-if="!contact.id || contact.id === showContact">
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
                            <input-mask class="form-control sm" v-model="contacts[index].phone" regex="^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$"/>
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
                    </template>
                    <template v-else>
                        <p class="mb-0">
                            {{ getFullName(contact) }}
                        </p>
                        <p class="text-sm text-secondary mb-0">
                            <span class="phone">{{ contact.phone }}</span>
                            <span class="email">{{ contact.email }}</span>
                        </p>
                        <a @click.prevent="showContact = contact.id"
                           class="text-primary" href="#">Редактировать</a>

                        <a @click="removeContact(index)" href="#" class="text-danger ml-2">Удалить контакт</a>
                    </template>
                </div>

                <div class="trashed border-box" v-if="contactsToRemove.length">
                    <p class="mb-2 text-secondary">Удаленные контакты</p>
                    <div class="contact mb-2 trashed" v-for="(contact, index) in contactsToRemove">
                        <span class="d-inline-block">
                            {{ getFullName(contact) || "Контакт #" + index }}
                        </span>
                        <a href="#" @click.prevent="restoreContact(index)" class="d-inline-block">Восстановить</a>
                    </div>
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
                        <input-autocomplete type="bank" v-model="account.bank_bik" strict
                                            :value-key="bank => bank.data.bic"
                                            :subtitle-key="bank => bank.data.bic"
                                            title-key="value"
                                            @choose="autocompleteBank" :state="index"
                                            el-class="sm"/>
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
                    <a @click.prevent="removeAccount(index)" href="#" class="text-danger">Удалить счёт</a>
                </div>

                <!-- Trash -->
                <div class="trashed border-box" v-if="accountsToRemove.length">
                    <p class="mb-2 text-secontary">Удаленные реквизиты</p>
                    <div class="account mb-2 trashed" v-for="(account, index) in accountsToRemove">
                        <span class="d-inline-block">
                            {{ account.checking_num || "Счёт #" + index }}
                        </span>
                        <a href="#" @click.prevent="restoreAccount(index)" class="d-inline-block">Восстановить</a>
                    </div>
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
import Formats from "../../../mixins/formats";
import {EventBus} from "../../../index";

export default {
    name: "edit-popup",
    mixins: [ Axios, Formats ],
    components: { ModalButtons },
    props: {
        data: Object
    },
    data(){
        return {
            id: '',
            name: '',
            inn: '',
            ogrn: '',
            kpp: '',
            address: '',
            legal_address: '',
            opf_type: '',
            category: '',

            types: [
                { id: 1, name: 'Юридическое лицо'},
                { id: 2, name: 'Физическое лицо'}
            ],

            contacts: [],
            accounts: [],
            showContact: null,
            showAccount: null,

            contactsToRemove: [],
            accountsToRemove: [],
        }
    },

    methods: {

        // autocomplete counterparty
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

        // autocomplete bank
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
            let trash = this.contacts.splice(index, 1);
            if(trash){
                trash = trash.pop();
                trash.id && this.contactsToRemove.push(trash);
            }
        },

        // Восстановить контакт
        restoreContact(index){
            let restore = this.contactsToRemove.splice(index, 1);
            restore && this.contacts.push(restore.pop());
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
            let trash = this.accounts.splice(index, 1);
            if(trash){
                trash = trash.pop();
                trash.id && this.accountsToRemove.push(trash); // если есть id - даем возможность восстановить
            }
        },

        // Восстановить счет
        restoreAccount(index){
            let restore = this.accountsToRemove.splice(index, 1);
            restore && this.accounts.push(restore.pop());
        },



        // Сохранить контрагента
        save(){

            // Prepare data
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

            // Removed data
            if(this.contactsToRemove.length){
                let ids = [];
                for(let contact of this.contactsToRemove)
                    ids.push(contact.id);

                ids.length && (data.remove_contacts = ids.join(','));
            }

            // Removed accounts
            if(this.accountsToRemove.length){
                let ids = [];
                for(let account of this.accountsToRemove)
                    ids.push(account.id);

                ids.length && (data.remove_accounts = ids.join(','));
            }

            console.log(data);

            this.axiosPOST(`/ui/counterparties/${this.id}/update`, data).then(r => {
                if(r.success){
                    this.$emit('vuedals:close');
                    EventBus.$emit('update:counterparties');
                }
            })
        },

        closePopup(){
            if(confirm('Данные не сохранены. Закрыть?'))
                this.$emit('vuedals:close');
        },
    },

    computed: {
        existMainContact(){
            for(let i in this.contacts){
                if(this.contacts[i].main_contact)
                    return true;
            }
            return false;
        },

        banks(){
            return this.$store.state.banks;
        }
    },

    created(){

        let data = this.data;

        // Start mapping
        if(data){
            for(let i in data){
                if(this[i] !== undefined)
                    this[i] = data[i];
            }
        }

        // For type and category
        if(data.category_id)
            this.category = data.category_id;
        if(data.type_id)
            this.opf_type = data.type_id;
    }
}
</script>
