<template>
    <div class="bill__choose-status bill-status" :class="status.name">
        <b-dropdown variant="link" :toggle-class="'text-decoration-none p-0 ' + status.name">
            <template #button-content>
                {{ status.title }}
            </template>

            <b-dropdown-item v-if="bill.status === 'rejected'" @click="issueBill">Выставить счет</b-dropdown-item>

            <template v-else>
                <template v-if="remainingBillSum">
                    <b-dropdown-item @click="pay">Оплачено</b-dropdown-item>
                    <b-dropdown-item @click="partPay">Частичная оплата</b-dropdown-item>
                </template>
                <b-dropdown-item v-if="!isRealized" @click="closeAsRealized">Закрыть актом</b-dropdown-item>
                <b-dropdown-item @click="cancelBill">Отменить счет</b-dropdown-item>
            </template>

            <b-dropdown-item v-if="false" @click="test">
                <b-icon-code-slash/> run test
            </b-dropdown-item>
        </b-dropdown>
    </div>
</template>

<script>

import axios from "../../../../mixins/axios";
import utils from "../../../../mixins/utils";
import popup from "../../../../mixins/popup-mix";

// popups
import RejectPopup from "../popups/reject-bill";

import {EventBus} from "../../../../index";

export default {
    name: "status",
    mixins: [ axios, utils, popup ],
    props: {
        bill: {},
        options: Object,
    },

    computed: {

        // Remaining sum
        // остаточная стоимость
        remainingBillSum(){

            if(this.bill.paid_at)
                return 0;

            if(this.bill.transactions_sum_amount)
                return parseFloat(this.bill.sum) - parseFloat(this.bill.transactions_sum_amount);

            let transactionAmount = 0;

            if(this.bill.transactions && this.bill.transactions.length) {
                this.bill.transactions.forEach(transaction => {
                    transactionAmount += parseFloat(transaction.amount);
                });
            }

            const remainingAmount = parseFloat(this.bill.sum) - transactionAmount;

            return remainingAmount >= 0 ? remainingAmount : 0
        },

        status(){

            const bill = this.bill;

            const statuses = {
                'template': 'Шаблон',
                'paid': 'Оплачен',
                'realized-paid': 'Реализован, оплачен',
                'realized': 'Реализован, не оплачен',
                'rejected': 'Отменен',
                'issued': 'Выставлен',
                'draft': 'Черновик'
            }

            for(let status in statuses){
                let name = statuses[status];
                if(bill.status === status) {
                    return { title: name, name: status }
                }
            }

            return { title: 'Выставлен', name: 'issued' }
        },

        bill_id(){
            return this.bill?.id || null
        },

        axiosURL(){
            return `/ui/bills/${this.bill_id}/set-status`
        },

        isRealized(){
            const status = this.status;
            return !!~['realized', 'realized-paid'].indexOf(status?.name);
        }
    },

    methods: {

        /**
         * Part pay
         */
        partPay(){

            // check bill
            if(this.bill.paid_at || !this.remainingBillSum){
                this.errorNotify('Счёт уже оплачен');
                return;
            }

            const popupConfigs = {
                validator: this.partPayValidator,
                inputType: 'number',
            };

            this.promptPopup('Введите сумму частичной оплаты', 0, popupConfigs)
                .then(data => {
                    this.setStatus('part-paid', { sum: data.value })
                })
                .catch(() => { /* nothing */ })

        },

        partPayValidator(value){
            value = parseFloat(value);
            if(!value || isNaN(value) || value < 1) {
                this.errorNotify('Задайте сумму частичной оплаты')
                return false
            }

            if(value > this.remainingBillSum) {
                this.errorNotify('Сумма не должна превышать ' + this.formatPrice(this.remainingBillSum))
                return false
            }
            return true
        },


        // full pay
        pay(){

            if(this.bill.transactions && !this.bill.transactions.length)
                this.setStatus('paid');
            else{
                this.confirmPopup('Будет создана операция на недостающую сумму ' + this.formatPrice(this.remainingBillSum))
                    .then(() => { this.setStatus('paid')})
                    .catch(() => { /* nothing */})
            }
        },

        // realized bill
        closeAsRealized(){
            this.confirmPopup('Будет создан акт закрытия счёта')
                .then(() => {
                    this.setStatus('realized');
                })
                .catch(() => { /* nothing */ })
        },

        // reject bill
        cancelBill(){

            const rejectPopup = new Promise((resolve, reject) => {

                let options = {};
                if(this.options.billRelations)
                    options.billRelations = this.options.billRelations;

                let props = {
                    id: this.bill_id,
                    phone: this.bill?.payer_phone,
                    email: this.bill?.payer_email,
                    options
                };

                this.$emit('vuedals:new', {
                    name: 'right-modal',
                    dismissable: false,
                    escapable: true,
                    component: RejectPopup,
                    props: props,

                    onClose: (data) => {
                        if(data?.success)
                            resolve(data)
                        else
                            reject()
                    }
                })
            })

            rejectPopup.then((response) => {
                EventBus.$emit('app:update');
                this.commitChanges({ bill: response.bill });
            })
            rejectPopup.catch(() => { /* nothing */ })
        },

        // issue bill
        issueBill(){
            this.confirmPopup('Счёт будет выставлен')
                .then(() => {
                    this.setStatus('issued');
                })
                .catch(() => { /* nothing */ })
        },




        // -----------------
        // >>[ Utilities ]<<

        // set status
        setStatus(status, data){

            // prepare data
            let postData = { status };
            if(data && typeof data === 'object')
                postData = Object.assign(postData, data);

            if(this.options?.billRelations) {
                postData.with = this.options.billRelations.join(',');
            }

            this.$emit('load-status', true);

            return new Promise((resolve, reject) => {
                this.axiosPOST(this.axiosURL, postData).then(response => {
                    EventBus.$emit('app:update');
                    this.commitChanges({ bill: response.bill });
                    resolve(response);
                }).catch(response => {
                    this.errorNotify("Не удалось сменить статус счёта");
                    reject(response);
                }).finally(() => {
                    this.$emit('load-status', false);
                })
            })
        },


        // parent component commit
        commitChanges(data){
            console.log('commit data', {data})
            this.$emit('commit', data);
        },


        test(){
            const component = this;
            const test1 = function(){
                component.notify("Запущен тест 1/2");
                return new Promise((resolve => {
                    setTimeout(() => {
                        component.setStatus('jfkniertuwnkd').finally(resolve);
                    }, 1000)
                }))
            }
            const test2 = function(){
                component.notify('Запущен тест 2/2');
                return new Promise(resolve => {
                    setTimeout(() => {
                        component.axiosPOST('/ui/bills/-666/set-status').finally(resolve)
                    }, 1000)
                })
            }

            test1().then(test2).then(() => {
                setTimeout(() => {
                    this.successNotify('Тест окончен')
                }, 200);
            })
        }

    },

}
</script>
