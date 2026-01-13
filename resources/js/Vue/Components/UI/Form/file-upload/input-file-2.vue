<script>
import InputFileMixin from "./input-file-mixin";
import ChooseAttachment from "../../Modals/choose-attachment";

export default {
    name: "input-file",
    mixins: [InputFileMixin],

    // Properties
    props: {},

    // Getters
    computed: {},



    // Methods
    methods: {

        openChoosingModal(){

            let props = {
                accept: this.accept,
                public: this.public,
                maxSize: this.maxSize,
                accountPublic: this.accountPublic,
                extensions: this.extensions,
                autoload: this.autoload,
                uploadHandler: this.uploadHandler,
                removeBackground: this.removeBackground,
            };

            this.$emit('vuedals:new', {
                name: 'large-modal',
                dismissable: false, // hide header with close-x
                escapable: true,
                component: ChooseAttachment,
                props: props,

                onClose: (data) => {
                    console.log('Popup was closed', { data });

                    if(data.attachment) {
                        this.attachment = data.attachment;
                        this.$emit('input', data.attachment?.id);
                        this.$emit('input-uuid', data.attachment?.uuid);
                    }
                }
            })
        }

    },


    // Watchers
    watch: {}
}
</script>

<template>

    <div class="input-file-wrapper loader-wrapper" ref="inputFileWrapper">

        <div class="loader loader__white-bg centered" v-if="uploading">
            <b-spinner variant="primary" label="Загрузка"></b-spinner>
        </div>

        <input type="file" @change="loadHandler()" ref="file" :accept="accept">
        <div class="dragdrop" :class="dragHover ? 'bg-grey' : ''"
             ref="dragwrapper"
             @dragover="dragHover = true">

            <div class="dragdrop-mask" v-show="dragHover" @dragleave="dragHover = false"
                 @drop="dragHover = false"></div>

            <!-- Choose file -->
            <div v-if="!fileExist" @click.prevent="openChoosingModal" class="dragdrop__choosing">
                <slot>
                    <span class="text-primary">Выберите файл</span> или перетащите его сюда
                </slot>
            </div>

            <!-- Preview -->
            <div v-else-if="previewImage" :style="`background-image: url(${previewImage})`"
                 class="image-preview">

                <a @click.prevent="resetFile" href="#" class="reset">
                    <b-icon-x/>
                </a>

            </div>

            <!-- Show filename -->
            <template v-else>
                    <span class="filename">
                        {{ fileName }}
                    </span>
                <a href="#" @click.prevent="resetFile">
                    <b-icon-x/>
                </a>
            </template>
        </div>
    </div>

</template>
