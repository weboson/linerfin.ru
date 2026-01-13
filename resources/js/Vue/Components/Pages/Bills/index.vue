<template>
    <div class="top-header-wrapper bills-list-page bill-namespace">
        <div class="top-header" :class="topHeaderOffset">
            <header class="title">
                <img src="/assets/images/icons/bill.svg">
                <span>Счета</span>
            </header>
        </div>



        <div class="container-fluid">


            <!-- Main toolbar -->
            <div class="toolbar">
                <div class="left">
                    <router-link to="/bills/create" class="btn btn-primary">
                        <b-icon-file-text class="icon-left"/>
                        Выставить счёт
                    </router-link>

                    <a @click.prevent="createTemplate"
                       href="#" class="btn btn-outline-primary">
                        <b-icon-star class="icon-left"/>
                        Создать шаблон счёта
                    </a>

                </div>
            </div>


            <!-- Templates -->
            <section class="my-3 bill__templates" :class="showTemplates ? 'opened' : ''">
                <transition name="slide">
                    <div class="templates__list" v-if="showTemplates">
                        <header>
                            <p><strong>Шаблоны</strong></p>
                        </header>

                        <div class="template" v-for="template in templates">

                            <b-dropdown variant="link" toggle-class="text-decoration-none" no-caret>
                                <template #button-content>
                                    <b-icon-three-dots-vertical/>
                                </template>
                                <b-dropdown-item @click.prevent="openBill(template)" href="#">Создать счёт</b-dropdown-item>
                                <b-dropdown-item @click.prevent="editBill(template)" href="#">Редактировать</b-dropdown-item>
                                <b-dropdown-item @click.prevent="removeBills([template.id])" href="#">Удалить</b-dropdown-item>
                            </b-dropdown>

                            <div class="template__preview" @click="openBill(template)">
                                {{ getTemplatePreview(template, '#' + template.id) }}
                            </div>
                            <div class="template__name" @click="openBill(template)">
                                {{ template.num || "Без названия"}}
                            </div>
                            <div class="template__sum" @click="openBill(template)">
                                <price-format-ui :amount="template.sum"/>
                            </div>
                        </div>

                        <p v-if="!templates.length">
                            Создавайте шаблоны для быстрого выставления счетов
                        </p>
                    </div>
                </transition>

                <footer>
                    <a href="#" @click.prevent="toggleTemplates">
                        <b-icon-chevron-up class="icon-left"></b-icon-chevron-up>
                        <span v-if="showTemplates">Скрыть шаблоны</span>
                        <span v-else>Показать шаблоны</span>
                    </a>
                </footer>
            </section>


            <!-- Bill Filters -->
            <div class="toolbar filter">
                <div class="content">
                    <form @submit.prevent>

                        <div class="filter-form-row">
                            <date-picker type="date" input-class="form-control"
                                         v-model="filters.period" @input="applyFilter(500)"
                                         :range="true" range-separator=" - "/>

                            <select-ui :options="$store.state.OPFTypes" v-model="filters.type"
                                       @input="applyFilter(500)"
                                       el-class="form-control" class="mw-250"
                                       placeholder="Все статусы"/>

                            <input-search-simple el-class="form-control" @keyup="applyFilter(500)"
                                                 v-model="filters.search"/>

                            <a @click.prevent="toggleFilters"
                               href="#" class="show-filters-btn"
                               :class="showAllFilters ? 'opened' : ''">
                                <b-icon-sliders class="icon-left"></b-icon-sliders>
                                <span>Фильтры</span>
                            </a>

                        </div>

                    </form>
                </div>
            </div>

            <section class="table-type-1 bills-list">
                <header></header>

                <div class="table-type-1__table loader-wrapper">

                    <!-- Table header -->
                    <header :class="existSelect ? 'exists-selected' : ''">

                        <div class="cell select">
                            <b-form-checkbox v-model="allSelected" :indeterminate="isPartitionSelected" />
                        </div>

                        <template v-if="!existSelect">
                            <div class="cell bill-status">
                                <span>Статус</span>
                            </div>
                            <div class="cell counterparty">
                                <span>Контрагент и номер счета</span>
                            </div>
                            <div class="cell sum">
                                <span>Сумма</span>
                            </div>
                            <div class="cell pay-before">
                                <span>Прогноз</span>
                                <span class="text-primary ml-1" v-b-tooltip.hover title="Прогноз даты оплаты. Данная информация будет заполняться в календарь">
                                    <b-icon-question-circle/>
                                </span>
                            </div>
                        </template>

                        <div v-else class="cell select-toolbar">
                            <span class="select-count">{{ selected.length }}</span>
                            <a href="#" @click.prevent="editBill"
                               class="edit-btn" v-if="selected.length === 1">Редактировать</a>
                            <a href="#" @click.prevent="removeBills()" class="remove-btn">Удалить</a>
                        </div>

                    </header>
                    <!-- end table header -->

                    <transition name="fade-out">
                        <div class="loader" v-if="loading">
                            <b-spinner variant="primary" label="Загрузка"></b-spinner>
                        </div>
                    </transition>


                    <!-- nothing -->
                    <div v-if="!bills.length" class="table-type-1__nothing">
                        Ничего не найдено
                    </div>


                    <!-- Table rows -->
                    <div v-for="item in bills"
                         class="table-type-1__table-row">

                        <div class="table-type-1__table-row-header">

                            <!-- Checkbox -->
                            <div class="cell select">
                                <b-form-checkbox v-model="selected" :key="item.id" :value="item.id"/>
                            </div>

                            <div class="cell bill-status">
                                <bill-status :bill="item"
                                             v-on:commit="() => getBills()"
                                             :options="{ billRelations }"/>
                            </div>
                            <div class="cell counterparty" @click.prevent="openBill(item)">
                                <div class="counterparty" v-if="item.counterparty">
                                    {{ item.counterparty.name }}
                                </div>
                                <div class="num text-secondary">
                                    {{ item.num ? '№' + item.num : '—' }}
                                    {{ item.created_at ? "от " + formatDate(item.created_at) : '' }}
                                </div>
                            </div>
                            <div class="cell sum" @click.prevent="openBill(item)">
                                <template v-if="item.transactions_sum_amount">
                                    <price-format-ui :amount="item.sum"/>
                                    <div class="text-success small d-flex">
                                        <div class="mr-1">Оплачено</div>
                                        <price-format-ui :amount="item.transactions_sum_amount"
                                                                  class="without-currency"/>
                                    </div>

                                </template>
                                <template v-else>
                                    <price-format-ui :amount="item.sum"/>
                                </template>
                            </div>
                            <div class="cell pay-before" @click.prevent="openBill(item)">
                                {{ item.pay_before ? formatDate(item.pay_before) : '—' }}
                            </div>

                        </div>


                        <!-- Table row inner content -->
                        <div class="table-type-1__table-row-content" v-if="false">
                            <div>
                                /.../
                            </div>

                            <footer>
                                <a href="#" @click.prevent>
                                    <b-icon-chevron-up class="icon-left"/>
                                    <span>Скрыть</span>
                                </a>
                            </footer>
                        </div>
                    </div>
                </div>

                <footer>
                    <div class="pagination">
                        <template v-if="pages.length > 1">
                            <a v-for="pageNum in pages" href="#" class="pageLink"
                               v-on:click.prevent="choosePage(pageNum)"
                               :class="pageNum.toString() === page.toString() ? 'current' : ''">
                                {{ pageNum }}
                            </a>
                            <a v-if="morePages" href="#" class="pageLink show-more">Еще {{ morePages }}</a>
                        </template>
                    </div>
                    <div class="per-page-count">
                        <span>
                            Количество записей
                        </span>

                        <select-ui :options="per_page_counts" v-model="perPageCount" :required="true"
                                   @input="choosePPC"/>
                    </div>
                </footer>
            </section>
        </div>

    </div>
</template>

<script>

import axios from '../../../mixins/axios';
import formats from '../../../mixins/formats';
import paginator from '../../../mixins/paginator';
import BillUtils from './bill-utils';
import PageOffsetMixin from '../../../mixins/page-offset';
import BillStatus from './show/status'
import { EventBus } from '../../../index';

export default {
    name: "bills-component",
    mixins: [axios, formats, paginator, BillUtils, PageOffsetMixin],
    components: {BillStatus},
    data(){
        return {
            billStack: [],

            selected: [],
            allSelected: false,

            filters: {},

            loading: false,
            filterTimer: false,

            showAllFilters: false,

            // Templates
            templates: [],
            showTemplates: true,


            billRelations: [
                'positions.nds_type',
                'signature_list_with_attachments',
                'counterparty',
                'checking_account',
                'account',
                'logo_attachment',
                'stamp_attachment',
                'transactions'
            ],
        }
    },


    computed: {

        bills(){
            return this.billStack;
        },

        existSelect(){
            return !! this.selected.length;
        },

        isPartitionSelected(){
            if(this.selected.length && this.selected.length < this.bills.length)
                return true;
            return false;
        },

        filterExists(){
            let filters = this.filters,
                exists = {};
            for(let key in filters){
                let filter = filters[key];
                if(!filter || filter === '' || filter === undefined)
                    continue;

                exists[key] = filters[key];
            }

            return exists;
        }
    },


    watch: {
        allSelected: function(){
            if(this.allSelected){
                this.selected = [];
                for(let option of this.bills){
                    this.selected.push(option.id);
                }
            }
            else{
                this.selected = [];
            }
        },

        selected: function(){
            if(this.selected.length === this.bills.length)
                this.allSelected = true;
            else if(!this.selected || !this.selected.length)
                this.allSelected = false;
        }
    },


    // Methods
    methods: {

        // Load bill collection
        getBills(){
            this.loading = true;
            this.axiosGET('/ui/bills?' + this.requestParams(true)).then(data => {
                this.lastPage = Number.parseInt(data.last_page || 1);
                this.billStack = data.bills || [];
                this.templates = data.templates || [];
            }).finally(() => {
                this.loading = false;
            });
        },

        setStatus(bill, status){
            let id = bill.id;
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
        },


        // Get request params
        requestParams(to_query_string){
            let params = {
                with: 'counterparty,account,checking_account',
                page: this.page,
                ppc: this.perPageCount
            };

            if(Object.keys(this.filterExists).length)
                params = Object.assign(params, this.filterExists);

            if(to_query_string)
                params = this.queryString(params);

            return params;
        },


        toggleTemplates(){
            this.showTemplates = !this.showTemplates;
            if(this.showTemplates)
                localStorage.setItem('linerfinShowBillsTemplates', '1');
            else
                localStorage.removeItem('linerfinShowBillsTemplates');
        },



        /* -- FILTERS -- */

            // Toggle filters
            toggleFilters(){
                this.showAllFilters = !this.showAllFilters;
            },

            // [developing]
            setFilterTimer(){
                clearTimeout(this.filterTimer);
            },

            resetFilter(){
                this.resetFilterTimer();
                for(let i in this.filters)
                    this.filters[i] = null;

                this.getBills();
            },

            applyFilter(delay){
                this.resetFilterTimer(); // reset timer
                this.loading = true;

                // if exist delay - start timer
                if(delay){
                    this.filterTimer = setTimeout(() => {
                        this.applyFilter();
                    }, delay);
                    return;
                }

                this.getBills();
            },



        /* -- PAGINATION -- */

            choosePage(num){
                this.page = num;
                this.getBills();
            },

            choosePPC(ppc){
                this.perPageCount = ppc;
                this.page = 1;
                this.getBills();
            },



        // Create template
        createTemplate(){
            this.$router.push('/bills/templates/create');
        },

    },

    created(){
        this.getBills();
        this.showTemplates = !!localStorage.getItem('linerfinShowBillsTemplates');
    }
}
</script>
