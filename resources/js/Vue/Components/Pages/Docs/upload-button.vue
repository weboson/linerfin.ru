<template>
    <label class="docs__upload-btn">
        <input type="file" @change="loadHandler" ref="file" :accept="accept" name="file">
        <span class="btn btn-primary" :class="uploading ? 'disabled' : ''">
            <template v-if="!uploading">
                <b-icon-upload></b-icon-upload>
                <span class="ml-1">Загрузить документ</span>
            </template>
            <template v-else>
                <b-spinner variant="light" label="Spinning"></b-spinner>
                <span class="ml-1">Загружается</span>
            </template>
        </span>
    </label>
</template>

<style lang="sass">
.docs__upload-btn
    margin: 0
    input[type="file"]
        display: none
</style>

<script>

import AxiosMix from '~@vueMixins/axios';
import UtilsMix from '~@vueMixins/utils';

export default {
    name: "upload-button",

    mixins: [ AxiosMix, UtilsMix ],

    data(){
        return {
            accept: '.doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf,.png,.jpg,.jpeg',
            uploading: false,
        }
    },

    methods: {
        loadHandler(){
            const [file] = this.$refs.file.files;

            console.log({file, type: file.type})

            // check file type
            if(!this.acceptFileType(file.type)){
                this.errorNotify('Неверный тип файла');
                return;
            }

            // check file size
            if(this.maxSize && file.size > Number.parseInt(10*1024*1024)){
                this.errorNotify('Превышен максимальный размер файла');
                return;
            }

            if(this.uploading)
                return console.log('already uploading');


            let formData = new FormData();
            formData.set('attachment', file);

            const options = { headers: { 'Content-Type': 'multipart/form-data' } };
            const url = '/ui/attachments';

            this.uploading = true;

            this.axiosPOST(url, formData, options).then(data => {
                if(data.attachment_id)
                    this.$emit('uploaded')
                else
                    this.errorNotify('Не удалось загрузить документ');
            }).finally(() => { this.uploading = false; });
        },


        // disabled
        acceptFileType(type){
            return true;

            if(!this.accept) return true;
            let accept = this.accept.split(',');
            return ~accept.indexOf(type);
        },
    },


}
</script>
