<template>
    <div class="form-wrapper loader-wrapper">
        <transition name="fade-out">
            <div class="loader centered" style="background: #FCFCFC;" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
        </transition>

        <small class="form-text text-muted mt-0 mb-2">
            Страница компании будет доступна по ссылке:
        </small>

        <div class="form-group with-tip">
            <div class="input-group position-relative">
                <div class="input-group-prepend">
                    <span class="input-group-text">https://</span>
                </div>

                <input type="text" name="company_subdomain"
                       v-model="subdomain" @blur="checkSubdomain"
                       @focus="subdomainAffected = true"
                       @keyup="checkSubdomainOnKeyup"
                       class="form-control" required>

                <div class="input-group-append">
                    <span class="input-group-text">.linerfin.ru/</span>
                </div>

                <div class="form-tip" v-if="smallLoading">
                    <b-icon-three-dots animation="cylon"/>
                </div>
                <div class="form-tip text-primary" v-else v-b-tooltip.hover
                     title="Домен используется для быстрого доступа к аккаунту. Вы можете использовать сгенерированный домен или ввести другой">
                    <b-icon-question-circle/>
                </div>

            </div>

            <transition name="slide">
                <small class="form-text text-muted" v-if="subdomainAffected">
                    Допустимы латинские буквы без учета регистра, цифры и нижнее подчеркивание, например example_123
                    <br>
                    <a href="#" @click.prevent="generateSubdomain">Автоматически</a>
                </small>
            </transition>

            <small v-if="subdomainAlreadyExists" class="form-text text-danger">
                Адрес занят, выберите другой
            </small>
        </div>

        <button class="btn btn-primary w-100 mt-3" @click.prevent="commitData" :disabled="subdomainAlreadyExists">
            Продолжить
        </button>

    </div>
</template>

<script>
import CreateCompanyMixin from "./CreateCompanyMixin";
import axios from "../../../Vue/mixins/axios";

export default {
    name: "CreateCompany5",
    mixins: [ CreateCompanyMixin, axios ],

    props: {
        options: Object
    },

    data(){
        return{
            subdomainAlreadyExists: false,
            subdomainAffected: false,

            company_name: '',
            subdomain: '',

            loading: false,
            smallLoading: false,
            checkTimer: null
        }
    },

    methods: {

        commitData(){
            if(this.subdomainAlreadyExists) return;
            this.$emit('commit', {
                company_subdomain: this.subdomain
            });
        },

        checkSubdomain(){
            if(!this.subdomain)
                return;

            this.smallLoading = true;

            this.axiosPOST('/ui/check-subdomain', {subdomain: this.subdomain}).then(r => {
                this.smallLoading = false;
                this.subdomainAlreadyExists = !r.free;
            }).catch(() => {
                this.smallLoading = false;
            })
        },

        checkSubdomainOnKeyup(){
            clearTimeout(this.checkTimer);
            this.checkTimer = setTimeout(this.checkSubdomain, 500);
        },

        generateSubdomain(){
            if(!this.company_name)
                return;

            this.loading = true;

            this.axiosPOST('/ui/generate-subdomain', {
                name: this.company_name
            }).then(r => {
                this.loading = false;

                this.subdomain = r.subdomain || this.subdomain;
                this.subdomainAffected = this.subdomainAlreadyExists = false;
            }).catch(() => {
                this.loading = false;
            });
        }
    },

    created() {
        this.subdomain = this.options?.company_subdomain || '';
        this.company_name = this.options?.company_name || '';

        if(!this.subdomain)
            this.generateSubdomain();
    }
}
</script>
