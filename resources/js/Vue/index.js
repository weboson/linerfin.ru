/* STEP: Import libraries
--------------------------------------------*/
import Vue from 'vue';

// vue router
import VueRouter from 'vue-router';
Vue.use(VueRouter);

// bootstrap
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

// vuedals
import { default as Vuedals } from 'vuedals';
Vue.use(Vuedals);

// apexcharts
import VueApexCharts from 'vue-apexcharts'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

// VueJS vue2-datepicker module setup
// import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/locale/ru';


// Moment.js locale
import moment from 'moment';
moment.locale('ru');


// ------ end imports ------- //



// VueJS routes and stores
import router from './router.js';
import store from './store.js';



// VueJS Global components
Vue.component('application', require('./Components/Layout/Application.vue').default);
Vue.component('select-ui', require('./Components/UI/Form/select-ui.vue').default);
Vue.component('input-search-simple', require('./Components/UI/Form/input-search-simple').default);
Vue.component('checkbox-ui', require('./Components/UI/Form/checkbox-ui.vue').default);
Vue.component('price-format-ui', require('./Components/UI/Form/price-format-ui.vue').default);
Vue.component('date-picker', require('vue2-datepicker').default);
Vue.component('input-mask', require('./Components/UI/Form/input-mask').default);
Vue.component('input-file', require('./Components/UI/Form/file-upload/input-file').default);
Vue.component('input-autocomplete', require('./Components/UI/Form/input-autocomplete').default);
Vue.component('input-ui', require('./Components/UI/Form/input-ui').default);
Vue.component('error-label', require('./Components/UI/Form/error-label').default);


// Create VueJS event bus
export const EventBus = new Vue;


// Create Vue instance
const app = new Vue({
    el: '#linerfin',
    router,
    store
});



//// any scripts >>>

String.prototype.replaceAt = function(index, replacement) {
    if(index < 0) index = this.length + index;
    return this.substr(0, index) + replacement + this.substr(index + replacement.length);
}
