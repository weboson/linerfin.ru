<script>
import axios from "../../../../mixins/axios";
import utils from "../../../../mixins/utils";
import ModalButtons from "../../../UI/Modals/modal-buttons"

export default {
    name: "edit-popup",
    mixins: [ axios, utils ],
    components: { ModalButtons },
    props: { data: Object },

    data(){
        return {
            id: null,
            name: '',
            num: '',
            balance: 0,
            bank_name: '',
            bank_bik: '',
            bank_swift: '',
            bank_inn: '',
            bank_kpp: '',
            bank_correspondent: '',
            comment: '',

            setBankManually: false,
            loading: false,
        }
    },

    computed: {
        isEditing(){
            return !!this.data?.id && !!this.id;
        }
    },

    methods: {
        save(){
            const url = `/ui/banks/${this.id ? this.id + '/' : ''}save`;
            this.loading = true;

            let data = {
                name:       this.name,
                num:        this.num,
                balance:    this.balance,
                bank_name:  this.bank_name,
                bank_bik:   this.bank_bik,
                bank_swift: this.bank_swift,
                bank_inn:   this.bank_inn,
                bank_kpp:   this.bank_kpp,
                bank_correspondent: this.bank_correspondent,
                comment:    this.comment,
            };


            this.axiosPOST(url, data)
                .then(r => {
                    this.$emit('vuedals:close', r);
                    this.loading = false;
                })
                .catch(r => this.loading = false)
        },

        close(){
            if(confirm('Данные не сохранены. Закрыть?'))
                this.$emit('vuedals:close');
        },

        autocompleteBank(bank){
            this.bank_name          = bank.value || '';
            this.bank_bik           = bank.data.bic || '';
            this.bank_swift         = bank.data.swift || '';
            this.bank_inn           = bank.data.inn || '';
            this.bank_kpp           = bank.data.kpp || '';
            this.bank_correspondent = bank.data.correspondent_account || '';
        }
    },


    created() {
        if(this.data){
            this.id                 = this.data.id;
            this.name               = this.data.name || '';
            this.num                = this.data.num || '';
            this.balance            = this.data.balance || 0;
            this.bank_name          = this.data.bank_name || '';
            this.bank_bik           = this.data.bank_bik || '';
            this.bank_swift         = this.data.bank_swift || '';
            this.bank_inn           = this.data.bank_inn || '';
            this.bank_kpp           = this.data.bank_kpp || '';
            this.bank_correspondent = this.data.bank_correspondent || '';
            this.comment            = this.data.comment || '';
        }
    }

}
</script>



<template>
    <div class="vuedal-wrapper">

        <div class="vuedal-header">
            {{ isEditing ? 'Редактирование' : 'Новый расчётный счёт' }}
        </div>


        <!-- Modal Main Content -->
        <div class="vuedal-content loader-wrapper">

            <transition name="fade-out">
                <div class="loader centered" v-if="loading">
                    <b-spinner variant="primary" label="Загрузка"></b-spinner>
                </div>
            </transition>

            <div class="form-group">
                <label>Название счёта</label>
                <input v-model="name" type="text" class="form-control" placeholder="Введите название счёта">
                <form-errors :errors="axiosErrors" name="name" />
            </div>

            <div class="form-group">
                <label>Номер счёта</label>
                <input v-model="num" type="text" class="form-control" placeholder="Введите номер счёта">
                <form-errors :errors="axiosErrors" name="num" />
            </div>

            <div class="form-group">
                <label>Баланс</label>
                <div class="with-tip">
                    <input v-model="balance" type="text" class="form-control" placeholder="Введите текущий баланс счёта"
                           :disabled="isEditing">
                    <div class="form-tip text-primary" v-b-tooltip.hover title="Введите актуальный остаток на вашем расчётном счёте">
                        <b-icon-question-circle/>
                    </div>

                    <form-errors :errors="axiosErrors" name="balance" />
                </div>

                <small v-if="isEditing" class="form-text text-muted">
                    Для корректирования баланса используйте "Корректировать сумму"
                </small>
            </div>


            <section class="select-bank border-bottom  mb-3 pb-5">
                <template v-if="!setBankManually">
                    <div class="form-group">
                        <label>Выбрать банк</label>
                        <input-autocomplete type="bank" v-model="bank_name" strict
                                            title-key="value"
                                            :subtitle-key="bank => bank.data.bic"
                                            @choose="autocompleteBank"/>
                        <small class="form-text text-muted">Для поиска банка введите Название, БИК, SWIFT</small>


                        <form-errors :errors="axiosErrors" name="bank_name" />
                    </div>

                    <div class="bank-description">
                        <p v-if="bank_bik">БИК {{ bank_bik }}</p>
                        <p v-if="bank_correspondent">Кор. счёт {{ bank_correspondent }}</p>
                        <p v-if="bank_inn">ИНН {{ bank_inn }}</p>
                        <p v-if="bank_kpp">КПП {{ bank_kpp }}</p>
                        <p v-if="bank_swift">SWIFT {{ bank_swift }}</p>
                    </div>

                    <a @click.prevent="setBankManually = true" href="#">Ввести банк вручную</a>

                </template>

                <template v-else>

                    <div class="form-group">
                        <label>
                            Название Банка <a @click.prevent="setBankManually = false" href="#">найти</a>
                        </label>
                        <input v-model="bank_name" type="text" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>БИК Банка</label>
                        <input v-model="bank_bik" type="text" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>ИНН Банка</label>
                        <input v-model="bank_inn" type="text" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>КПП Банка</label>
                        <input v-model="bank_kpp" type="text" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Кор. счёт Банка</label>
                        <input v-model="bank_correspondent" type="text" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>SWIFT Банка</label>
                        <input v-model="bank_swift" type="text" class="form-control">
                    </div>

                </template>

            </section>

            <div class="form-group">
                <label>Комментарий</label>
                <textarea v-model="comment" class="form-control"></textarea>
            </div>


        </div>
        <!-- end main content -->


        <modal-buttons @save="save" @cancel="close" :disabled="loading"/>
    </div>

</template>
