<script>

import utils from "../../../mixins/utils";
import formats from "../../../mixins/formats";
import axios from "../../../mixins/axios";

import { EventBus } from "../../../index";

export default {
    name: "Common",
    mixins: [ utils, formats, axios ],

    data(){
        return {
            loader: false,

            editPhone: false,
            newPhone: '',

            editName: false,
            newName: '',

            editEmail: false,
            newEmail: '',

            editPassword: false,
            newPassword: '',
            repeatPassword: '',
            showPassword: false
        }
    },

    computed: {
        user(){
            return this.$store.state.user || {};
        },

        phoneMasked(){
            if(!this.user.phone)
                return '';

            let phone = this.user.phone;
            return this.formatPhone(phone, true);
        }
    },

    methods: {

        editNameLabel(){
            this.editName = true;
            this.newName = this.getFullName(this.user);
        },

        saveUserName(){
            this.loader = true;
            this.axiosPOST('/ui/settings/change-name', { name: this.newName }).then(data => {
                EventBus.$emit('app:update');
                this.editName = false;
                this.newName = '';
            }).finally(() => {
                setTimeout(() => { this.loader = false }, 300);
            });
        },

        editPhoneLabel(){
            this.editPhone = true;
            this.newPhone = this.user.phone;
        },

        savePhone(){
            this.loader = true;
            this.axiosPOST('/ui/settings/change-phone', { phone: this.newPhone }).then(data => {
                EventBus.$emit('app:update');
                this.editPhone = false;
                this.newPhone = '';
            }).finally(() => {
                setTimeout(() => { this.loader = false }, 300);
            });
        },

        editEmailLabel(){
            this.editEmail = true;
            this.newEmail = this.user.email;
        },
        saveEmail(){
            this.loader = true;
            this.axiosPOST('/ui/settings/change-email', { email: this.newEmail }).then(data => {
                EventBus.$emit('app:update');
                this.editEmail = false;
                this.newEmail = '';
            }).finally(() => {
                setTimeout(() => { this.loader = false }, 300);
            });
        },


        editPasswordLabel(){
            this.editPassword = true;
            this.newPassword = this.repeatPassword = '';
            this.showPassword = false;
        },

        changePassword(){

            if(!this.newPassword || !this.repeatPassword){
                this.errorNotify('Введите пароль');
                return;
            }

            if(this.newPassword !== this.repeatPassword){
                this.errorNotify('Пароли не совпадают');
                return;
            }

            this.loader = true;
            this.axiosPOST('/ui/settings/change-password', {
                password: this.newPassword,
                r_password: this.repeatPassword
            }).then(data => {
                EventBus.$emit('app:update');
                this.editPassword = false;
                this.newPassword = this.repeatPassword = '';
            }).finally(() => {
                setTimeout(() => { this.loader = false }, 300);
            });
        },
    }
}
</script>



<template>
    <div class="container-fluid top-header__content settings__common">
        <div class="content">

            <table class="custom-table th-170">
                <tr>
                    <th>Фамилия, имя <br> и отчество</th>
                    <td>
                        <div>{{ getFullName(user) }}</div>
                        <a @click.prevent="editNameLabel" v-if="!editName" href="#">
                            Изменить
                        </a>
                    </td>
                </tr>
                <tr v-if="editName" class="edit">
                    <td colspan="2">
                        <form @submit.prevent="saveUserName">
                            <div class="form-group">
                                <input v-model="newName" type="text" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-link">Сохранить</button>
                            <a @click.prevent="editName = false" href="#" class="btn-link btn text-secondary">Отмена</a>
                        </form>
                    </td>
                </tr>


                <tr>
                    <th>
                        Телефон
                    </th>
                    <td v-if="user.phone">
                        <div>{{ phoneMasked }}</div>
                        <a @click.prevent="editPhoneLabel" v-if="!editPhone" href="#">
                            Изменить
                        </a>
                    </td>
                    <td v-else>
                        <form @submit.prevent="savePhone">
                            <div class="input-group">
                                <input v-model="newPhone" type="text" class="form-control" placeholder="Введите номер телефона">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-outline-primary">Сохранить</button>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                <tr v-if="editPhone" class="edit">
                    <th>
                        Новый номер <br> телефона
                    </th>
                    <td colspan="2">
                        <form @submit.prevent="savePhone">
                            <div class="form-group">
                                <input v-model="newPhone" type="text" class="form-control" placeholder="Введите номер телефона">
                            </div>
                            <button type="submit" class="btn btn-link">Сохранить</button>
                            <a href="#" @click.prevent="editPhone = false" class="btn btn-link text-secondary">Отмена</a>
                        </form>
                    </td>
                </tr>

                <tr>
                    <th>Электронная <br> почта</th>
                    <td>
                        <div>{{ user.email }}</div>
                        <a @click.prevent="editEmailLabel" v-if="!editEmail" href="#">Изменить</a>
                    </td>
                </tr>
                <tr v-if="editEmail" class="edit">
                    <td colspan="2">
                        <form @submit.prevent="saveEmail">
                            <div class="form-group">
                                <input type="text" class="form-control" v-model="newEmail">
                            </div>
                            <button type="submit" class="btn btn-link">Сохранить</button>
                            <a @click.prevent="editEmail = false" href="#" class="btn btn-link text-secondary">Отмена</a>
                        </form>
                    </td>
                </tr>



                <tr>
                    <th>Пароль</th>
                    <td>
                        <div>**********</div>
                        <a @click.prevent="editPasswordLabel" v-if="!editPassword" href="#">Изменить</a>
                    </td>
                </tr>
                <tr v-if="editPassword" class="edit">
                    <td colspan="2">
                        <form @submit.prevent="changePassword">
                            <div class="form-group">
                                <input v-model="newPassword" :type="showPassword ? 'text' : 'password'" class="form-control" placeholder="Новый пароль">
                            </div>
                            <div class="form-group">
                                <input v-model="repeatPassword" :type="showPassword ? 'text' : 'password'" class="form-control" placeholder="Повторите пароль">
                            </div>
                            <b-form-checkbox v-model="showPassword">Показать пароль</b-form-checkbox>

                            <button class="btn btn-link" type="submit">
                                Сохранить
                            </button>
                            <a @click.prevent="editPassword = newPassword = repeatPassword = ''"
                               href="#" class="btn btn-link text-secondary">
                                Отмена
                            </a>
                        </form>
                    </td>
                </tr>

            </table>

        </div>
    </div>
</template>
