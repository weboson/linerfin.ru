<template>
    <div class="form-wrapper">
        <div class="form-group with-tip mb-4" >
            <div class="position-relative">
                <input-file v-model="attachment_id" class="bg-white"
                            :extensions="['jpg', 'png', 'jpeg']"
                            accept="image/jpeg,image/png"
                            :attachment-data="inputFileData"
                            @input-uuid="saveUUID"
                            @input="logoChanged"/>

                <div class="form-tip text-primary" v-b-tooltip.hover title="Вы можете загрузить логотип компании, который отобразиться в выставленных счетах">
                    <b-icon-question-circle/>
                </div>
            </div>

            <small class="form-text text-muted">Рекомендуется изображение в формате PNG <br> размером не более 300x700px</small>
        </div>

        <button class="btn btn-primary w-100" @click.prevent="commit" :disabled="!this.uuid">Продолжить</button>
        <button class="text-secondary btn w-100 mt-2 p-0" style="font-size: 14px"
                @click.prevent="$emit('commit', {})">
            Пропустить
        </button>
    </div>
</template>

<script>
import CreateCompanyMixin from "./CreateCompanyMixin";
export default {
    name: "CreateCompany4",
    mixins: [ CreateCompanyMixin ],

    props: {
        options: Object
    },

    data(){
        return {
            attachment_id: null,
            uuid: null,
        }
    },

    computed: {
        inputFileData(){
            if(this.uuid)
                return { uuid: this.uuid };
            return {};
        }
    },

    methods: {
        logoChanged(data){
            console.log('logo changed', data);
        },
        loadHandler(file){
            console.log('load handler', file);
        },
        commit(){
            console.log('commit run');
            if(!this.uuid) return;
            this.$emit('commit', {
                logo_attachment_id: this.attachment_id,
                logo_attachment_uuid: this.uuid
            });
        },

        saveUUID(uuid){
            this.uuid = uuid || null;
        }
    },

    created(){
        this.attachment_id = this.options?.logo_attachment_id || null;
        this.uuid = this.options?.logo_attachment_uuid || null;
    }
}
</script>
