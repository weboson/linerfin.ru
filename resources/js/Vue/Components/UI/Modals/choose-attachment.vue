<template>
    <div class="choose-attachment-modal">
        <header>
            <slot>Выберите файл</slot>
            <a href="#" @click.prevent="$emit('vuedals:close', {})" class="close">
                <b-icon-x></b-icon-x>
            </a>
        </header>
        <input-file :accept="accept" :public="public" :account-public="accountPublic" :extensions="extensions" :autoload="autoload" :uploadHandler="uploadHandler" :scope="scope" :remove-background="removeBackground"
                    :max-size="maxSize"
                    v-on:upload="chooseAttachment">
            <div class="text-primary text-center" style="font-weight: 500;">
                <b-icon-cloud-upload class="mr-2"/>
                Загрузить изображение
            </div>
        </input-file>
        <div class="choose-attachment-modal__list loader-wrapper" :class="loading ? 'loading' : ''">
            <div class="loader centered" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
            <div class="row">
                <div class="col-4 mb-4" v-for="attachment in attachments" :key="attachment.id">
                    <b-card class="h-100" :img-src="'/ui/attachments/' + attachment.uuid" :img-alt="attachment.name"
                            @click="chooseAttachment(attachment)">
                        <b-card-text>
                            <div class="text-primary small">{{ attachment.name || 'Изображение' }}</div>
                            <div class="date small text-muted">{{ formatDate(attachment.created_at, true) }}</div>
                        </b-card-text>
                    </b-card>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass">
    .choose-attachment-modal

        > header
            border-bottom: none
            font-size: 18px
            font-weight: 500


        &__list

            &.loading
                min-height: 200px


            .card
                cursor: pointer
                transition: border-color 250ms
                &:hover
                    border-color: #0662C1



            .card-body
                padding: 10px


            img.card-img
                background-color: #f9f9f9
                object-fit: contain
                height: 180px
                width: 180px



</style>


<script>

import Axios from '../../../mixins/axios';
import Utils from '../../../mixins/utils';

export default {
    name: "choose-attachment",
    mixins: [Axios, Utils],

    props: {
        accept: { default: '*/*'}, // input accept attribute

        maxSize: {
            type: String,
            default: null
        },

        // Access options
        public: {
            type: Boolean,
            default: false
        },
        accountPublic: {
            // public for this account
            type: Boolean,
            default: false
        },


        // Allowed extensions
        extensions: {
            type: Array
        },


        // Autoload options
        autoload: {
            type: Boolean,
            default: true
        },


        // callback
        uploadHandler: Function,
        scope: {
            type: Object,
            default: null
        },

        removeBackground: Boolean
    },

    data(){
        return {
            attachments: [],
            loading: false,
        }
    },

    methods: {

        // get attachments
        getAttachments(){

            const saveAttachments = (data) => { this.attachments = data?.attachments || []; };

            this.loading = true;
            this.axiosGET('/ui/attachments')
                .then(saveAttachments)
                .finally(() => { this.loading = false; })
        },

        // remove attachments


        // choose attachment
        chooseAttachment(attachment){
            this.$emit('vuedals:close', { attachment });
        }


    },

    mounted(){
        this.getAttachments();
    }
}
</script>
