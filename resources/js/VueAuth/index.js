import Vue from 'vue';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import { default as Vuedals } from 'vuedals';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(Vuedals);


// UI
Vue.component('select-ui', require('../Vue/Components/UI/Form/select-ui.vue').default);
Vue.component('input-search-simple', require('../Vue/Components/UI/Form/input-search-simple').default);
Vue.component('checkbox-ui', require('../Vue/Components/UI/Form/checkbox-ui.vue').default);
Vue.component('price-format-ui', require('../Vue/Components/UI/Form/price-format-ui.vue').default);
Vue.component('date-picker', require('vue2-datepicker').default);
Vue.component('input-mask', require('../Vue/Components/UI/Form/input-mask').default);
Vue.component('input-file', require('../Vue/Components/UI/Form/file-upload/input-file').default);
Vue.component('input-autocomplete', require('../Vue/Components/UI/Form/input-autocomplete').default);
Vue.component('input-ui', require('../Vue/Components/UI/Form/input-ui').default);

// Account
/*Vue.component('create-company', require('./Components/CreateCompany').default);
Vue.component('register-user', require('./Components/RegisterUser').default);
Vue.component('sign-in', require('./Components/SignIn').default);*/
import CreateCompanyWrapper from './Components/CreateCompany/CreateCompanyWrapper';
import RegisterUser from './Components/RegisterUser';

// import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/locale/ru';


export const EventBus = new Vue;

const app = new Vue({
    el: '#linerfin',
    components: { CreateCompanyWrapper, RegisterUser }
});


// Moment.js locale
import moment from 'moment';
moment.locale('ru');


String.prototype.replaceAt = function(index, replacement) {
    if(index < 0) index = this.length + index;
    return this.substr(0, index) + replacement + this.substr(index + replacement.length);
}
