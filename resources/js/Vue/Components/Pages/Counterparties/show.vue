<template>
    <section class="top-header-wrapper page-counterparty">
        <div class="top-header" :class="topHeaderOffset">

            <div class="breadcrumbs-wrapper">
                <a href="#" class="back-link"
                    @click.prevent="$router.push('/counterparties')">
                    <b-icon-arrow-left class="icon-left"/>
                    Назад
                </a>
                <ul class="breadcrumbs">
                   <li>Контрагенты</li>
                   <li>{{ counterparty.name }}</li>
                </ul>
            </div>
            <header class="title">
                <span>{{ counterparty.name }}</span>
            </header>
        </div>

        <div class="container-fluid top-header__content">
            <header></header>

            <div class="toolbar mb-4">
                <div class="left">
                    <a href="#" @click.prevent="editPopup" class="btn btn-primary btn-170">
                        Редактировать
                    </a>
                    <a href="#" @click.prevent="removeCounterparty" class="btn btn-outline-danger btn-170">
                        Удалить
                    </a>
                </div>
            </div>

            <div class="content loader-wrapper">

                <transition name="fade-out">
                    <div class="loader centered" v-if="loader">
                        <b-spinner variant="primary" label="Загрузка"></b-spinner>
                    </div>
                </transition>

                <div class="row">
                    <div class="col-8">
                        <div class="sec-header">О контрагенте</div>
                        <table class="custom-table">
                            <tr v-if="counterparty.opf">
                                <th>Тип</th>
                                <td>
                                    {{ counterparty.opf.name }}
                                    <span v-if="!!counterparty.opf.short_name && counterparty.opf.name !== counterparty.opf.short_name">
                                        ({{ counterparty.opf.short_name }})
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="counterparty.legal_address">
                                <th>Юр. адрес</th>
                                <td>{{ counterparty.legal_address }}</td>
                            </tr>
                            <tr v-if="counterparty.address">
                                <th>Адрес</th>
                                <td>{{ counterparty.address }}</td>
                            </tr>
                            <tr v-if="counterparty.inn">
                                <th>ИНН</th>
                                <td>{{ counterparty.inn }}</td>
                            </tr>
                            <tr v-if="counterparty.ogrn">
                                <th>ОГРН</th>
                                <td>{{ counterparty.ogrn }} </td>
                            </tr>
                            <tr v-if="counterparty.kpp">
                                <th>КПП</th>
                                <td>{{ counterparty.kpp }} </td>
                            </tr>
                        </table>


                        <section class="checking-accounts" v-if="counterparty.accounts && counterparty.accounts.length">
                            <div class="sec-header">Банковские реквизиты</div>
                            <div class="account" v-for="account in counterparty.accounts">
                                <table class="custom-table">
                                    <tr v-if="account.main_account">
                                        <th colspan="2" class="text-secondary">
                                            Основной счёт
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>Банк</th>
                                        <td>{{ account.bank_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>БИК Банка</th>
                                        <td>{{ account.bank_bik }}</td>
                                    </tr>
                                    <tr>
                                        <th>Кор. счёт Банка</th>
                                        <td>{{ account.bank_correspondent }}</td>
                                    </tr>
                                    <tr>
                                        <th>Расчётный счёт</th>
                                        <td>{{ account.checking_num }}</td>
                                    </tr>
                                </table>
                            </div>
                        </section>


                    </div>
                    <section class="col-4 sec-right">
                        <div v-if="counterparty.address || counterparty.legal_address">
                            <div class="text-secondary">Почтовый адрес</div>
                            <div class="address">{{ counterparty.address || counterparty.legal_address }}</div>
                        </div>

                        <!-- Templates -->
                        <div class="bill-namespace">
                            <section class="my-3 bill__templates" v-if="templates.length">
                                <div class="templates__list">
                                    <header>
                                        <p><strong>Шаблоны</strong></p>
                                    </header>

                                    <div class="template" v-for="template in templates">

                                        <b-dropdown variant="link" toggle-class="text-decoration-none" no-caret>
                                            <template #button-content>
                                                <b-icon-three-dots-vertical/>
                                            </template>
                                            <b-dropdown-item @click.prevent="openBill(template)" href="#">Создать счёт</b-dropdown-item>
                                            <b-dropdown-item @click.prevent="editBill(template)" href="#">Редактировать</b-dropdown-item>
                                            <b-dropdown-item @click.prevent="removeBills([template.id])" href="#">Удалить</b-dropdown-item>
                                        </b-dropdown>

                                        <div class="template__preview" @click="openBill(template)">
                                            {{ getTemplatePreview(template, '#' + template.id) }}
                                        </div>
                                        <div class="template__name" @click="openBill(template)">
                                            {{ template.num || "Без названия"}}
                                        </div>
                                        <div class="template__sum" @click="openBill(template)">
                                            <price-format-ui :amount="template.sum"/>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </section>
                </div>

                <section class="contacts" v-if="contacts.length">
                    <div class="sec-header">Контакты</div>

                    <div class="contacts__list">
                        <div class="contact" v-for="contact in contacts">
                            <div class="contant__name">
                                {{ getFullName(contact) }}
                            </div>
                            <div class="contact__phone" v-if="contact.phone">
                                <a href="#">{{ contact.phone }}</a>
                            </div>
                            <div class="contact__email" v-if="contact.email">
                                <a href="#">{{ contact.email }}</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        </div>


    </section>
</template>

<script>
import Axios from '../../../mixins/axios';
import Utils from "../../../mixins/utils";
import Formats from '../../../mixins/formats';
import PageOffsetMixin from "../../../mixins/page-offset";

import BillUtils from '../Bills/bill-utils';
import EditPopup from "./edit-popup";

export default {
    name: "counterparty-show",
    mixins: [ Axios, Formats, BillUtils, Utils, PageOffsetMixin ],
    props: ['counterparty_id'],

    data(){
        return {
            loader: false,
            counterparty: {}
        }
    },

    methods: {
        getCounterparty(){
            let params = {
                with: 'category,contacts,opf,accounts,bill_templates'
            };

            this.loader = true;
            this.axiosGET(`/ui/counterparties/${this.counterparty_id}?${this.queryString(params)}`)
                .then(data => {
                    setTimeout(() => { this.loader = false }, 300);
                    this.counterparty = data.counterparty || {};
                })
                .catch(r => {
                    setTimeout(() => { this.loader = false }, 300);
                    this.errorNotify('Не удалось открыть контрагента');
                })
        },


        editPopup(){

            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                props: { data: this.counterparty },

                component: EditPopup,
                onClose: (data) => {
                    this.getCounterparty();
                }
            })
        },

        removeCounterparty(){
            if(!confirm('Подтвердите удаление контрагента ' + this.counterparty?.name))
                return;

            this.loader = true;
            this.axiosPOST('/ui/counterparties/delete', {
                ids: this.counterparty_id
            }).then(r => {
                this.loader = false;
                this.$router.push('/counterparties');
            })
            .catch(r => {
                setTimeout(() => { this.loader = false }, 300);
                this.errorNotify('Не удалось удалить контрагента', 'Удаление контрагента');
            })
        },


        // Remove bill template
        removeBills(ids){

            ids = ids || this.selected;
            if(!ids || !ids.length)
                return;

            if(!confirm('Удалить выбранные счета?'))
                return;

            this.loader = true;

            this.axiosPOST('/ui/bills/delete', {
                ids: ids.join(',')
            }).finally(r => {
                this.getCounterparty();
            })
        },
    },

    computed: {
        contacts(){
            return this.counterparty.contacts || [];
        },

        accounts(){
            return this.counterparty.accounts || [];
        },

        templates(){
            return this.counterparty.bill_templates || [];
        }
    },

    created() {
        this.getCounterparty();
    }
}
</script>
