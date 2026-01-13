import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        account: {}, // Account
        user: {}, // Users
        budgetItems: [], // Budget items

        // Counterparties
        counterparties: [], // List
        counterpartyCategories: [], // Categories
        projects: [], // Projects
        checkingAccounts: [], // Checking accounts
        OPFTypes: [], // Organization accounts

        currencies: [], // Currencies

        settings: {}, // UI settings

        ndsTypes: [], // NDS

        // Balance
        monthBalance: {
            incomes: 0,
            expenses: 0,
            balance: 0
        },

        totalBalance: 0, // Total Balance

        budgetItemTypes: []
    },


    getters: {
        monthIncome: state => {
            return state.monthBalance.incomes;
        },

        monthExpense: state => {
            return state.monthBalance.expenses;
        },

        monthBalance: state => {
            return state.monthBalance.balance;
        },

        accountName: state => {
            if(state?.account?.name)
                return state.account.name;

            return '';
        },

        userAccounts: state => {
            if(state?.user?.accounts)
                return state.user.accounts;
            return [];
        },

        isDemo: state => {
            return !!state.account?.is_demo
        }
    },


    mutations:{

        setAccount: (state, account) => {
            state.account = account;
            if(account.user)
                state.user = account.user;
        },

        updateUserData: (state, user) => {
            state.user = user;
        },

        setSettings: (state, settings) => {
            if(typeof settings === 'object')
                state.settings = settings;
        },

        setBudgetItems: (state, budget_items) => {
            state.budgetItems = budget_items;
        },

        setCounterparties: (state, counterparties) => {
            state.counterparties = counterparties;
        },

        setCounterpartyCategories: (state, cats) => {
            state.counterpartyCategories = cats; // meow =^_^=
        },

        setProjects: (state, projects) => {
            state.projects = projects;
        },

        setCheckingAccounts: (state, accounts) => {
            state.checkingAccounts = accounts;
        },

        setCurrencies: (state, currencies) => {
            state.currencies = currencies;
        },

        setMonthBalances: (state, balances) => {
            state.monthBalance = balances;
        },

        setTotalBalance: (state, balance) => {
            state.totalBalance = balance;
        },

        setNDSTypes: (state, types) => {
            state.ndsTypes = types;
        },

        setOPFTypes: (state, types) => {
            state.OPFTypes = types;
        },

        setBudgetItemTypes: (state, types) => {
            state.budgetItemTypes = types
        }
    }
});
