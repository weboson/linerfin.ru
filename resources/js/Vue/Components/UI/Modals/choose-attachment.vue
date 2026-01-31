<template>
    <div class="choose-attachment-modal">
        <header>
            <slot>Выберите файл</slot>
            <a href="#" @click.prevent="$emit('vuedals:close', {})" class="close">
                <b-icon-x></b-icon-x>
            </a>
        </header>
        
        <!-- Настройки удаления фона -->
        <div class="bg-removal-settings mb-3 p-3 border rounded">
            <div class="form-check mb-2">
                <input
                    type="checkbox"
                    id="removeBackgroundCheck"
                    v-model="localRemoveBackground"
                    class="form-check-input"
                />
                <label for="removeBackgroundCheck" class="form-check-label">
                    <b-icon-magic class="mr-1"/>
                    Удалить белый фон у изображений
                </label>
            </div>
            
            <div v-if="localRemoveBackground" class="bg-removal-options pl-4">
                <small class="text-muted d-block">
                    Фон автоматически удаляется у PNG, JPG, JPEG, BMP изображений
                </small>
            </div>
        </div>
        
        <!-- Компонент загрузки файла -->
        <input-file 
            :accept="accept" 
            :public="public" 
            :account-public="accountPublic" 
            :extensions="extensions" 
            :autoload="autoload" 
            :uploadHandler="uploadHandler" 
            :scope="scope" 
            :remove-background="localRemoveBackground"
            :max-size="maxSize"
            v-on:upload="chooseAttachment">
            
            <div class="text-primary text-center" style="font-weight: 500;">
                <b-icon-cloud-upload class="mr-2"/>
                Загрузить изображение
            </div>
        </input-file>
        
        <!-- Список загруженных файлов -->
        <div class="choose-attachment-modal__list loader-wrapper" :class="loading ? 'loading' : ''">
            <div class="loader centered" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
            
            <div v-if="!loading && attachments.length === 0" class="text-center text-muted py-5">
                <b-icon-image class="display-4 d-block mb-3" style="opacity: 0.3;"></b-icon-image>
                Нет загруженных изображений
            </div>
            
            <div class="row" v-else>
                <div class="col-4 mb-4" v-for="attachment in attachments" :key="attachment.id">
                    <b-card class="h-100 attachment-card" 
                            :img-src="getAttachmentUrl(attachment)" 
                            :img-alt="attachment.name"
                            @click="chooseAttachment(attachment)">
                        
                        <template #img>
                            <div class="card-img-wrapper">
                                <img :src="getAttachmentUrl(attachment)" 
                                     :alt="attachment.name"
                                     class="card-img-top"
                                     @error="handleImageError">
                                
                                <span v-if="attachment.extension === 'png' && isImage(attachment) && attachment.meta?.background_removed" 
                                      class="badge badge-transparent">
                                    <b-icon-check-circle class="mr-1"/>
                                    Прозрачный фон
                                </span>
                            </div>
                        </template>
                        
                        <b-card-text>
                            <div class="text-primary small text-truncate" :title="attachment.name">
                                {{ attachment.name || 'Изображение' }}
                            </div>
                            <div class="date small text-muted">
                                {{ formatDate(attachment.created_at, true) }}
                                <span v-if="attachment.extension" class="text-uppercase ml-1">
                                    ({{ attachment.extension }})
                                </span>
                            </div>
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
        padding: 1rem 1.5rem
        display: flex
        justify-content: space-between
        align-items: center
        
        .close
            color: #6c757d
            text-decoration: none
            font-size: 1.5rem
            
            &:hover
                color: #0662C1
    
    .bg-removal-settings
        background-color: #f8f9fa
        margin: 0 1.5rem
        
        .form-check-label
            font-weight: 500
            cursor: pointer
    
    &__list
        margin-top: 1rem
        padding: 0 1.5rem 1.5rem
        
        &.loading
            min-height: 200px
            position: relative
        
        .loader.centered
            position: absolute
            top: 50%
            left: 50%
            transform: translate(-50%, -50%)
    
        .attachment-card
            cursor: pointer
            transition: all 0.25s ease
            border: 2px solid transparent
            overflow: hidden
            
            &:hover
                border-color: #0662C1
                box-shadow: 0 4px 12px rgba(6, 98, 193, 0.15)
                transform: translateY(-2px)
            
            .card-img-wrapper
                position: relative
                height: 180px
                overflow: hidden
                background-color: #f9f9f9
                display: flex
                align-items: center
                justify-content: center
                
                img.card-img-top
                    object-fit: contain
                    height: 100%
                    width: 100%
                    max-height: 180px
                    padding: 10px
                
                .badge-transparent
                    position: absolute
                    bottom: 10px
                    right: 10px
                    background: rgba(0, 0, 0, 0.7)
                    color: white
                    font-weight: normal
                    font-size: 0.7rem
                    padding: 2px 6px
            
            .card-body
                padding: 10px
            
            .text-truncate
                max-width: 100%
                overflow: hidden
                text-overflow: ellipsis
                white-space: nowrap
</style>

<script>
import Axios from '../../../mixins/axios';
import Utils from '../../../mixins/utils';

export default {
    name: "choose-attachment",
    mixins: [Axios, Utils],

    props: {
        accept: { 
            default: 'image/*'
        },

        maxSize: {
            type: String,
            default: null
        },

        public: {
            type: Boolean,
            default: false
        },
        accountPublic: {
            type: Boolean,
            default: false
        },

        extensions: {
            type: Array,
            default: () => ['png', 'jpg', 'jpeg', 'bmp', 'gif']
        },

        autoload: {
            type: Boolean,
            default: true
        },

        uploadHandler: Function,
        scope: {
            type: Object,
            default: null
        },

        removeBackground: {
            type: Boolean,
            default: true
        }
    },

    data(){
        return {
            attachments: [],
            loading: false,
            // Локальная переменная для v-model
            localRemoveBackground: this.removeBackground
        }
    },

    methods: {
        getAttachmentUrl(attachment) {
            if (attachment && attachment.uuid) {
                return `/ui/attachments/${attachment.uuid}`;
            }
            return '';
        },
        
        isImage(attachment) {
            const imageExtensions = ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp'];
            return imageExtensions.includes(attachment.extension?.toLowerCase());
        },
        
        handleImageError(event) {
            event.target.style.display = 'none';
            const parent = event.target.parentElement;
            const fallback = document.createElement('div');
            fallback.className = 'image-fallback';
            fallback.innerHTML = `
                <div class="text-center text-muted p-3">
                    <b-icon-file-image class="h3"></b-icon-file-image>
                    <div class="small mt-2">Изображение</div>
                </div>
            `;
            parent.appendChild(fallback);
        },

        getAttachments(){
            const saveAttachments = (data) => { 
                this.attachments = data?.attachments || []; 
            };

            this.loading = true;
            this.axiosGET('/ui/attachments')
                .then(saveAttachments)
                .catch(error => {
                    console.error('Error loading attachments:', error);
                    this.errorNotify('Ошибка при загрузке списка файлов');
                })
                .finally(() => { 
                    this.loading = false; 
                });
        },

        chooseAttachment(attachment){
            const result = {
                attachment,
                removeBackground: this.localRemoveBackground
            };
            
            this.$emit('vuedals:close', result);
        }
    },

    mounted(){
        this.getAttachments();
    },
    
    watch: {
        // Следим за изменением пропса извне
        removeBackground(newVal) {
            this.localRemoveBackground = newVal;
        }
    }
}
</script>