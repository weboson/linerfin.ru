<template>
    <div class="wrapper" :class="wrapperClass">

        <transition name="fade-out">
            <div class="loader centered" v-if="loading" style="z-index: 150;">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
        </transition>

        <section id="top-panel" class="container-fluid" :class="topHeaderOffset">
            <div class="row">
                <div @click="$router.push('/')" class="linerfin-logo-container">
                    <div class="linerfin-logo"></div>
                </div>
                <div class="col panel-base">
                    <div class="left d-flex flex-row">

                        <choose-account></choose-account>

                        <div class="total-balance">
                            <div class="title">На всех рублевых счетах</div>
                            <price-format-ui class="balance" :amount="$store.state.totalBalance"></price-format-ui>
                        </div>
                    </div>

                    <div class="right d-flex flex-row">
                        <div>
                            <a href="#" class="help-me">
                                <b-icon-question-circle class="icon-left"></b-icon-question-circle>
                                <span>Помощь</span>
                            </a>
                        </div>
                        <div>
                            <profile-button :dark-view="dark" @dark-mod="updateDarkMod"></profile-button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="content-wrapper" v-if="!loading">
            <sidebar-component :dark="dark"></sidebar-component>
            <main>
                <router-view></router-view>
            </main>

            <transition name="fade">
                <div class="background-loader" v-if="backgroundLoading && !loading">
                    <b-spinner variant="primary" label="Загрузка"></b-spinner>
                    <div class="title">Операция выполняется</div>
                </div>
            </transition>
        </div>

        <demo-mod v-if="demoAccount"></demo-mod>

        <vuedal></vuedal>

    </div>
</template>

<script>
import SidebarComponent from './Sidebar';
import Axios from '../../mixins/axios';
import PageOffsetMixin from '../../mixins/page-offset';

import { Component as Vuedal } from 'vuedals';
import { EventBus } from "../../index";
import ChooseAccount from "./ChooseAccount";
import ProfileButton from "./ProfileButton";
import DemoMod from "./DemoMod";

export default {
    name: "Application",
    mixins: [ Axios, PageOffsetMixin ],
    components: { SidebarComponent, Vuedal, ChooseAccount, ProfileButton, DemoMod },

    data(){
        return {
            loading: true,
            backgroundLoading: false,
            dark: false
        }
    },



    computed: {
        demoAccount(){
            return !!this.account?.is_demo;
        },

        account(){
            return this.$store.state.account;
        },

        routerName(){
            return this.$route?.name;
        },

        topHeaderOffset(){
            if(this.routerName === 'main-page' && this.pageOffset > 0)
                return 'with-offset';
            return '';
        },

        wrapperClass(){
            let classes = [];
            if(this.dark)
                classes.push('dark-view');
            if(this.demoAccount)
                classes.push('wrapper__demo-mod');

            return classes.join(' ');
        }
    },


    methods: {
        updateApplication(){
            this.axiosGET('/ui/bootstrap').then(response => {

                response.checking_accounts && this.$store.commit('setCheckingAccounts', response.checking_accounts);
                response.budget_items && this.$store.commit('setBudgetItems', response.budget_items);
                response.counterparties && this.$store.commit('setCounterparties', response.counterparties);
                response.counterparty_categories && this.$store.commit('setCounterpartyCategories', response.counterparty_categories);
                response.projects && this.$store.commit('setProjects', response.projects);
                response.account && this.$store.commit('setAccount', response.account);
                response.nds_types && this.$store.commit('setNDSTypes', response.nds_types);
                response.opf_types && this.$store.commit('setOPFTypes', response.opf_types);
                response.budget_item_types && this.$store.commit('setBudgetItemTypes', response.budget_item_types);

                // currencies
                this.$store.commit('setCurrencies', [
                    { id: '0', name: 'RUB'}
                ]);

                // Balances
                if(response.balance !== undefined){
                    this.$store.commit('setMonthBalances', response.balance);
                    this.$store.commit('setTotalBalance', response.balance.total || 0);
                }

                setTimeout(() => {
                    this.loading = false;
                }, 500);

            });
        },

        updateCounterparties(){
            this.axiosGET('/ui/counterparties?with=category,contacts,accounts,opf&trashed').then(r => {
                this.$store.commit('setCounterparties', r.counterparties || []);
            })
        },

        updateAccount(){
            this.axiosGET('/ui/account').then(r => {
                this.$store.commit('setAccount', r.account || {});
            });
        },

        updateProjects(){
            this.axiosGET('/ui/projects').then(r => {
                this.$store.commit('setAccount', r.projects || {});
            });
        },

        updateBudgetItems(){
            this.axiosGET('/ui/budget-items').then(r => {
                this.$store.commit('setBudgetItems', r.budget_items || {});
            });
        },

        updateCheckingAccounts(){
            this.axiosGET('/ui/banks').then(r => {
                this.$store.commit('setCheckingAccounts', r.banks || []);
            });
        },


        closeChoosingAccount(){
            this.choosingAccount = false;
        },

        updateDarkMod(dark){
            this.dark = !!dark;
        }
    },



    created(){
        this.updateApplication();
        EventBus.$on('app:update', this.updateApplication);
        EventBus.$on('update:account', this.updateAccount);
        EventBus.$on('update:counterparties', this.updateCounterparties);
        EventBus.$on('update:banks', this.updateCheckingAccounts);
        EventBus.$on('update:projects', this.updateProjects);
        EventBus.$on('update:budget-items', this.updateBudgetItems);

        // background loader
        EventBus.$on('loader:on', () => this.backgroundLoading = true);
        EventBus.$on('loader:off', () => this.backgroundLoading = false);
    }
}
</script>
