<script>

import axios from "../../../../mixins/axios";
import utils from "../../../../mixins/utils";
import { EventBus } from "../../../../index";

// edit popup
import EditPopup from "./edit-popup";
import BalanceCorrectPopup from "./balance-correcting";

export default {
    name: "bank-list",
    mixins: [ axios, utils ],

    data(){
        return {
            // isDemo: false,
            selected: [],
            allSelected: false,

            loading: false
        }
    },

    computed: {

        banks(){
            return this.$store.state.checkingAccounts;
        },

        existSelect(){
            return !! this.selected.length;
        },

        isPartitionSelected(){
            return !!(this.selected.length && this.selected.length < this.banks.length);
        },
        isDemo() {
            console.log(location.host.split('.')[0], location.host.split('.')[0] === 'demo')
            return location.host.split('.')[0] === 'demo';
        }

    },


    watch: {
        allSelected: function(){
            if(this.allSelected){
                this.selected = [];
                for(let option of this.banks){
                    this.selected.push(option.id);
                }
            }
            else{
                this.selected = [];
            }
        },

        selected: function(){
            if(this.banks.length && this.selected.length === this.banks.length)
                this.allSelected = true;
            else if(!this.selected || !this.selected.length)
                this.allSelected = false;
        }
    },


    methods: {
        editPopup(bank, createNew){

            if(createNew)
                bank = {};
            else{
                if(!bank && this.selected[0]){
                    let bank_id = this.selected[0];
                    for(let i in this.banks){
                        if(this.banks[i].id === bank_id)
                            bank = this.banks[i];
                    }
                }
            }

            this.selected = [];

            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                props: { data: bank },
                component: EditPopup,

                onClose: data => {
                    EventBus.$emit('app:update');
                }
            });
        },

        balanceCorrectPopup(bank){

            if(!bank && this.selected[0]){
                let bank_id = this.selected[0];
                for(let i in this.banks){
                    if(this.banks[i].id === bank_id)
                        bank = this.banks[i];
                }
            }

            if(!bank || !Object.keys(bank).length || !bank.id){
                this.errorNotify('Расчётный счёт не найден');
                return;
            }

            let data = { id: bank.id };
            if(bank.balance)
                data.old_balance = bank.balance;

            this.selected = [];

            this.$emit('vuedals:new', {
                name: 'center-modal',
                dismissable: false,
                escapable: true,
                props: data,
                component: BalanceCorrectPopup,

                onClose: data => {
                    if(data && data?.bank)
                        EventBus.$emit('app:update');
                }
            });
        },


        removeBanks(){
            if(!confirm('Подтверждаете удаление выбранных рассчетных счетов?'))
                return;

            this.axiosPOST(`/ui/banks/remove?ids=${this.selected.join(',')}`).then(r => {
                this.selected = [];
                EventBus.$emit('app:update');
            })
        },

        redirectToTochkaAuth() {
            location.href = location.protocol +'//'+location.host.split('.')[1] +'.'+location.host.split('.')[2] + '/api/v1/bank/tochka/connect/' + location.host.split('.')[0];
        }
    }
}


</script>

<template>
    <div class="container-fluid top-header__content settings__banks">

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="left">
                <a @click.prevent="editPopup(null, true)" href="#" class="btn btn-primary">
                    Добавить счёт
                </a>
                <a  @click.prevent="redirectToTochkaAuth()" v-show="!isDemo" href="#" id="tochka-connect" class="btn btn-primary">
                    Подключить счёт из Точки
                </a>
            </div>
        </div>
        <!-- end toolbar -->



        <!-- Table list -->
        <section class="table-type-1 loader-wrapper">

            <header></header>

            <div class="table-type-1__table">

                <!-- Table header -->
                <header :class="existSelect ? 'exists-selected' : ''">

                    <div class="cell select">
                        <b-form-checkbox v-model="allSelected" :indeterminate="isPartitionSelected" />
                    </div>

                    <template v-if="!existSelect">
                        <div class="cell name">
                            <span>Счёт</span>
                        </div>
                        <div class="cell num">
                            <span>Расчетный счёт</span>
                        </div>
                        <div class="cell bank">
                            <span>Банк</span>
                        </div>
                        <div class="cell sum">
                            <span>Сумма</span>
                        </div>
                    </template>

                    <div v-else class="cell select-toolbar">
                        <span class="select-count">{{ selected.length }}</span>

                        <template v-if="selected.length === 1">
                            <a @click.prevent="editPopup()" href="#" class="edit-btn">
                                Редактировать
                            </a>
                            <a @click.prevent="balanceCorrectPopup()" href="#" class="edit-ptn">
                                Корректировать сумму
                            </a>
                        </template>

                        <a @click.prevent="removeBanks" href="#" class="remove-btn">Удалить</a>
                    </div>

                </header>
                <!-- end table header -->


                <!-- Loader -->
                <transition name="fade-out">
                    <div class="loader" v-if="loading">
                        <b-spinner variant="primary" label="Загрузка"></b-spinner>
                    </div>
                </transition>
                <!-- end loader -->


                <div v-if="!banks.length" class="table-type-1__nothing">
                    Ничего не найдено
                </div>


                <!-- Table rows -->
                <div v-for="bank in banks"
                     class="table-type-1__table-row">
                    <div class="table-type-1__table-row-header">

                        <div class="cell select">
                            <b-form-checkbox v-model="selected" :key="bank.id" :value="bank.id"/>
                        </div>

                        <div class="cell name">
                            {{ bank.name }}
                            <div class="subtitle">Рублёвый счёт</div>
                        </div>
                        <div class="cell num">{{ bank.num }}</div>
                        <div class="cell bank">{{ bank.bank_name }}</div>
                        <div class="cell sum">
                            <price-format-ui :amount="bank.balance"/>
                        </div>
                    </div>
                </div>
                <!-- end table rows -->

            </div>
        </section>
    </div>
</template>
