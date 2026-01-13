<template>
    <div id="form">
        <form method="POST" action="/register" @submit="submit">

            <input type="hidden" name="_token" :value="CSRF">

            <header id="form-title">
                Регистрация
            </header>


            <!-- Full name -->
            <div class="form-group">
                <input-ui v-model="name" name="name" placeholder="Фамилия, имя и отчество" required/>

                <small v-if="errors.name" class="form-text text-danger">
                    {{ error('name') }}
                </small>
            </div>

            <!-- E-Mail -->
            <div class="form-group">
                <input-ui v-model="email" type="email" name="email" placeholder="Электронная почта" required/>
                <small v-if="errors.email" class="form-text text-danger">
                    {{ error('email') }}
                </small>
            </div>


            <!-- Phone -->
<!--            <input type="hidden" v-model="phone" name="phone">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" v-model="phone" class="form-control" placeholder="Телефон" required
                           :disabled="!!mobileConfirmation || codeSendingProcess">

                    <div class="input-group-append">
                        <a @click.prevent="sendMobileCode" class="btn btn-secondary"
                           :class="repeatSendingLabel || codeSendingProcess ? 'disabled' : ''">
                            Выслать код {{ repeatSendingLabel }}
                            <b-icon v-if="codeSendingProcess" variant="secondary" class="ml-3" icon="circle-fill" animation="throb"></b-icon>
                        </a>
                    </div>
                </div>
                <small class="form-text text-muted">
                    На ваш номер телефона будет отправлен код для подтверждения
                </small>
                <small v-if="errors.phone" class="form-text text-danger">
                    {{ error('phone') }}
                </small>
            </div>-->
            <div class="form-group">
                <input-ui name="phone" v-model="phone" placeholder="Телефон"/>
                <small v-if="errors.phone" class="form-text text-danger">
                    {{ error('phone') }}
                </small>
            </div>


            <!-- SMS Confirmation -->
<!--            <div class="form-group" v-if="mobileConfirmation">
                <input type="text" v-model="mobileCode" name="phone_code" class="form-control" placeholder="Код из SMS">
                <a @click.prevent="changeMobile" href="#">Изменить номер</a>
            </div>-->


            <!-- Password -->
            <div class="form-group">
                <input-ui type="password" v-model="password" name="password" placeholder="Пароль" required/>
                <small v-if="errors.password" class="form-text text-danger">
                    {{ error('password') }}
                </small>
            </div>

            <!-- Password confirm -->
            <div class="form-group">
                <input-ui type="password" v-model="password_confirmation" name="password_confirmation" placeholder="Повторите пароль" required/>
                <small v-if="errors.password_confirmation" class="form-text text-danger">
                    {{ error('password_confirmation') }}
                </small>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" v-model="agreements" name="agreements" class="form-check-input" required id="agreements">
                <label for="agreements">
                    Согласен на обработку
                    <a target="_blank" href="http://linerfin.ru/privacy#confidential">персональных данных</a>
                    и
                    <a target="_blank" href="http://linerfin.ru/privacy">договор оферту</a>
                </label>

                <small v-if="errors.agreements" class="form-text text-danger">
                    {{ error('agreements') }}
                </small>
            </div>

            <div class="btn-group w-100">
                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                <a v-if='suggestRecovery' class="btn btn-secondary" :href="`/forgot-password?account=${suggestRecovery}`">Восстановить</a>
            </div>

            <footer>
                Я уже зарегистрирован. <a href="/login">Вход</a>
            </footer>
        </form>
    </div>
</template>

<script>

import axios from "../../Vue/mixins/axios";
import utils from "../../Vue/mixins/utils";


export default {
    name: "RegisterUser",
    mixins: [axios, utils],
    data(){
        return {
            name: '',
            phone: '',
            email: '',
            password: '',
            password_confirmation: '',
            agreements: true,

            errors: {},
            suggestRecovery: '',

            mobileCode: '',

            mobileConfirmation: false, // show input with code
            sentAt: null,
            repeatSendingLabel: '',
            codeSendingProcess: false,

            repeatSendingTimer: null
        }
    },


    // Component Methods
    methods: {

        // get old value
        old(property, def){
            def = def || '';
            if(window.oldFormData && window.oldFormData[property])
                return window.oldFormData[property];

            return null;
        },

        // get error
        error(property){
            if(this.errors[property]){
                if(typeof this.errors[property] === 'object')
                    return this.errors[property].join(`<br>`);
                return this.errors[property];
            }

            return null;
        },


        // set error
        setError(property, error){
            let errors = Object.assign({}, this.errors);
            errors[property] = error;
            this.errors = errors;
        },


        // get olds properties
        getOlds(){
            const properties = ['name', 'phone', 'email'];

            properties.forEach(value => {
                this[value] = this.old(value) || '';
            })
        },


        // on submit
        submit(e){

            this.errors = {};
            if(!this.agreements){
                this.setError('agreements', 'Подтвердите для продолжения');
                e.preventDefault();
                return;
            }

            if(this.password !== this.password_confirmation){
                this.setError('password_confirmation', 'Пароли не совпадают');
                e.preventDefault();
                return;
            }

            if(!this.password || this.password.length < 8){
                this.setError('password', 'Пароль должен быть не менее 8 символов');
                e.preventDefault();
                return;
            }

        },

        sendMobileCode(){
            if(this.repeatSendingTimer || this.codeSendingProcess)
                return;

            this.codeSendingProcess = true;

            this.axiosPOST('/verify-phone', {_token: this.CSRF, phone: this.phone}).then(r => {
                this.codeSendingProcess = false;
                this.mobileConfirmation = true;
                this.sentAt = new Date().getTime();
                const allowRepeatAt = this.sentAt + 30000;

                this.repeatSendingLabel = '(30)';

                this.repeatSendingTimer = setInterval(() => {
                    let now = new Date().getTime();

                    if(now > allowRepeatAt) {
                        this.repeatSendingLabel = '';
                        clearInterval(this.repeatSendingTimer);
                        this.repeatSendingTimer = null;
                        return;
                    }

                    this.repeatSendingLabel = '(' + Math.round((allowRepeatAt - now) / 1000) + ')';

                }, 1000);
            }).catch(r => {
                this.codeSendingProcess = false;
                this.errors = r?.data?.errors || {};
            });
        },

        changeMobile(){
            this.sentAt = null;
            this.mobileConfirmation = false;
        }
    },

    computed: {
        CSRF(){
            let meta = document.head.querySelector('[name="csrf-token"]');
            if(meta)
                return meta.content || '';

            return '';
        },


    },

    created(){

        if(window.formErrors && Object.prototype.toString.call(window.formErrors) === '[object Object]')
            this.errors = window.formErrors;

        this.getOlds();
    }
}
</script>
