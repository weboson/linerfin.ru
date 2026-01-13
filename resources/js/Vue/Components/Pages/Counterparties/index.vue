<template>
    <div class="top-header-wrapper">
        <div class="top-header" :class="topHeaderOffset">
            <header class="title">
                <img src="/assets/images/icons/contractors.svg">
                <span>Контрагенты</span>
            </header>
        </div>



        <div class="container-fluid">

            <div class="toolbar">
                <div class="left">
                    <a @click.prevent="createPopup"
                       href="#" class="btn btn-primary">
                        <b-icon-person-plus></b-icon-person-plus>
                        Добавить контрагента
                    </a>
                </div>
                <div class="right">
                    <a @click.prevent
                       href="#" class="btn btn-outline-secondary disabled">
                        <b-icon-download class="icon-left"/>
                        Импорт
                    </a>
                    <a @click.prevent
                       href="#" class="btn btn-outline-secondary disabled">
                        <b-icon-upload class="icon-left"/>
                        Экспорт
                    </a>
                </div>
            </div>

            <div class="toolbar filter">
                <div class="content">
                    <form @submit.prevent>
                        <div class="filter-form-row">
                            <select-ui :options="$store.state.counterpartyCategories" v-model="filters.category"
                                       @input="applyFilter(500)"
                                       el-class="form-control" class="mw-250"
                                       placeholder="Категория контрагента"/>

                            <select-ui :options="$store.state.OPFTypes" v-model="filters.type"
                                       @input="applyFilter(500)"
                                       el-class="form-control" class="mw-250"
                                       placeholder="Тип контрагента"/>

                            <input-search-simple el-class="form-control" @keyup="applyFilter(500)"
                                                 v-model="filters.search"/>

                            <a @click="resetFilter" v-if="Object.keys(filterExists).length"
                               href="#" class="text-secondary">Сбросить фильтры</a>

                        </div>
                    </form>
                </div>
            </div>

            <section class="table-type-1 counterparties-list">
                <header></header>

                <div class="table-type-1__table loader-wrapper">

                    <!-- Table header -->
                    <header :class="existSelect ? 'exists-selected' : ''">

                        <div class="cell select">
                            <b-form-checkbox v-model="allSelected" :indeterminate="isPartitionSelected" />
                        </div>

                        <template v-if="!existSelect">
                            <div class="cell counterparty">
                                <span>Контрагент</span>
                            </div>
                            <div class="cell type">
                                <span>Тип</span>
                            </div>
                            <div class="cell inn">
                                <span>ИНН</span>
                            </div>
                            <div class="cell contact">
                                <span>Контактное лицо</span>
                            </div>
                        </template>

                        <div v-else class="cell select-toolbar">
                            <span class="select-count">{{ selected.length }}</span>
                            <a href="#" @click.prevent="editPopup(selected[0])"
                               class="edit-btn" v-if="selected.length === 1">Редактировать</a>
                            <a href="#" @click.prevent="removeCounterparties" class="remove-btn">Удалить</a>
                        </div>

                    </header>
                    <!-- end table header -->

                    <transition name="fade-out">
                        <div class="loader" v-if="loading">
                            <b-spinner variant="primary" label="Загрузка"></b-spinner>
                        </div>
                    </transition>

                    <div v-if="!counterparties.length" class="table-type-1__nothing">
                        Ничего не найдено
                    </div>

                    <!-- Table rows -->
                    <div v-for="item in counterparties"
                         class="table-type-1__table-row">

                        <div class="table-type-1__table-row-header">

                            <!-- Checkbox -->
                            <div class="cell select">
                                <b-form-checkbox v-model="selected" :key="item.id" :value="item.id"/>
                            </div>

                            <div class="cell counterparty" @click.prevent="openCounterparty(item)">
                                <div class="counterparty__name">
                                    {{ item.name }}
                                </div>
                                <div v-if="item.category" class="counterparty__category">
                                    {{ item.category.name }}
                                </div>
                            </div>
                            <div class="cell type" @click.prevent="openCounterparty(item)">
                                <span>{{ item.opf ? item.opf.short_name || item.opf.name : '' }}</span>
                            </div>
                            <div class="cell inn" @click.prevent="openCounterparty(item)">
                                <span>{{ item.inn }}</span>
                            </div>
                            <div class="cell contact" @click.prevent="openCounterparty(item)">

                                <template v-if="item.contacts && item.contacts.length">
                                    <div class="contact__name">
                                        {{ getFullName(item.contacts[0]) }}
                                    </div>
                                    <div class="contact__data">
                                        <a v-if="item.contacts[0].phone"
                                           href="#" class="phone">
                                            {{ item.contacts[0].phone }}
                                        </a>
                                        <a v-if="item.contacts[0].email"
                                           href="#" class="email">
                                            {{ item.contacts[0].email }}
                                        </a>
                                    </div>
                                </template>

                            </div>

                        </div>


                        <!-- Table row inner content -->
                        <div class="table-type-1__table-row-content" v-if="false">
                            <div>
                                Внутренний контент
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

import axios from '~@vueMixins/axios';
import formats from '~@vueMixins/formats';
import paginator from '~@vueMixins/paginator';
import CreatePopup from "./create-popup";
import EditPopup from "./edit-popup";
import PageOffsetMixin from '~@vueMixins/page-offset';

export default {
    name: "Counterparties",
    mixins: [ axios, formats, paginator, PageOffsetMixin ],
    data(){
        return {
            counterpartyStack: [],

            selected: [],
            allSelected: false,

            filters: {
                search: '',
                category: null,
                type: null
            },

            loading: false,
            filterTimer: false
        }
    },

    computed: {

        existSelect(){
            return !! this.selected.length;
        },

        counterparties(){
            return this.counterpartyStack;
        },

        isPartitionSelected(){
            if(this.selected.length && this.selected.length < this.counterparties.length)
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
                for(let option of this.counterparties){
                    this.selected.push(option.id);
                }
            }
            else{
                this.selected = [];
            }
        },

        selected: function(){
            if(this.counterparties.length && this.selected.length === this.counterparties.length)
                this.allSelected = true;
            else if(!this.selected || !this.selected.length)
                this.allSelected = false;
        }
    },

    methods: {
        getCounterparties(){
            this.loading = true;
            let url = '/ui/counterparties?' + this.requestParams(true);

            this.axiosGET(url)
                .then(data => {
                    this.counterpartyStack = data.counterparties || [];
                    this.lastPage = Number.parseInt(data.last_page || 1);
                    this.loading = false;
                    this.selected = []; // reset selected
                    this.allSelected = false;
                })
        },

        createPopup(){
            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                component: CreatePopup,

                onClose: (data) => {
                    this.getCounterparties();
                }
            })
        },

        editPopup(id){

            let data = {};

            for(let i in this.counterparties){
                if(this.counterparties[i].id === id)
                     data = this.counterparties[i];
            }

            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                props: { data },

                component: EditPopup,
                onClose: (data) => {
                    this.getCounterparties();
                }
            })
        },


        openCounterparty(counterparty){
            this.$router.push('/counterparties/' + counterparty.id);
        },

        requestParams(to_query_string){
            let params = {
                with: 'category,contacts,opf,accounts',
                page: this.page,
                ppc: this.perPageCount
            };

            if(Object.keys(this.filterExists).length)
                params = Object.assign(params, this.filterExists);

            if(to_query_string)
                params = this.queryString(params);

            return params;
        },

        // Выбор страницы
        choosePage(num){
            this.page = num;
            this.getCounterparties();
        },


        // per_page_count
        // количество на одной странице
        choosePPC(ppc){
            this.perPageCount = ppc;
            this.page = 1;
            this.getCounterparties();
        },


        // Remove counterparty
        removeCounterparties(){
            if(!confirm('Удалить выбранные контрагенты?'))
                return;

            this.axiosPOST('/ui/counterparties/delete', {
                ids: this.selected.join(',')
            }).then(r => {
                this.loading = false;
                this.selected = [];
                this.getCounterparties();
            })
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

            this.getCounterparties();
        },

        resetFilterTimer(){
            clearTimeout(this.filterTimer);
        },

        resetFilter(){
            this.resetFilterTimer();
            for(let i in this.filters)
                this.filters[i] = null;

            this.getCounterparties();
        }
    },

    created(){
        this.getCounterparties();
    }
}
</script>
