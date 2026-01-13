import VueRouter from 'vue-router';

// Main routes
import MainPage from '~@vueComponents/Pages/Main/MainPage.vue';
import Counterparties from '~@vueComponents/Pages/Counterparties';
    import Counterparty from '~@vueComponents/Pages/Counterparties/show';

import Bills from '~@vueComponents/Pages/Bills';
    import BillEdit from '~@vueComponents/Pages/Bills/edit';
    import Bill from '~@vueComponents/Pages/Bills/show/show';

import Reports from '~@vueComponents/Pages/Reports/Reports';
    import ReportPnL from '~@vueComponents/Pages/Reports/PnL'; // ОПиУ (P&L)
    import ReportFoF from '~@vueComponents/Pages/Reports/FoF'; // ДДС


import DocPage from '~@vueComponents/Pages/Docs';


import CalendarPage from "~@vueComponents/Pages/Calendar/CalendarPage";


import TestPlace from "~@vueComponents/Pages/TestPlace";


// Setting's routes
import SettingsMain from '~@vueComponents/Pages/Settings/SettingsMain.vue';
    import CommonSettings from '~@vueComponents/Pages/Settings/Common';
    import CompaniesSettings from '~@vueComponents/Pages/Settings/Companies';
    import UsersSettings from '~@vueComponents/Pages/Settings/Users';
    import ProjectsSettings from '~@vueComponents/Pages/Settings/ProjectSettings/Projects';
    import BudgetItemsSettings from '~@vueComponents/Pages/Settings/BudgetItemSettings/BudgetItemsWrapper';
    import BankList from '~@vueComponents/Pages/Settings/CheckingAccounts/list';

export default new VueRouter({
    mode: 'history',
    routes: [

        { path: '/', name: 'main-page', component: MainPage },

        // Counterparty
        { path: '/counterparties', name: 'counterparties', component: Counterparties },
            { path: '/counterparties/:counterparty_id', component: Counterparty, props: true },

        // Bills
        { path: '/bills', name: 'bills', component: Bills },
            { path: '/bills/create', component: BillEdit },
            { path: '/bills/:bill_id', component: Bill, props: true },
            { path: '/bills/:bill_id/edit', component: BillEdit, props: true },

            // Bill Templates
            { path: '/bills/templates/create', component: BillEdit, props: { template: true } },
            { path: '/bills/templates/:bill_id', component: Bill },
            { path: '/bills/templates/:bill_id/edit', component: BillEdit, props: (route) => ({ template: true, bill_id: route.params.bill_id }) },
            { path: '/bills/templates/:useTemplate/use', component: BillEdit, props: true },


        // Reports
        { path: '/reports', name: 'reports', component: Reports },
            { path: '/reports/pnl', component: ReportPnL },
            { path: '/reports/flow-of-funds', component: ReportFoF },


        // Calendar
        { path: '/calendar', name: 'calendar', component: CalendarPage },

        // Documents
        { path: '/docs', name: 'documents', component: DocPage },


        // Setting's routes
        { path: '/settings', component: SettingsMain,
          children: [
              { path: '', name: 'settings', component: CommonSettings },
              { path: 'companies', component: CompaniesSettings },
              { path: 'banks', component: BankList },
              { path: 'users', component: UsersSettings },
              { path: 'projects', component: ProjectsSettings },
              { path: 'budget-items', component: BudgetItemsSettings },
          ]
        },



        // Test page
        // { path: '/debaggu', name: 'testplace', component: TestPlace },

    ],
    linkActiveClass: 'active',
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return { x: 0, y: 0 }
        }
    }
})
