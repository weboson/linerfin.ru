import ModalButtons from "../../../UI/Modals/modal-buttons";
import {EventBus} from "../../../../index";
import moment from "moment";

import Axios from "~@vueMixins/axios";
import Utils from "~@vueMixins/utils";
import BudgetItemsMixin from "../../Settings/BudgetItemSettings/mixins/BudgetItemsMixin";

import BudgetItemCreate from "~@vueComponents/Pages/Settings/BudgetItemSettings/modals/CreatePopup"
import ProjectPopup from "./ProjectPopup";

export default {
    mixins: [ Axios, Utils, BudgetItemsMixin ],
    props: {
        data: Object
    },
    data(){
        return {

            id: null,
            type: '',
            plannedOperation: false,
            amount: 0,
            currency: '0',
            checkingAccount: '',
            date: new Date,
            dateFirstIncome: false,
            dateLastIncome: false,

            project_id: null,
            project_name: null,
            project: null,

            budget_item_id: null,
            budget_item_name: null,
            budget_item: null,

            periodicity: 'month',
            description: '',

            // counterparty
            counterparty_id: null,
            counterparty_name: '',
            counterparty_inn: '',
            counterparty_kpp: '',
            counterparty_address: '',
            counterparty_type: '',
            counterparty: {},

            errors: {},

            periodicityStack: [
                { value: 'day', name: 'Каждый день'},
                { value: 'week', name: 'Каждую неделю'},
                { value: 'month', name: 'Каждый месяц'},
                { value: 'year', name: 'Каждый год'}
            ],

            currencies: [
                { id: '0', name: 'RUB'}
            ]
        }
    },

    components: { ModalButtons },

    methods: {
        save(){

            if(!this.validateData(this.formData))
                return;

            const url = '/ui/transactions/' + (this.id ? this.id + '/' : '') + 'save'

            this.errors = {};
            this.axiosPOST(url, this.formData).then(response => {
                this.$emit('vuedals:close');
                EventBus.$emit('app:update');
                EventBus.$emit('transactions:update');
            }).catch(r => {
                if(r.data?.errors)
                    this.errors = r.data.errors || {};
                else
                    this.errors = {};
            })
        },

        clearError(field){
            if(this.errors.hasOwnProperty(field))
                this.errors[field] = undefined;
        },

        projectsPopup(){
            self = this

            this.$emit('vuedals:new', {
                name: 'center-modal choose-budget-item-popup',
                component: ProjectPopup,
                dismissable: false,
                escapable: true,
                props: { },
                onClose(args){

                    if(!args || args instanceof Event)
                        return;

                    if(args.project) {
                        self.chooseProject(args.project);
                        self.project_name = args.project?.name;
                        self.project = args.project;
                    }
                }
            });
        },

        counterpartiesPopup(){
            this.projectsPopup();
        },

        budgetItemsPopup(){
            const self = this;

            this.$emit('loader:on');
            this.getGroupsAndTypes(this.type)
                .then(response => {
                    this.$emit('vuedals:new', {
                        name: 'center-modal choose-budget-item-popup',
                        component: BudgetItemCreate,
                        dismissable: false,
                        escapable: true,
                        props: {
                            category: this.type,
                            groups: response.groups,
                            types: response.types
                        },
                        onClose(args){

                            if(!args || args instanceof Event)
                                return;

                            if(args.item) {
                                self.chooseBudgetItem(args.item);
                                self.budget_item_name = args.item?.name;
                                self.budget_item = args.item;
                            }
                            // console.log('choose', args.item)
                        }
                    })
                })
                .finally(() => { this.$emit('loader:off')})
        },

        cancel(){
            this.$emit('vuedals:close', { action: 'canceled' });
        },

        validateData(data){
            let errorsExists = false;

            if(!data.scheduled){
                if(!data.date){
                    this.errorNotify("Укажите дату операции");
                    errorsExists = true;
                }
            }
            else{
                if(!data.scheduled_date_start || !data.scheduled_date_end){
                    this.errorNotify("Укажите период плановой операции");
                    errorsExists = true;
                }
                else if(data.scheduled_date_start >= data.scheduled_date_end){
                    this.errorNotify("Неверный период плановой операции");
                    errorsExists = true;
                }

                if(!data.periodicity){
                    this.errorNotify("Укажите периодичность операции");
                    errorsExists = true;
                }
            }

            return !errorsExists;
        },

        chooseCounterparty(item){

            this.clearError('counterparty_id');

            this.counterparty = item;
            this.counterparty_id = item?.id || null;

            // For counterparties from Autocomplete
            this.counterparty_name = item?.value || '';
            this.counterparty_inn = item?.data?.inn || '';
            this.counterparty_kpp = item?.data?.kpp || '';
            this.counterparty_address = item?.data?.address?.value || '';

            // find counterparty type
            if(item?.data?.opf?.short){
                let opf_type = this.$store.state.OPFTypes.find(type => type?.short_name === item.data.opf.short)
                this.counterparty_type = opf_type ? opf_type.id : null;
            }
            else {
                this.counterparty_type = null;
            }

        },

        chooseProject(project){
            this.clearError('project_id');
            this.project_id = project.id || null;
            this.project_name = project.name;
        },

        chooseBudgetItem(item){
            this.clearError('budget_item_id');
            this.budget_item_id = item.id || null;
            this.budget_item_name = item.name;
        },

        loadData(){
            if(!this.data) return;
            const data = this.data;
            const availableProperties = ['id', 'type', 'amount', 'currency', 'project', 'project_id', 'budget_item', 'budget_item_id', 'description', 'counterparty_id', 'counterparty_name', 'counterparty_inn', 'counterparty_kpp', 'counterparty_address', 'counterparty_type', 'counterparty']

            // transaction date
            if(data.date) this.date = new Date(data['date']);

            // counterparty
            if(data.counterparty) {
                let counterparty = data.counterparty;
                this.counterparty_name = counterparty.name;
                this.counterparty_inn = counterparty.inn;
                this.counterparty_kpp = counterparty.kpp;
            }

            // project
            if(data.project)
                this.project_name = data.project.name;

            // budget item
            if(data.budget_item)
                this.budget_item_name = data.budget_item.name;

            // checking account
            if(data.type === 'income' && data.to_ca_id)
                this.checkingAccount = data.to_ca_id;
            if(data.type === 'expense' && data.from_ca_id)
                this.checkingAccount = data.from_ca_id;
            if(data.type === 'transfer'){
                if(data.from_ca_id && this.hasOwnProperty('from_ca_id'))
                    this.from_ca_id = data.from_ca_id;
                if(data.to_ca_id && this.hasOwnProperty('to_ca_id'))
                    this.to_ca_id = data.to_ca_id;
            }




            availableProperties.forEach(key =>{
                if(data.hasOwnProperty(key) && this.hasOwnProperty(key))
                    this[key] = data[key];
            });
        }
    },

    computed: {

        baseFormData(){
            return {}
        },

        formData(){

            // Collect data
            let data = {
                scheduled: this.plannedOperation,
                amount: this.amount,
                currency: this.currency,
                date: this.date,
                scheduled_date_start: this.dateFirstIncome,
                scheduled_date_end: this.dateLastIncome,
                project_id: this.project_id,
                budget_item_id: this.budget_item_id,
                periodicity: this.periodicity,
                description: this.description,
                type: this.type
            };

            if(this.counterparty_id)
                data.counterparty_id = this.counterparty_id;

            else if(this.counterparty_name){
                data.counterparty_name = this.counterparty_name;
                data.counterparty_inn = this.counterparty_inn || '';
                data.counterparty_kpp = this.counterparty_kpp || '';
                data.counterparty_address = this.counterparty_address || '';
                data.counterparty_type = this.counterparty_type || '';
            }

            Object.assign(data, this.baseFormData);

            // Format dates
            const dateFields = ['date', 'scheduled_date_start', 'scheduled_date_end'];

            for(let date_field of dateFields){
                if(data[date_field] && data[date_field] instanceof Date)
                    // data[date_field] = data[date_field].getTime()/1000;
                    data[date_field] = moment(data[date_field]).format('DD.MM.YYYY HH:mm:ss');
            }

            return data;
        }
    },

    watch: {
        amount(value){
            value = parseFloat(value);
            if(isNaN(value) || value < 0)
                value = 0;

            this.amount = value;
        },

    },


    created(){
        if(this.data)
            this.loadData();

        // this.counterparty_name = '123';
    }


}
