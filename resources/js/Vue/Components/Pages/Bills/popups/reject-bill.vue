<script>
import ModalButtons from '../../../UI/Modals/modal-buttons';
import axios from "../../../../mixins/axios";

export default {
    name: "reject-bill",
    components: { ModalButtons },
    mixins: [axios],

    // props: ['id', 'phone', 'email', 'options'],
    props: {
        id: Number,
        phone: null,
        email: null,
        options: Object
    },

    data(){
        return {
            sendNotify: true,
            phoneNotify: '',
            emailNotify: '',
            QRCode: false,
            comment: '',

            loading: false,
        }
    },


    methods: {

        save(){

            // prepare data
            let postData = {
                status: 'rejected',
                send_notify: this.sendNotify,
                phone: this.phoneNotify,
                email: this.emailNotify,
                comment: this.comment
            };

            if(this.options?.billRelations) {
                postData.with = this.options.billRelations.join(',');
            }

            let httpRequest = this.axiosPOST(`/ui/bills/${this.id}/set-status`, postData);

            this.loading = true;

            httpRequest.then(data => {
                this.$emit('vuedals:close', data);
                this.loading = false;
            });
        },

        close(){
            this.$emit('vuedals:close', {});
        }
    },

    created(){
        if(this.phone)
            this.phoneNotify = this.phone;
        if(this.email)
            this.emailNotify = this.email;
    }
}
</script>



<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">Отозвать счёт</div>
        <div class="vuedal-content loader-wrapper">

            <transition name="fade-out">
                <div class="loader centered" v-if="loading">
                    <b-spinner variant="primary" label="Загрузка"></b-spinner>
                </div>
            </transition>

            <div class="form-group">
                <b-checkbox v-model="sendNotify">
                    Предупредите контрагента, что счёт не нужно оплачивать
                </b-checkbox>
            </div>

            <transition name="fade">
                <div v-if="sendNotify">
<!--                    <div class="form-group">
                        <label>
                            SMS на телефон
                        </label>
                        <input-mask v-model="phoneNotify"
                                    class="form-control"
                                    regex="^[\d+]{10,12}$"/>
                    </div>-->

                    <div class="form-group">
                        <label>
                            Электронная почта
                        </label>
                        <input type="email" v-model="emailNotify" class="form-control">
                    </div>

                    <!--            <div class="form-group">
                                    <b-checkbox v-model="QRCode">Добавить QR-код</b-checkbox>
                                </div>-->
                </div>
            </transition>

            <div class="form-group">
                <label>Комментарий</label>
                <textarea class="form-control" v-model="comment"></textarea>
                <small class="form-text text-muted">
                    Например: <a href="#" @click.prevent="comment = 'Не оплачивайте этот счёт'">Не оплачивайте этот счёт</a>
                </small>
            </div>
        </div>

        <modal-buttons save-text="Отозвать счёт" @save="save" @cancel="close" :disabled="loading"/>
    </div>
</template>
