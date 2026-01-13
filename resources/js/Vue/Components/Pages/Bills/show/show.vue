<script>
import Utils from "../../../../mixins/utils";
import Axios from "../../../../mixins/axios";
import Formats from "../../../../mixins/formats";
import BillUtils from "../bill-utils";
import RejectPopup from "../popups/reject-bill";
import clipboard from "../../../../mixins/clipboard";
import PageOffsetMixin from "../../../../mixins/page-offset";

import StatusComponent from "./status";
import PayProgress from './pay-progress';
import TransactionsComponent from "./transactions";

export default {
    props: ['bill_id'],
    mixins: [Utils, Axios, Formats, BillUtils, clipboard, PageOffsetMixin],
    components: {
        "bill-status": StatusComponent,
        "transactions": TransactionsComponent,
        "pay-progress": PayProgress
    },
    data(){
        return {
            bill: {}, // Bill's data

            billRelations: [
                'positions.nds_type',
                'signature_list_with_attachments',
                'counterparty',
                'checking_account',
                'account',
                'logo_attachment',
                'stamp_attachment',
                'transactions',
                'transactions.toCheckingAccount',
                'transactions.counterparty',
                'transactions.budgetItem',
                'transactions.project'
            ],

            loading: true, // Show loader
        }
    },


    // Getters
    computed: {

        billPositions(){
            return this?.bill?.positions || []
        },

        billSignature(){
            return this?.bill?.signature_list || this?.bill?.signature_list_with_attachments || []
        },

        account(){
            return this.bill?.account;
        },

        VAT(){
            if(this.bill.nds_type_id)
                return this.$store.state.ndsTypes.find(i => i.id === this.bill.nds_type_id) || null;

            return null;
        },

        VATAmount(){
            if(!this.VAT || !this.bill.sum || !this.VAT.percentage) return 0;
            return parseFloat(this.bill.sum) * parseFloat(this.VAT.percentage) / 100;
        },

        checkingAccount(){
            return this.bill?.checking_account;
        },

        counterpartyName(){
            if(!this?.bill?.counterparty){
                if(this.bill.counterparty_name)
                    return this.bill.counterparty_name;

                return null;
            }

            return this.bill.counterparty.name;
        },

        payBefore(){
            if(!!this.bill?.pay_before){
                let date = this.formatDate(this.bill.pay_before);
                if(date)
                    return `до ${date}`;
            }

            return '';
        },

        downloadLink(){
            if(this.bill?.link)
                return '/bill-download-' + this.bill?.link;

            return '#';
        },

        printLink(){
            if(this.bill?.link)
                return '/bill-' + this.bill?.link + "?print";

            return '#';
        },

        // link for img[src]
        stampAttachmentSrc(){
            if(!this.bill?.stamp_attachment?.uuid)
                return null;
            return `/ui/attachments/${this.bill.stamp_attachment.uuid}`;
        },

        mayBeReject(){
            return !(this.bill?.status === 'template' || this.bill?.status === 'rejected');
        },

    },



    // Methods
    methods: {
        getData(){
            this.loading = true;

            this.axiosGET('/ui/bills/' + this.bill_id + '?with=' + this.billRelations.join(','))
                .then(data => {
                    this.loading = false;
                    data.bill && (this.bill = data.bill);
                });
        },

        /*setStatusOld(status){
            let id = this.bill.id,
                bill = this.bill;
            if(!id) return;
            EventBus.$emit('loader:on');
            this.axiosPOST('/ui/bills/' + id + '/set-status', { status }).then(data => {
                // update bill data
                if(data.bill) {
                    bill.status = data.bill?.status;
                    bill.paid_at = data.bill?.paid_at;
                    bill.issued_at = data.bill?.issued_at;
                    bill.realized_at = data.bill?.realized_at;
                    bill.rejected_at = data.bill?.rejected_at;
                }
                setTimeout(() => EventBus.$emit('loader:off'), 200);
            });
        },*/


        deleteBill(){
            if(!confirm('Подтвердите удаление счета ' + this.billNumber(true)))
                return;

            this.loading = true;
            const errorMsg = "Не удалось удалить счёт"
            const request = this.axiosPOST(`/ui/bills/${this.bill_id}/delete`);

            request.then(data => {
                if(data.success) this.$router.push('/bills');
                else this.errorNotify(errorMsg);
            });

            // catch
            request.catch(response => {
                !response?.data?.msg && this.errorNotify(errorMsg)
            });
        },



        getAttachmentImage(attachment){
            if(!attachment?.uuid)
                return null;
            return `/ui/attachments/${attachment.uuid}`;
        },



        // get bill number with date
        billNumber(withDate, def){
            def = def || '';

            if(!this.bill.num) return def;

            let number;
            number = `№${this.bill.num}`;

            if(withDate && this.bill?.issued_at){
                let date = this.formatDate(this.bill.issued_at);
                if(date) number += (' от ' + date);
            }

            return number;
        },

        positionUnitCount(position){
            let count = position.count || '';
            if(position.units)
                count += ` ${position.units}`;

            return count;
        },

        rejectPopup(){

            let props = {
                id: this.bill_id,
                phone: this.bill?.payer_phone,
                email: this.bill?.payer_email,
                options: { billRelations: this.billRelations }
            };

            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                component: RejectPopup,
                props: props,

                onClose: (data) => {
                    if(data?.success && data.bill)
                        this.bill = data.bill;

                    if(data.msg)
                        this.successNotify(data.msg);
                }
            })
        },

        shareBill(){
            let link = [];
            link.push(window.location.protocol + "//");
            link.push(APPDATA?.DOMAIN ? APPDATA.DOMAIN : 'linerfin.ru');
            link.push('/bill-' + this.bill?.link);

            this.copyToClipboard(link.join('')).then(() => {
                this.successNotify('Ссылка на счёт скопированна', 'Поделиться');
            }).catch(() => {
                this.errorNotify('Не удалось подготовить ссылку', 'Поделиться');
            })
        }
    },

    created(){
        this.getData();
    }
}
</script>

<style lang="sass">

</style>

<template>

    <div class="top-header-wrapper bill-view-page bill-namespace">

        <!-- header -->
        <div class="top-header" :class="topHeaderOffset">
            <div class="breadcrumbs-wrapper">
                <a href="#" class="back-link"
                   @click.prevent="$router.push('/bills')">
                    <b-icon-arrow-left class="icon-left"/>
                    Назад
                </a>
                <ul class="breadcrumbs">
                    <li>Счета</li>
                    <li>{{ billNumber(true, 'Просмотр') }}</li>
                </ul>
            </div>
            <div class="header-wrapper d-flex justify-content-between align-items-center">
                <header class="title">
                    <img src="/assets/images/icons/bill.svg" class="icon-left">
                    <span>
                    {{ billNumber(true, 'Просмотр') }}
                </span>

                    <bill-status :bill="bill"
                                 v-on:load-status="i => this.loading = i"
                                 v-on:commit="data => this.bill = data.bill"
                                 :options="{ billRelations }"/>

                </header>

                <pay-progress :bill="bill"/>
            </div>
        </div>
        <!-- end header -->



        <!-- main content -->
        <div class="container-fluid top-header__content">


            <!-- toolbar -->
            <div class="toolbar">

                <div class="left">

                    <b-button variant="primary" @click="$router.push(`/bills/${bill_id}/edit`)">
                        Редактировать
                    </b-button>

                    <a @click.prevent="deleteBill" href="#"
                       class="btn btn-160 btn-outline-danger">
                        Удалить
                    </a>
                </div>

                <div class="right">
                    <a :href="downloadLink" target="_blank" class="btn btn-160 btn-outline-secondary">
                        <b-icon-download class="icon-left"/>
                        <span>Скачать</span>
                    </a>
                    <a :href="printLink" target="_blank" class="btn btn-160 btn-outline-secondary">
                        <b-icon-printer class="icon-left"/>
                        <span>Печать</span>
                    </a>
                    <a href="#" class="btn btn-160 btn-outline-secondary"
                       @click.prevent="shareBill">
                        <b-icon-share class="icon-left"/>
                        <span>Поделиться</span>
                    </a>
                </div>
            </div>
            <!-- end toolbar -->


            <!-- content -->
            <div class="content loader-wrapper bill-view">

                <!-- loader -->
                <transition name="fade-out">
                    <div class="loader centered" v-if="loading">
                        <b-spinner variant="primary" label="Загрузка"></b-spinner>
                    </div>
                </transition>



                <!-- bill header -->
                <section class="bill-view__header">
                    <div class="left">

                        <div class="bill-counterparty">
                            {{ counterpartyName }}
                        </div>
                        <div class="bill-number">
                            {{ billNumber(true, '—') }}
                        </div>

                    </div>

                    <div class="right">
                        <div class="bill-sum">
                            <price-format-ui :amount="bill.sum"/>
                        </div>
                        <div class="bill-pay-date" v-if="payBefore">
                            {{ payBefore }}
                        </div>
                    </div>
                </section>



                <!-- bill positions table -->
                <table class="table bill-view__positions">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Товар или услуга</th>
                        <th>Артикул</th>
                        <th>Цена за ед.</th>
                        <th>Количество</th>
                        <th>НДС</th>
                        <th>Сумма</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-for="(pos, i) in billPositions">
                        <td>{{ i+1 }}</td>
                        <td>{{ pos.name }}</td>
                        <td>{{ pos.vendor_code }}</td>
                        <td>
                            <price-format-ui class="justify-content-end without-currency" :amount="pos.unit_price"/>
                        </td>
                        <td>{{ positionUnitCount(pos) }}</td>
                        <td>
                            <price-format-ui v-if="pos.nds_type && pos.nds_type.percentage"
                                             class="justify-content-end without-currency"
                                             :amount="pos.unit_price * pos.count * pos.nds_type.percentage / 100"/>
                        </td>
                        <td>
                            <price-format-ui class="align-right" :amount="pos.unit_price * pos.count"/>
                        </td>
                    </tr>
                    </tbody>
                </table>




                <!-- table footer -->
                <section class="row row-mg">

                    <!-- recipient details -->
                    <div class="col-6 col-pd bill-view__recipient-details">
                        <p class="fw-500">Реквизиты получателя</p>
                        <table class="custom-table th-150">
                            <template v-if="account">
                                <tr v-if="account.inn">
                                    <th>ИНН</th>
                                    <td>{{ account.inn }}</td>
                                </tr>
                                <tr v-if="account.kpp">
                                    <th>КПП</th>
                                    <td>{{ account.kpp }}</td>
                                </tr>
                                <tr v-if="account.ogrn">
                                    <th>ОГРН</th>
                                    <td>{{ account.ogrn }}</td>
                                </tr>
                                <tr v-if="account.address">
                                    <th>Адрес</th>
                                    <td>{{ account.address }}</td>
                                </tr>
                                <tr v-if="account.legal_address">
                                    <th>Юр. адрес</th>
                                    <td>{{ account.legal_address }}</td>
                                </tr>
                            </template>

                            <template v-if="checkingAccount">
                                <tr v-if="checkingAccount.num">
                                    <th></th>
                                    <td>{{ checkingAccount.num }}</td>
                                </tr>
                                <tr v-if="checkingAccount.bank_bik">
                                    <th>БИК Банка</th>
                                    <td>{{ checkingAccount.bank_bik }}</td>
                                </tr>
                                <tr v-if="checkingAccount.bank_name">
                                    <th>Банк</th>
                                    <td>{{ checkingAccount.bank_name }}</td>
                                </tr>
                            </template>
                        </table>
                    </div>


                    <!-- bill sum -->
                    <div class="col-6 col-pd border-left bill-view__sum">
                        <div class="bill-nds-sum">
                            <div v-if="VAT"
                                 class="bill-nds-sum__item">
                                <div v-if="VAT.percentage === null">Без НДС</div>
                                <div v-else>НДС {{ VAT.name }}</div>

                                <div v-if="VAT.percentage !== null">
                                    <price-format-ui class="align-right" :amount="VATAmount"/>
                                </div>
                            </div>
                        </div>
                        <div class="bill-sum">
                            <span>Общая сумма</span>
                            <price-format-ui :amount="bill.sum"/>
                        </div>
                    </div>
                </section>


                <!-- bill signature -->
                <section class="bill-view__signature">
                    <div v-for="(signature, i) in billSignature"
                         class="bill-signature">
                        <div class="bill-signature__position">{{ signature.position }}</div>
                        <div v-if="signature.signature_attachment || signature.stamp_attachment"
                             class="bill-signature__images">
                            <img v-if="signature.signature_attachment" class="signature"
                                 :src="getAttachmentImage(signature.signature_attachment)">

                            <!-- stamp attachment -->
                            <img v-if="stampAttachmentSrc && i === 0" class="stamp"
                                 :src="stampAttachmentSrc">
                        </div>
                        <div class="bill-signature__full-name">{{ signature.full_name }}</div>
                    </div>
                </section>


                <div class="bill-comment my-5" v-if="bill.comment">
                    <p class="fw-500">Комментарий для контрагента</p>
                    <p>{{ bill.comment }}</p>
                </div>



                <!-- bill footer -->
                <section class="bill__footer">
                    <p class="fw-500">История счёта</p>
                    <p><span class="text-secondary">{{ formatDate(bill.created_at, true) }}</span> создан</p>
                    <p><span class="text-secondary">{{ formatDate(bill.updated_at, true) }}</span> изменён</p>
                </section>


            </div>
            <!-- end content -->


            <div class="toolbar mt-4">
                <div class="left">
                    <a v-if="mayBeReject" href="#" class="btn btn-160 btn-outline-secondary"
                       @click.prevent="rejectPopup">
                        Отменить счёт
                    </a>
                </div>
            </div>


            <!-- transactions -->
            <transactions v-if="bill.transactions && bill.transactions.length"
                :transactions="bill.transactions"
                :loader="loading"
                @load-status="i => this.loading = !!i"
                @bill:update="getData"/>

        </div>
    </div>
</template>
