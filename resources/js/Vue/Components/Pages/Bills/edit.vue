<template>


    <!-- Top header -->
    <div class="top-header-wrapper bill-create-page bill-namespace">

        <div class="loader loader__white-bg" style="padding-top: 300px;" v-if="loading">
            <b-spinner variant="primary" label="Загрузка"></b-spinner>
        </div>

        <div class="top-header" :class="topHeaderOffset">
            <div class="breadcrumbs-wrapper">
                <a href="#" class="back-link"
                   @click.prevent="$router.push('/bills')">
                    <b-icon-arrow-left class="icon-left"/>
                    Назад
                </a>

                <ul class="breadcrumbs">
                    <li>Счета</li>
                    <li>{{ pageTitle }}</li>
                </ul>
            </div>
            <header class="title">
                <img src="/assets/images/icons/bill.svg" class="icon-left">
                <span>{{ pageTitle }}</span>
            </header>
        </div>


        <!-- Main content -->
        <div class="container-fluid top-header__content"
             v-if="!finalLabel">

            <div class="content bill-blank">

                <!-- Creating steps -->
                <header class="page-nav-switch">
                    <a href="#" class="switch-link" :class="!sendStep ? 'active' : ''" @click.prevent>
                        Шаг 1. Данные счёта
                    </a>
                    <a href="#" class="switch-link" :class="sendStep ? 'active' : ''" @click.prevent>
                        Шаг 2. Отправка контрагенту
                    </a>
                </header>


                <!-- First step -->
                <div class="first-step bill-blank__first-step" v-if="!sendStep">
                    <div class="pb-5 mb-3 border-bottom">
                        <div class="row row-mg">

                            <!-- left side -->
                            <div class="col-8 col-pd pr-5 bill-blank__left-side">

                                <table class="custom-table th-150">

                                    <!-- bill number -->
                                    <tr>
                                        <th>Номер</th>
                                        <td>
                                            <div class="form-group mb-0 with-tip">
                                                <input class="form-control" type="text" placeholder="Автоматически"
                                                       v-model="num" @keyup="realTimeSaving">
                                                <small v-if="errors.num" class="text-danger">{{ errors.num[0] }}</small>

                                                <div class="form-tip text-primary" v-b-tooltip.hover title="Автоматически будет создан номер счета в формате 0000-000001">
                                                    <b-icon-question-circle/>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>


                                    <!-- select company -->
                                    <tr v-if="$store.getters.userAccounts.length > 1">
                                        <th class="vertical-top pt">Ваша компания</th>
                                        <td>
                                            <div class="form-group mb-0">
                                                <select-ui v-model="account" el-class="form-control" class="w-100" :required="!template"
                                                           :options="$store.getters.userAccounts"
                                                           @change="choosingCompany" :subtitle-key="i => i.subdomain + '.linerfin.ru'"/>
                                                <small v-if="errors.company" class="text-danger">{{ errors.company[0] }}</small>
                                            </div>
                                        </td>
                                    </tr>


                                    <!-- checking account -->
                                    <tr v-if="checkingAccounts.length > 1">
                                        <th class="vertical-top pt">Счет</th>
                                        <td class="pb-3">
                                            <select-ui el-class="form-control" class="w-100" :required="!template"
                                                       v-model="checking_account" :options="checkingAccounts"
                                                       :subtitle-key="i => `${i.num} | ${i.bank_name}`" right-title-key="balance" right-title-type="price"
                                                       @change="realTimeSaving"/>
                                            <div>
                                                <small v-if="errors.checking_account" class="text-danger">{{ errors.checking_account[0] }}</small>
                                                <small class="text-muted" v-else-if="selectedCheckingAccount && (!selectedCheckingAccount.bank_name || !selectedCheckingAccount.bank_bik)">
                                                    <b-icon icon="exclamation-circle-fill" variant="warning"/>
                                                    У выбранного счёта не указаны реквизиты банка
                                                </small>
                                            </div>

                                            <div v-if="selectedCheckingAccount && (selectedCheckingAccount.bank_name && selectedCheckingAccount.bank_bik)"
                                                class="checking-account__info mt-2">
                                                <div class="bank-name">
                                                    <span class="text-secondary">
                                                        Банк:
                                                    </span>
                                                    <span>{{ selectedCheckingAccount.bank_name }}</span>
                                                </div>
                                                <div class="bank-bik">
                                                    <span class="text-secondary">БИК Банка:</span>
                                                    <span>{{ selectedCheckingAccount.bank_bik }}</span>
                                                </div>
                                            </div>

                                            <a class="btn btn-link p-0 mt-2" @click.prevent='editCheckingAccount(selectedCheckingAccount)' href="#">Ввести реквизиты банка</a>
                                        </td>
                                    </tr>
                                    <tr v-else-if="!checking_account">
                                        <th class="vertical-top pt-1">Счет</th>
                                        <td class="pb-3 pt-1">
                                            <p class="text-warning mb-2">
                                                <b-icon-info-circle class="mr-2"/>
                                                Не указаны реквизиты банка
                                            </p>
                                            <a class="btn btn-link p-0" @click.prevent='editCheckingAccount(selectedCheckingAccount)' href="#">Ввести реквизиты банка</a>
                                        </td>
                                    </tr>



                                    <!-- #counterparty -->
                                    <tr>
                                        <th class="vertical-top pt">Контрагент</th>
                                        <td v-if="!counterpartyManually" class="pb-3">
                                            <div class="form-group mb-0 with-tip">
                                                <input-autocomplete v-model="counterparty_name" type="counterparty" @choose="choosingCounterparty"
                                                                    title-key="value" :subtitle-key="item => `ИНН ${item.data.inn}`" strict-mod chevron
                                                                    placeholder="Введите ИНН или название компании" def-placeholder/>

                                                <small v-if="errors.counterparty" class="text-danger">{{ errors.counterparty[0] }}</small>
                                            </div>

                                            <a class="btn btn-link p-0 mt-2" @click.prevent="counterpartyManually = true" href="#">Ввести реквизиты самостоятельно</a>
                                        </td>

                                        <!-- counterparty manually -->
                                        <td v-if="counterpartyManually" class="pb-3">
                                            <div class="form-group">
                                                <input-autocomplete v-model="counterparty_inn" type="counterparty" @choose="choosingCounterparty"
                                                                    placeholder="ИНН" def-placeholder
                                                                    @keyup="realTimeSaving"
                                                                    :value-key="item => item.data.inn"
                                                                    :subtitle-key="item => 'ИНН ' + item.data.inn"
                                                                    title-key="value"/>
                                                <small class="form-text text-muted">
                                                    Для поиска введите ИНН или наименование компании
                                                </small>
                                                <small v-if="errors.counterparty_inn" class="text-danger">{{ errors.counterparty_inn[0] }}</small>
                                            </div>
                                            <div class="form-group">
                                                <input-autocomplete v-model="counterparty_name" type="counterparty"
                                                                    @choose="choosingCounterparty"
                                                                    @keyup="realTimeSaving"
                                                                    title-key="value"
                                                                    :subtitle-key="item => item.data.inn"
                                                                    placeholder="Наименование контрагента"/>
                                                <small v-if="errors.counterparty_name" class="text-danger">{{ errors.counterparty_name[0] }}</small>
                                            </div>
                                            <div class="form-group">
                                                <select-ui v-model="counterparty_type_id" :options="$store.state.OPFTypes"
                                                           @choose="realTimeSaving" placeholder="Тип"
                                                           el-class="form-control" class="w-100" required/>
                                                <small v-if="errors.counterparty_type_id" class="text-danger">{{ errors.counterparty_type_id[0] }}</small>
                                            </div>
                                            <div class="form-group">
                                                <!--                                                <label>КПП</label>-->

                                                <input-autocomplete v-model="counterparty_kpp" type="counterparty"
                                                                    @choose="choosingCounterparty"
                                                                    @keyup="realTimeSaving"
                                                                    :value-key="item => item.data.kpp"
                                                                    :subtitle-key="item => 'КПП ' + item.data.kpp"
                                                                    title-key="value"
                                                                    placeholder="КПП"/>

                                                <small v-if="errors.counterparty_kpp" class="text-danger">{{ errors.counterparty_kpp[0] }}</small>
                                            </div>
                                            <div class="form-group">
                                                <!--                                                <label>Юр. адрес</label>-->
                                                <input-ui v-model="counterparty_address" @input="realTimeSaving" type="text"
                                                          placeholder="Юр. адрес"/>
                                                <small v-if="errors.counterparty_address" class="text-danger">{{ errors.counterparty_address[0] }}</small>
                                            </div>

                                            <a @click.prevent="counterpartyManually = false" v-if="$store.state.counterparties.length"
                                               href="#" class="btn btn-link p-0 mt-2 text-secondary">Выбрать из списка</a>
                                        </td>
                                    </tr>

                                    <!-- pay before -->
                                    <tr v-if="!template && status !== 'template'">
                                        <th>Прогноз даты оплаты</th>
                                        <td>
                                            <div class="form-group mb-0 with-tip">
                                                <date-picker type="date" input-class="form-control" class="w-100" format="DD.MM.YYYY"
                                                             :start="new Date()" v-model="pay_before"  @change="realTimeSaving"/>
                                                <small v-if="errors.pay_before" class="text-danger">{{ errors.pay_before[0] }}</small>
                                                <div class="form-tip text-primary" v-b-tooltip.hover title="Данная информация будет отображена в платежном календаре">
                                                    <b-icon-question-circle/>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>


                                </table>
                            </div>


                            <!-- right side -->
                            <div class="col-4 col-pd bill-blank__right-side">

                                <div class="mb-4 bill-blank__logo">
                                    <input-file v-model="logo_id"
                                                :attachment-data="logoAttachmentData"
                                                :extensions="['jpg', 'png', 'jpeg']"
                                                max-size="1258291"
                                                accept="image/jpeg,image/png"
                                                @input-uuid="logoAttachmentHandler">
                                        <div class="text-center text-secondary">
                                            Логотип компании <b-icon-plus-circle class="ml-1"/>
                                        </div>
                                    </input-file>
                                </div>

                                <div class="form-group bill-blank__counterparty-comment">
                                    <textarea class="form-control" v-model="comment" placeholder="Комментарий для контрагента"
                                              @keyup="realTimeSaving"></textarea>
                                    <small v-if="errors.counterparty_comment" class="text-danger">{{ errors.counterparty_comment[0] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- bill positions [#positions] -->
                    <div class="positions-list border-bottom pb-5 mb-3 pt-3">
                        <p class="header-title">Список товаров или услуг</p>

                        <table class="positions-table custom-table">
                            <thead>
                            <tr>
                                <th class="pr-1">№</th>
                                <th style="width: 250px;">Товар или услуга</th>
                                <th>Артикул</th>
                                <th style="width: 120px;">Цена за ед.</th>
                                <th style="width: 120px;">Количество</th>
                                <th style="width: 100px;">Единицы</th>
                                <th style="width: 140px; text-align: right;">Сумма</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr v-for="(position, i) in positions">
                                <td>{{ i + 1 }}</td>
                                <td>
                                    <input type="text" v-model="positions[i].name" class="form-control" @keyup="realTimeSaving">
                                </td>
                                <td>
                                    <input type="text" v-model="positions[i].vendor_code" class="form-control" @keyup="realTimeSaving">
                                </td>
                                <td>
                                    <input type="number" min="0" v-model="positions[i].unit_price" class="form-control" @keyup="realTimeSaving">
                                </td>
                                <td>
                                    <input type="number" min="1" v-model="positions[i].count" class="form-control" @keyup="realTimeSaving">
                                </td>
                                <td>
                                    <input-autocomplete v-model="positions[i].units" title-key="value" @keyup="realTimeSaving" :options="units" disable-timeout/>
                                </td>
                                <td class="sum">
                                    <price-format-ui :amount="positionCost(position)" class="justify-content-end"/>
                                    <a href="#" class="positions-list__remove-position text-danger" title="Удалить позицию"
                                       v-if="positions.length > 1" @click.prevent="removePosition(i)">
                                       <b-icon-x/>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- errors -->
                        <div v-if="positionErrors" class="alert alert-danger my-3">
                            <ul class="mb-0">
                                <li v-for="error in positionErrors">
                                    <template v-for="er in error">{{ er }}</template>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="#" @click.prevent="addPosition">
                                <b-icon-plus class="icon-left"/>
                                Добавить
                            </a>
                            <div class="sum bill-view__sum">
                                <div class="bill-nds-sum" v-if="VATType">
                                    <div class="bill-nds-sum__item">
                                        <b-dropdown variant="link" toggle-class="text-decoration-none"
                                                    no-caret dropleft>
                                            <template #button-content>
                                                <div class="bill__nds-name">
                                                    <b-icon-chevron-down font-scale="0.8"/>
                                                    <span>{{ VATType.percentage !== null ?  `НДС ${VATType.name}` : 'Без НДС' }}</span>
                                                </div>
                                            </template>

                                            <b-dropdown-item v-for="VAT in VATs" :key="VAT.id" @click="chooseBillVAT(VAT)">
                                                {{ VAT.name }}
                                            </b-dropdown-item>

                                            <b-dropdown-divider/>
                                            <b-dropdown-text>
                                                <b-icon-exclamation-circle class="text-warning mr-1"/>
                                                Для изменения НДС компании, перейдите в <router-link to="/settings/companies">настройки</router-link>
                                            </b-dropdown-text>
                                        </b-dropdown>

                                        <div v-if="VATType.percentage !== null">
                                            <price-format-ui class="align-right" :amount="VATAmount"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="bill-sum">
                                    <span>Общая сумма</span>
                                    <price-format-ui :amount="totalSum"/>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- signature list -->
                    <section class="signature-list mb-3 pt-3">
                        <p class="header-title">Подпись и печать</p>

                        <b-form-checkbox v-model="enableSignatureAttachments" :value="false" :unchecked-value="true"
                                         @change="realTimeSaving">
                            Без подписи и печати
                        </b-form-checkbox>

                        <div class="signature mt-3" v-for="(sign, i) in signature" :class="i > 0 ? 'border-top pt-3' : ''">
                            <div class="row row-mg">
                                <div :class="enableSignatureAttachments ? 'col-8' : 'col-12'" class="col-pd">

                                    <a v-if="!enableSignatureAttachments && signature.length > 1" @click.prevent="removeSignature(i)" href="#" class="close">
                                        <b-icon-x/>
                                    </a>

                                    <table class="custom-table th-200">
                                        <tr>
                                            <th>Должность</th>
                                            <td>
                                                <input type="text" class="form-control" v-model="signature[i].position"
                                                    @keyup="signatureAffectChanged">
                                            </td>
                                        </tr>
                                        <tr v-if="enableSignatureAttachments">
                                            <th>Подпись</th>
                                            <td>
                                                <input-file v-model="signature[i].signature_attachment_id"
                                                            :extensions="['jpg', 'png', 'jpeg']"
                                                            max-size="1258291"
                                                            accept="image/jpeg,image/png"
                                                            @input="signatureAffectChanged"
                                                            :attachment-data="signature[i].signature_attachment"
                                                            remove-background/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Расшифровка подписи</th>
                                            <td>
                                                <input type="text" class="form-control" v-model="signature[i].full_name"
                                                       @keyup="signatureAffectChanged">
                                            </td>
                                        </tr>
                                    </table>
                                </div>


                                <div class="col-4 col-pd">
                                    <a v-if="signature.length > 1" @click.prevent="removeSignature(i)" href="#" class="close">
                                        <b-icon-x/>
                                    </a>

                                    <div class="company-stamp" v-if="enableSignatureAttachments && i === 0">

                                        <input-file v-model="stamp_id"
                                                    :attachment-data="stampData"
                                                    :exceptions="['jpg', 'png', 'jpeg']"
                                                    @input-uuid="stampAttachmentHandler"
                                                    accept="image/jpeg,image/png"
                                                    max-size="1258291"
                                                    remove-background>
                                            <div class="text-center text-secondary">
                                                Печать компании <b-icon-plus-circle class="ml-1"/>
                                            </div>
                                        </input-file>

                                    </div> <!-- / company stamp -->
                                </div>
                            </div>
                        </div>


                        <!-- signature errors -->
                        <div v-if="signatureErrors" class="alert alert-danger my-3">
                            <ul class="mb-0">
                                <li v-for="error in signatureErrors">
                                    <template v-for="er in error">{{ er }}</template>
                                </li>
                            </ul>
                        </div>

                        <footer class="mt-4">
                            <a href="#" @click.prevent="addSignature">
                                <b-icon-plus class="icon-left"/>
                                Добавить сотрудника
                            </a>
                        </footer>
                    </section>


                    <section v-if="!this.template" class="creating-template border-top pt-3 mt-3">
                        <b-form-checkbox v-model="createTemplate">
                            Создать шаблон
                        </b-form-checkbox>

                        <div v-if="createTemplate" class="form-group mt-2 mb-3">
                            <label>Название шаблона</label>
                            <input v-model="createTemplateName" placeholder="Автоматически"
                                   type="text" class="form-control">
                        </div>

                        <p class="text-secondary mt-2">
                            Шаблон будет создан после успешной отправки контрагенту <br>
                            и будет доступен на странице счетов.
                        </p>
                    </section>

                </div>


                <div class="send-step" v-else>

                    <div class="d-flex justify-content-center py-4">
                        <success size="150"></success>
                    </div>

                    <div class="text-center">
                        <header class="h4">
                            Счёт выставлен
                        </header>

                        <div v-if="num" class="number">
                        <span class="text-secondary">
                            Номер счёта
                        </span>
                            {{ "№" + num }}
                        </div>

                        <div class="payer" v-if="counterparty_name">
                            <span class="text-secondary">Кому</span>
                            {{ counterparty_name }}
                        </div>

                        <div class="actions mt-4">
                            <router-link :to="`/bills/${id}`" class="btn btn-170 btn-outline-secondary" exact>
                                <b-icon-eye class="icon-left"/>
                                <span>Посмотреть</span>
                            </router-link>
                            <a :href="printLink" class="btn btn-170 btn-outline-secondary"
                               :target="printLink === '#' ? '_self' : '_blank'">
                                <b-icon-printer class="icon-left"/>
                                <span>Напечатать</span>
                            </a>
                            <a :href="downloadLink" class="btn btn-170 btn-outline-secondary"
                               :target="downloadLink === '#' ? '_self' : '_blank'">
                                <b-icon-download class="icon-left"/>
                                <span>Скачать</span>
                            </a>
                        </div>
                    </div>

                    <div class="row row-mg col-pd border-top pt-4 mt-5">

                        <div class="container-fluid text-center d-flex flex-column align-items-center">
                            <div class="mb-3 text-secondary">
                                Отправьте уведомление контрагенту
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <input-ui v-model="payerEmail" type="email" placeholder="Электронная почта"/>
                                    <button @click="sendNotifyClient" class="btn btn-primary">
                                        <span>Отправить</span>
                                        <b-icon-arrow-right-square class="ml-1"/>
                                    </button>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>


            <!-- Actions in footer -->
            <div v-if="!sendStep" class="toolbar mt-3">
                <div class="left align-items-center">

                    <!-- For drafts -->
                    <template v-if="status === 'draft'">
                        <a @click.prevent="removeDraft" href="#"
                           class="btn btn-200 btn-outline-secondary">
                            Удалить черновик
                        </a>
                        <span class="d-inline-block ml-4 text-secondary">
                            {{ lastSaveTimeText }}
                        </span>
                    </template>

                    <template v-else-if="bill_id">
                        <a @click.prevent="$router.push( '/bills/' + bill_id )" href="#"
                           class="btn btn-200 btn-outline-secondary">
                            Отмена
                        </a>
                    </template>

                </div>
                <div class="right">
                    <a @click.prevent="saveAndNext" href="#" class="btn btn-200 btn-primary">
                        Выставить счёт
                    </a>
                </div>
            </div>

            <div v-else class="toolbar mt-3">
                <div class="left">
                    <a href="#" class="btn btn-200 btn-outline-secondary"
                       @click.prevent="sendStep = false">
                        Редактировать
                    </a>
                </div>
                <div class="right">
                    <router-link class="btn btn-outline-secondary" to="/bills" exact>
                        Вернуться к списку
                    </router-link>
                </div>
            </div>
        </div>


        <!-- Final label -->
        <div class="container-fluid top-header__content loader-wrapper"
             v-if="finalLabel">

            <div class="content">
                <final :data="{ id, num, counterparty, status, template, pay_before, payerNotify, payerPhone, payerEmail, comment }"/>
            </div>
        </div>

    </div>
</template>




<script>

import Axios from '../../../mixins/axios';
import formats from '../../../mixins/formats';
import BillUtils from './bill-utils';
import moment from 'moment';
import utils from "../../../mixins/utils";
import PageOffsetMixin from '../../../mixins/page-offset.js';

import Final from "./edit-final";

import {EventBus} from "../../../index";
import EditPopup from "../Settings/CheckingAccounts/edit-popup";
import Success from '../../UI/Animations/Success';
import InputFile from '../../UI/Form/file-upload/input-file-2';

export default {
    name: "bill-edit",
    mixins: [Axios, formats, BillUtils, utils, PageOffsetMixin],

    props: {
        bill_id: {},
        template: {},
        useTemplate: {}
    },

    components: { Final, Success, InputFile },

    data(){
        return {
            id: null, // Bill ID
            status: 'draft',
            sendStep: false,
            finalLabel: false,

            // Bill data
            num: '',
            pay_before: null,
            account: null,
            checking_account: null,
            counterparty: null,
            nds_type_id: null, // VAT type

            comment: '',


            // Bill logo
            logo_id: null,
            logo_uuid: null,

            // Company stamp
            stamp_id: null,
            stamp_uuid: null,


            // Counterparty manually
            counterpartyManually: false,
            counterparty_name: '',
            counterparty_type_id: null,
            counterparty_inn: '',
            counterparty_kpp: '',
            counterparty_address: '',


            // Bill's positions
            positions: [],
            units: [{value: 'шт'}, {value: 'кг'}, {value: 'час'}, {value: 'усл'}],

            // Bill's signature list
            signature: [],
            enableSignatureAttachments: true,
            signatureChanged: false,

            // Saving
            lastSaveTime: null,
            realTimeSavingTimer: false,

            // Loader
            loading: false,

            disableRealtimeSaving: false,

            // Payer contacts
            payerPhone: '',
            payerEmail: '',
            payerNotify: true,

            // Create template
            createTemplate: false,
            createTemplateName: '',


            // Hash link to bill
            link: '',

            errors: {},
        }
    },


    computed: {

        // Logo attachment data for input-file
        logoAttachmentData(){
            return this.logo_uuid ? { uuid: this.logo_uuid } : {}
        },

        // Stamp attachment data for input-file
        stampData(){
            return this.stamp_uuid ? {uuid: this.stamp_uuid} : {}
        },

        // allowed checking accounts
        checkingAccounts(){
            return this.$store.state.checkingAccounts.filter(i => i.name !== 'Наличные');
        },

        // Last time of auto-saving
        lastSaveTimeText(){
            if(this.lastSaveTime)
                return moment(this.lastSaveTime).calendar()

            return ''
        },

        // Total sum of bill
        totalSum(){
            let sum = 0;
            for(let i in this.positions){
                sum += this.positionCost(this.positions[i]);
            }
            return sum;
        },



        // VAT list
        VATs(){
            return this.$store.state.ndsTypes || [];
        },

        // Chosen VAT
        VATType(){
            if(this.nds_type_id)
                return this.VATs.find(i => i.id === this.nds_type_id) || null;

            return null;
        },

        // Sum by VAT
        VATAmount(){
            // if VAT type exists
            if(this.VATType && this.VATType.percentage)
                return this.totalSum * parseFloat(this.VATType.percentage) / 100;

            return 0;
        },




        // Get info about chosen company
        selectedCompany(){
            let company_id = this.account,
                companies = this.$store.getters.userAccounts;

            // if company not exists
            if(company_id){
                for(let i in companies){
                    if(companies[i].id === Number(company_id))
                        return companies[i];
                }
            }

            return null;
        },

        // Get selected checking account
        selectedCheckingAccount(){
            let account_id = this.checking_account,
                accounts = this.$store.state.checkingAccounts;

            if(account_id)
                for(let i in accounts)
                    if(accounts[i].id === Number(account_id))
                        return accounts[i];

            return null;
        },


        // Title of page
        pageTitle(){
            if(this.template)
                return this.bill_id ? "Редактирование шаблона" : "Создание шаблона";
            return this.bill_id ? 'Редактирование счёта' : 'Создание счёта';
        },


        /* --  LINKS  -- */
        downloadLink(){
            return this.link ? '/bill-download-' + this.link : '#';
        },

        printLink(){
            return this.link ? '/bill-' + this.link + "?print" : '#';
        },

        showLink(){
            return this.link ? `/bill-${this.link}` : '#';
        },

        positionErrors(){
            let er = this.errors;
            if(!er || !Object.keys(er).length)
                return null;
            if(!er.unit_price && !er.count && !er.units)
                return null;

            let errors = [];
            er.unit_price && errors.push(er.unit_price);
            er.count && errors.push(er.count);
            er.units && errors.push(er.units);

            return errors;
        },

        signatureErrors(){
            let er = this.errors;
            if(!er || !Object.keys(er).length)
                return null;
            if(!er.position && !er.full_name && !er.signature_attachment_id && !er.stamp_attachment_id)
                return null;

            let errors = [];
            er.position && errors.push(er.position);
            er.full_name && errors.push(er.full_name);
            er.signature_attachment_id && errors.push(er.signature_attachment_id);

            return errors;
        },

        counterpartyData(){

            if(this.counterparty){
                let party = this.$store.state.counterparties.filter(i => i.id === this.counterparty);

                if(party)
                    return party[0];

            }
            return null;
        },

        counterpartyDataName(){
            if(this.counterpartyData)
                return this.counterpartyData?.name;
            return null;
        }


    },


    methods: {


        // Load bill data
        getBillData(bill_id){
            this.loading = true;
            this.axiosGET('/ui/bills/' + bill_id + '?with=positions,signature_list,logo_attachment,stamp_attachment')
                .then(data => {
                    this.loading = false;
                    if(data.bill){
                        let bill = data.bill;

                        if(bill.status !== 'template')
                            this.num = bill.num || '';

                        bill.pay_before             && (this.pay_before = new Date(bill.pay_before));
                        bill.company_id             && (this.account = bill.account_id);
                        bill.checking_account_id    && (this.checking_account = bill.checking_account_id);
                        bill.nds_type_id            && (this.nds_type_id = bill.nds_type_id);

                        // logo attachment
                        if(bill.logo_attachment){
                            this.logo_id = bill.logo_attachment.id;
                            this.logo_uuid = bill.logo_attachment.uuid;
                        }

                        // company stamp
                        if(bill.stamp_attachment){
                            this.stamp_id = bill.stamp_attachment.id;
                            this.stamp_uuid = bill.stamp_attachment.uuid;
                        }

                        // counterparty
                        bill.counterparty_id        && (this.counterparty = bill.counterparty_id);
                        this.loadCounterpartyData();

                        bill.link                   && (this.link = bill.link);
                        bill.comment                && (this.comment = bill.comment);


                        // get payer data
                        this.payerEmail = bill.payer_email || '';
                        this.payerPhone = bill.payer_phone || '';


                        // enable attachments
                        this.enableSignatureAttachments = !!bill.enable_attachments;

                        // get bill status
                        this.status = this.useTemplate ? 'draft' : bill.status || 'draft';

                        // Disabling auto-save
                        if(this.status !== 'draft')
                            this.disableRealtimeSaving = true;

                        // todo: this template?



                        // get positions
                        if(bill.positions){
                            let positions = [];
                            for(let i in bill.positions){
                                let position = bill.positions[i];
                                position.nds_type = position.nds_type_id || null;
                                delete position.nds_type_id;

                                positions.push(position);
                            }
                            if(positions.length)
                                this.positions = positions;
                        }


                        // get signatures
                        if(bill.signature_list){
                            let signatures = [];
                            for(let i in bill.signature_list){
                                let signature = bill.signature_list[i];
                                signature.signature_attachment_id = bill.signature_attachment_id || null;

                                signatures.push(signature);
                            }

                            if(signatures.length)
                                this.signature = signatures;
                        }

                    }
                });
        }, // <-- get bill data [end]



        // save bill
        save(status, strict, loader, callback) {

            clearTimeout(this.realTimeSavingTimer); // clear timeout
            status = status || this.status;

            let data = {
                num: this.num,
                pay_before: !!this.pay_before ? moment(this.pay_before).format() : null,
                account_id: this.account,
                nds_type_id: this.nds_type_id,
                checking_account_id: this.checking_account,
                counterparty_comment: this.comment,
                positions: this.positions,
                signature: this.signature,
                logo_attachment_id: this.logo_id || null,
                stamp_attachment_id: this.stamp_id || null,
                enable_attachments: this.enableSignatureAttachments ? 1 : 0,
                comment: this.comment,
                status: status
            };


            // Set counterparty data
            if(this.counterparty){
                data.counterparty_id = this.counterparty;
            }
            else if(this.counterparty_name && this.counterparty_inn){
                data.counterparty_name = this.counterparty_name;
                data.counterparty_type_id = this.counterparty_type_id;
                data.counterparty_inn = this.counterparty_inn;
                data.counterparty_kpp = this.counterparty_kpp;
            }
            // if data not exists -> check status
            else if(!~['template', 'draft'].indexOf(status)){
                this.notify('Для продолжения заполните контрагента');
                return;
            }


            // Strict-save mod
            if(!!strict) data.strict = 1;


            // Create template from this bill
            if(this.createTemplate){
                data.create_template = 1;
                data.create_template_name = this.createTemplateName;
            }

            // Prepare request URL
            const url = this.id ? `/ui/bills/${this.id}/save` : '/ui/bills/save';

            // enable loader
            if(loader) this.loading = true;

            this.axiosPOST(url, data).then(data => {
                this.errors = data.errors || {}; // errors
                data.id && (this.id = data.id); // save bill id

                // set updated timestamp
                data.updated_at && (this.lastSaveTime = new Date(data.updated_at * 1000));
                data?.bill?.link && (this.link = data.bill.link); // save link

                // set status
                if(status) this.status = status;

                // update counterparties
                EventBus.$emit('update:counterparties');

                loader && setTimeout(() => { this.loading = false }, 200);
                callback && callback(data);
            }).catch(data => {
                this.errors = data?.data?.errors || {};
                loader && setTimeout(() => { this.loading = false }, 200);
                callback && callback(data);
            })
        },


        // go to next step
        saveAndNext(){

            // For templates
            let template = this.template || this.status === 'template',
                status = null;

            if(template)
                status = 'template';
            if(this.status === 'paid')
                status = 'paid';
            if(this.status === 'issued')
                status = 'issued';

            // First step to next "sending to counterparty"
            if(!this.sendStep){
                this.save(status || 'issued', !template, true, data => {
                    if(data.success) this.sendStep = true;
                });
            }
        },

        sendNotifyClient(){
            if(!this.id) return;

            if(this.payerNotify && (this.payerEmail || this.payerPhone)){
                this.loading = true;
                this.axiosPOST('/ui/bills/' + this.id + '/send-notify', {
                    payer_email: this.payerEmail,
                    payer_phone: this.payerPhone
                }).then(data => {
                    this.finalLabel = true;
                }).finally(() => {
                    this.loading = false;
                })
            }
        },


        // Auto-save draft
        realTimeSaving(){
            if(this.disableRealtimeSaving || this.template || this.sendStep) return;

            clearTimeout(this.realTimeSavingTimer);
            this.realTimeSavingTimer = setTimeout(() => {
                this.save('draft');
            }, 2000);
        },


        // Remove draft action
        removeDraft(){
            const errorMsg = "Произошла ошибка при удалении черновика";

            this.axiosPOST(`/ui/bills/${this.id}/remove-draft`).then((r) => {
                if(r.success)
                    this.$router.push('/bills');
                else
                    this.errorNotify(errorMsg);
            }).catch((r) => {
                // Unknown error
                !r?.data?.msg && this.errorNotify(errorMsg);
            }) ;
        },


        // Add position string
        addPosition(){
            this.positions.push({
                name: '',
                vendor_code: '',
                unit_price: '',
                count: '',
                units: '',
            })
        },


        // Add signature
        addSignature(){
            this.signature.push({
                position: '',
                full_name: '',
                signature_attachment_id: null,
            });

            this.signatureChanged = true;
        },


        // Remove Position by index
        removePosition(index){
            this.realTimeSaving();
            this.positions.splice(index, 1);
            if(!this.positions.length)
                this.addPosition();
        },

        // Remove Signature by index
        removeSignature(index){
            this.realTimeSaving();
            this.signature.splice(index, 1);
            if(!this.signature.length)
                this.addSignature();
            this.signatureChanged = true;
        },

        // Signature list affected
        signatureAffectChanged(){
            this.realTimeSaving();
            this.signatureChanged = true;
        },


        // calculate position sum
        positionCost(position){
            if(!position.count || !position.unit_price)
                return 0;

            return position.unit_price * position.count;
        },




        // load counterparty name
        // usage in getBillData
        loadCounterpartyData(){
            if(this.counterparty){
                let counterparty = this.$store.state.counterparties.find(i => i.id === this.counterparty);
                if(counterparty){
                    this.counterparty_name = counterparty.name;
                    this.counterparty_inn = counterparty.inn;
                    this.counterparty_kpp = counterparty.kpp;
                    this.counterparty_address = counterparty.legal_address;
                }
            }
        },


        // create new or edit selected bank (checking account)
        editCheckingAccount(bank){

            bank = bank || {};

            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                props: { data: bank },
                component: EditPopup,

                onClose: data => {
                    EventBus.$emit('app:update');
                    if(data?.bank?.id) this.checking_account = data.bank.id;
                }
            });
        },


        // save logo uuid
        logoAttachmentHandler(uuid){
            this.realTimeSaving();
            this.logo_uuid = uuid || null;
        },

        // save stamp uuid
        stampAttachmentHandler(uuid){
            this.realTimeSaving();
            this.stamp_uuid = uuid || null
        },


        // [disabled]
        /*onChooseCounterparty(){
            if(!this.counterparty)
                return;

            const counterparty = this.$store.state.counterparties.find(o => o.id === this.counterparty);

            // Set payer contacts
            if(~['draft', 'template'].indexOf(this.status)){

                let email, phone;

                if(counterparty || counterparty?.contacts.length) {
                    const contact = counterparty.contacts[0];
                    phone = contact?.phone || '';
                    email = contact?.email || '';
                }

                this.payerPhone = phone || '';
                this.payerEmail = email || '';
            }
        },*/


        /* on choose company
         * -> set def signatures */
        choosingCompany(disableAutosave){

            if(!disableAutosave)
                this.realTimeSaving(); // auto-save

            // get company from store
            let company = this.$store.getters.userAccounts.find(x => x.id === parseInt(this.account)),
                signature = [];

            // set VAT type
            this.nds_type_id = company.nds_type_id || null;


            // UPLOAD SIGNATURES
            // skip if signatures changed manually
            if(!this.signatureChanged) {

                // Director signature
                if (company.director_position || company.director_name) {

                    let loadedSignature = {
                        position: company.director_position,
                        full_name: company.director_name
                    }

                    // signature
                    if (company.director_signature) {
                        loadedSignature.signature_attachment_id = company.director_signature.id || null;
                        loadedSignature.signature_attachment = {uuid: company.director_signature.uuid};
                    }

                    signature.push(loadedSignature);
                }


                // Accountant signature
                if (company.accountant_position && company.accountant_name) {
                    let loadedSignature = {
                        position: company.accountant_position,
                        full_name: company.accountant_name
                    }

                    // signature
                    if (company.accountant_signature) {
                        loadedSignature.signature_attachment_id = company.accountant_signature.id || null;
                        loadedSignature.signature_attachment = {uuid: company.accountant_signature.uuid};
                    }

                    signature.push(loadedSignature);
                }

                this.signature = signature; // push signatures

            }



            // Company logo
            if(company.logo_attachment && !this.logo_id){
                this.logo_id = company.logo_attachment.id;
                this.logo_uuid = company.logo_attachment.uuid;
            }

            // Company stamp
            if(company.stamp_attachment && !this.stamp_id) {
                this.stamp_id = company.stamp_attachment.id || null;
                this.stamp_uuid = company.stamp_attachment.uuid || null;
            }


        },


        choosingCounterparty(item){

            this.realTimeSaving();
            if(item?.id) this.counterparty = item.id;

            // For counterparties from Autocomplete
            this.counterparty_name = item?.value;
            this.counterparty_inn = item?.data?.inn;
            this.counterparty_kpp = item?.data?.kpp;
            this.counterparty_address = item?.data?.address?.value;

            // get opf type
            let opfType = item?.data?.opf?.code;
            if(opfType){
                opfType = parseInt(opfType);
                opfType = this.$store.state.OPFTypes.find(i => i.code === opfType);
                if(opfType){
                    this.counterparty_type_id = opfType.id;
                    this.counterparty_type = opfType.name;
                }
            }
        },


        positionDataLimiter(){
            this.positions.map(data => {
                if(Number.parseFloat(data.count) < 1) data.count = 1;
                if(Number.parseFloat(data.unit_price) < 0) data.unit_price = 0;
            });
        },

        chooseBillVAT(VAT){
            if(VAT.id)
                this.nds_type_id = VAT.id;
        }
    },

    watch: {
        positions: {
            handler: 'positionDataLimiter',
            deep: true
        }
    },


    /**
     * Initialize Component
     */
    created(){

        // choose this company
        this.account = this.$store.state.account.id;
        this.choosingCompany(true);

        // load bill by id
        if(this.bill_id){
            this.id = this.bill_id;
            this.getBillData(this.bill_id);
        }

        // for blank
        else if(!this.template && !this.pay_before){
            let date = new Date();
            date.setDate(date.getDate() + 7);
            this.pay_before = date;
        }

        // if template used
        if(this.useTemplate)
            this.getBillData(this.useTemplate);

        // if no positions
        if(this.positions.length < 1)
            this.addPosition();

        // if no signatures
        if(this.signature.length < 1)
            this.addSignature();

        // choose checking account if once
        if(this.checkingAccounts.length === 1)
            this.checking_account = this.checkingAccounts[0].id


    }
}
</script>
