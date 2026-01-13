<template>
    <div class="form-wrapper">


        <div class="form-group with-tip">
            <header class="text-secondary mb-1">
                Печать компании
            </header>
            <div class="position-relative">
                <input-file v-model="stampId" class="bg-white"
                            accept="image/jpeg,image/png"
                            :attachment-data="stampData"
                            :extensions="['jpg', 'png', 'jpeg']"
                            @input-uuid="saveStamp" key="0"/>

                <div class="form-tip text-primary" v-b-tooltip.hover title="Вы можете загрузить логотип компании, который отобразиться в выставленных счетах">
                    <b-icon-question-circle/>
                </div>
            </div>
        </div>


        <div class="form-group with-tip">
            <header class="text-secondary mb-1">
                Подпись директора
            </header>
            <div class="position-relative">
                <input-file v-model="directorSignatureId" class="bg-white"
                            accept="image/jpeg,image/png"
                            :attachment-data="directorSignatureData"
                            :extensions="['jpg', 'png', 'jpeg']"
                            @input-uuid="saveDirectorSignature" key="1"/>
                <div class="form-tip text-primary" v-b-tooltip.hover title="Подпись директора будет автоматически подставляться">
                    <b-icon-question-circle/>
                </div>
            </div>
        </div>


        <div class="form-group with-tip" v-if="accountantExists">
            <header class="text-secondary mt-2 mb-1">
                Подпись бухгалтера
            </header>
            <div class="position-relative">
                <input-file v-model="accountantSignatureId" class="bg-white"
                            accept="image/jpeg,image/png"
                            :attachment-data="accountantSignatureData"
                            :extensions="['jpg', 'png', 'jpeg']"
                            @input-uuid="saveAccountantSignature" key="2"/>
                <div class="form-tip text-primary" v-b-tooltip.hover title="Вы можете загрузить логотип компании, который отобразиться в выставленных счетах">
                    <b-icon-question-circle/>
                </div>
            </div>
        </div>


        <small class="form-text text-muted">Рекомендуется изображение в формате PNG <br> размером не более 300x700px</small>



        <footer class="mt-4">
            <button class="btn btn-primary w-100" @click.prevent="commit">
                Продолжить
            </button>

            <button class="text-secondary btn w-100 mt-2 p-0" style="font-size: 14px"
                    @click.prevent="$emit('commit', {})">
                Пропустить
            </button>
        </footer>
    </div>
</template>

<script>
import CreateCompanyMixin from "./CreateCompanyMixin";
export default {

    mixins: [ CreateCompanyMixin ],

    props: {
        options: Object,
        accountantExists: {
            type: Boolean,
            default: false
        }
    },

    data(){
        return {

            // Company stamp
            stampId: null,
            stampUuid: null,

            // Director attachments
            directorSignatureId: null,
            directorSignatureUuid: null,

            // Accountant attachments
            accountantSignatureId: null,
            accountantSignatureUuid: null,
        }
    },

    computed: {
        directorSignatureData(){
            return this.directorSignatureUuid ? { uuid: this.directorSignatureUuid } : {}
        },
        accountantSignatureData(){
            return this.accountantSignatureUuid ? { uuid: this.accountantSignatureUuid } : {}
        },
        stampData(){
            return this.stampUuid ? { uuid: this.stampUuid } : {}
        },
    },

    methods: {

        commit(){
            this.$emit('commit', {
                director_signature_id: this.directorSignatureId,
                director_signature_uuid: this.directorSignatureUuid,
                accountant_signature_id: this.accountantSignatureId,
                accountant_signature_uuid: this.accountantSignatureUuid,
                stamp_id: this.stampId,
                stamp_uuid: this.stampUuid,
            });
        },


        saveDirectorSignature(uuid){
            this.directorSignatureUuid = uuid || null
        },
        saveAccountantSignature(uuid){
            this.accountantSignatureUuid = uuid || null
        },
        saveStamp(uuid){
            this.stampUuid = uuid || null
        },
    },

    created(){
        // get init data
        this.directorSignatureId        = this.options?.directorSignatureId || null;
        this.directorSignatureUuid      = this.options?.directorSignatureUuid || null;
        this.accountantSignatureId      = this.options?.accountantSignatureId || null;
        this.accountantSignatureUuid    = this.options?.accountantSignatureUuid || null;
        this.stampId                    = this.options?.stampId || null;
        this.stampUuid                  = this.options?.stampUuid || null;
    }
}
</script>
