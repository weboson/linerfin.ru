<template>
    <div class="top-header-wrapper">
        <div class="top-header" :class="topHeaderOffset">
            <header class="title">
<!--                <img src="/assets/images/icons/reports.svg">-->
                <span>Документы</span>
            </header>
        </div>



        <div class="container-fluid">


            <!-- Main toolbar -->
            <div class="toolbar">
                <div class="left">

                    <upload-button @uploaded="getDocs"></upload-button>

                    <!--<a @click.prevent="runSyncService"
                       href="#" class="btn btn-outline-warning">
                        Синхронизовать
                    </a>-->

                </div>
            </div>


            <section class="table-type-1 docs-table">
                <header></header>

                <div class="table-type-1__table loader-wrapper">

                    <!-- table header -->
                    <header :class="existSelect ? 'exists-selected' : ''">
                        <div class="cell select">
                            <b-form-checkbox v-model="allSelected" :indeterminate="isPartitionSelected" />
                        </div>

                        <template v-if="!existSelect">
                            <div class="cell preview"></div>
                            <div class="cell name">
                                Название
                            </div>
                            <div class="cell created">
                                Создан
                            </div>
                            <div class="cell actions"></div>

                        </template>

                        <div v-else class="cell select-toolbar">
                            <span class="select-count">{{ selected.length }}</span>
                            <a href="#" v-if="selectedCanRemoved" @click.prevent="removeDocs" class="remove-btn">Удалить</a>
                        </div>
                    </header>
                    <!-- end table header -->


                    <!-- loader -->
                    <transition name="fade-out">
                        <div class="loader" v-if="loading">
                            <b-spinner variant="primary" label="Загрузка"></b-spinner>
                        </div>
                    </transition>


                    <!-- nothing -->
                    <div v-if="!docs.length" class="table-type-1__nothing">
                        Ничего не найдено
                    </div>


                    <!-- Table rows -->
                    <div class="table-type-1__table-row" v-for="doc in docs">

                        <div class="table-type-1__table-row-header">

                            <!-- Checkbox -->
                            <div class="cell select">
                                <b-form-checkbox v-model="selected" :key="doc.id" :value="doc.id"/>
                            </div>
                            <div class="cell preview">
                                <img :src="getDocPreview(doc)">
                            </div>
                            <div class="cell name">
                                <div class="doc-name">{{ doc.name }}</div>
                                <div class="doc-bill-link small" v-if="doc.bill">
                                    <router-link :to="`/bills/${doc.bill.id}`">Перейти к счёту</router-link>
                                </div>
                            </div>

                            <div class="cell created">
                                <div class="created_by" v-if="doc.user">
                                    {{ getFullName(doc.user, { initials: true }) }}
                                </div>
                                <div class="created_at text-secondary small">
                                    {{ formatDate(doc.created_at, true) }}
                                </div>
                            </div>

                            <div class="cell actions">
                                <a class="btn btn-link" v-if="doc.type === 'attachment'"
                                   :download="getDownloadName(doc)"
                                   :href="getDownloadLink(doc)">
                                    <b-icon-download/>
                                </a>

                                <a class="btn btn-link" v-else
                                   target="_blank"
                                   :href="getDownloadLink(doc)">
                                    <b-icon-download/>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</template>


<style lang="sass">
.docs-table
    > header
        padding: 5px

    .cell
        &.preview
            width: 80px
            img
                width: 100%
                height: 40px
                object-fit: contain

        &.name
            width: 60%
        &.created
            width: 20%


</style>


<script>
import AxiosMix from "~@vueMixins/axios";
import FormatsMix from '~@vueMixins/formats';
import UtilsMix from '~@vueMixins/utils';
import ModalsMix from '~@vueMixins/popup-mix';
import UploadButton from './upload-button';

import PageOffsetMixin from '~@vueMixins/page-offset';

export default {
    mixins: [AxiosMix, FormatsMix, PageOffsetMixin, ModalsMix, UtilsMix],
    components: { UploadButton },
    data(){
        return {
            docs: [],

            selected: [],
            allSelected: false,
            loading: false,

        }
    },

    computed: {

        existSelect(){
            return !! this.selected.length;
        },

        isPartitionSelected(){
            if(this.selected.length && this.selected.length < this.docs.length)
                return true;
            return false;
        },

        selectedCanRemoved(){

            if(!this.selected.length)
                return true

            for(let i in this.docs){
                let doc = this.docs[i]

                if(~this.selected.indexOf(doc.id))
                    if(doc.type !== 'attachment')
                        return false
            }

            return true
        }
    },

    watch: {
        allSelected: function(){
            if(this.allSelected){
                this.selected = [];
                for(let option of this.docs){
                    this.selected.push(option.id);
                }
            }
            else{
                this.selected = [];
            }
        },

        selected: function(){
            if(this.docs.length && this.selected.length === this.docs.length)
                this.allSelected = true;
            else if(!this.selected || !this.selected.length)
                this.allSelected = false;
        }
    },


    methods: {

        getDocs(){
            this.loading = true;
            this.axiosGET('/ui/docs')
                .then(r => {
                    this.docs = r.docs || [];
                })
                .finally(() => this.loading = false)
        },


        removeDocs(){
            if(!this.selectedCanRemoved)
                return this.notify('Выбранные документы не могут быть удалены');

            if(!this.selected.length)
                return

            let ids = [];
            this.selected.map(id => {
                let doc = this.docs.find(d => d.id === id)
                if(!doc || !doc?.attachment?.id) return;
                ids.push(doc.attachment.id)
            })

            this.confirmPopup("Подтвердите удаление выбранных документов")
                .then(() => {
                    this.loading = true;

                    this.axiosPOST('/ui/attachments/remove', { ids: ids.join(',') })
                        .then(r => {
                            this.selected = [];
                            this.successNotify("Выбранные документы успешно удалены");
                            this.getDocs();
                        }).finally(() => this.loading = false)
                })
                .catch(() => { /* nothing */})
        },


        getDocPreview(doc){
            if(doc.type === 'attachment' && doc.attachment){
                if(~['png', 'jpg', 'jpeg'].indexOf(doc.attachment.extension)){
                    return `/ui/attachments/${doc.attachment.uuid}`
                }
            }
            return '/assets/images/icons/doc.svg'
        },

        getDownloadName(doc){
            if(doc.type === 'attachment' && doc.attachment){
                return doc.attachment.name
            }
            return 'unnamed'
        },

        getDownloadLink(doc){
            if(doc.type === 'attachment' && doc.attachment)
                return `/ui/attachments/${doc.attachment.uuid}`

            if(doc.type === 'bill' && doc.bill)
                if(doc.bill?.link)
                    return '/bill-' + doc.bill.link;


            return '#'
        },


        runSyncService(){
            console.log('run sync service');
            this.axiosGET('/ui/docs/services/sync').then((r) => {
                const msg = r.msg || 'complete';
                alert(msg);
            })
        }
    },

    created(){
        this.getDocs()
    }
}
</script>
