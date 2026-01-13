<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            {{ isEditing ? 'Редактирование' : 'Создать компанию' }}
        </div>

        <div class="vuedal-content">
            <div class="form-group">
                <label>Наименование</label>
                <input v-model="name" type="text" class="form-control">
                <small class="form-text text-muted">
                    Введите название Вашей компании, например ООО Апельсин
                    <!-- не спрашивайте, почему апельсин :) -->
                </small>
            </div>
            <div class="form-group">
                <label>Тип организации</label>
                <select-ui v-model="opf_type" el-class="form-control" class="w-100"
                           :options="$store.state.OPFTypes" required/>
            </div>
            <div class="form-group">
                <label>
                    Доменное имя
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">https://</span>
                    </div>
                    <input v-model="subdomain" type="text" class="form-control">
                    <div class="input-group-append">
                        <span class="input-group-text">.{{ domain }}</span>
                    </div>
                </div>
                <small class="form-text text-muted">
                    Допустимы латинские буквы без учета регистра, цифры и нижнее подчеркивание, например example_123
                </small>
            </div>

            <div class="form-group">
                <label>ИНН</label>
                <input v-model="inn" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label>КПП</label>
                <input v-model="kpp" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label>ОГРН</label>
                <input v-model="ogrn" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label>Почтовый адрес</label>
                <input v-model="address" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label>Юр. адрес</label>
                <input v-model="legal_address" type="text" class="form-control">
            </div>

            <p class="h5 mb-4 mt-5">Выставление счетов</p>

            <div class="form-group">
                <label>Должность руководителя</label>
                <input v-model="director_position" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>ФИО руководителя</label>
                <input v-model="director_name" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>Должность бухгалтера</label>
                <input v-model="accountant_position" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>ФИО бухгалтера</label>
                <input v-model="accountant_name" type="text" class="form-control">
            </div>
        </div>

        <modal-buttons @save="save" @cancel="closePopup"></modal-buttons>
    </div>
</template>

<script>
import ModalButtons from '../../UI/Modals/modal-buttons';
import axios from "../../../mixins/axios";
import utils from "../../../mixins/utils";

export default {
    name: "company-popup",
    components: { ModalButtons },
    mixins: [ axios, utils ],
    props: { data: Object },

    data(){
        return {
            name: '',
            opf_type: null,
            subdomain: '',
            inn: '',
            kpp: '',
            ogrn: '',
            address: '',
            legal_address: '',
            director_position: '',
            director_name: '',
            accountant_position: '',
            accountant_name: ''
        }
    },

    computed: {
        domain(){
            return APPDATA?.DOMAIN || 'linerfin.ru/';
        },

        isEditing(){
            return !!this.data?.id;
        }
    },

    methods: {
        save(){
            let data = {
                name: this.name,
                opf_type: this.opf_type,
                subdomain: this.subdomain,
                inn: this.inn,
                kpp: this.kpp,
                ogrn: this.ogrn,
                address: this.address,
                legal_address: this.legal_address,
                director_position: this.director_position,
                director_name: this.director_name,
                accountant_position: this.accountant_position,
                accountant_name: this.accountant_name
            };
            const url = this.isEditing ? '/ui/companies/' + this.data.id + '/save' : '/ui/companies/save';

            this.axiosPOST(url, data).then(r => {
                this.$emit('vuedals:close', r);
            });
        },

        closePopup(){
            if(confirm('Данные не сохранены. Закрыть?'))
                this.$emit('vuedals:close', null);
        },
    },

    created() {
        if(this.data){
            let d = this.data;
            this.name = d?.name || '';
            this.opf_type = d?.organization_type || '';
            this.subdomain = d?.subdomain || '';
            this.inn = d?.inn || '';
            this.kpp = d?.kpp || '';
            this.ogrn = d?.ogrn || '';
            this.address = d?.address || '';
            this.legal_address = d?.legal_address || '';
            this.director_position = d?.director_position || '';
            this.director_name = d?.director_name || '';
            this.accountant_position = d?.accountant_position || '';
            this.accountant_name = d?.accountant_name || '';
        }
    }


}
</script>
