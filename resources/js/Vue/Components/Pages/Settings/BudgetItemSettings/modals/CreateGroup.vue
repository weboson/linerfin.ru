<template>
    <div class="center-modal__content">
        <header v-if="group">Изменить группу</header>
        <header v-else>Новая группа статей</header>
        <form @submit.prevent="saveGroup" class="loader-wrapper">
            <div class="loader loader__white-bg centered" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
            <div class="form-group">
                <input-ui v-model="name" placeholder="Название группы"/>
            </div>
            <div class="form-group">
                <input-ui v-model="desc" placeholder="Описание"/>
            </div>
            <footer class="d-flex justify-content-between align-items-center">
                <div class="left">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span v-if="group">Сохранить</span>
                        <span v-else>Создать</span>
                    </button>
                    <button @click.prevent="$emit('vuedals:close')" class="btn btn-outline-secondary btn-sm">Отмена</button>
                </div>
                <div class="right text-right">
                    <a href="#" @click.prevent="removeGroup" class="text-decoration-none text-danger" v-if="this.group">Удалить группу</a>
                </div>
            </footer>
        </form>
    </div>
</template>

<script>
import Axios from "../../../../../mixins/axios";
import {EventBus} from "../../../../../index";
import Utils from "../../../../../mixins/utils";

export default {
    mixins: [ Axios, Utils ],
    name: "CreateGroup",
    props: {
        group: Object,
        category: String
    },
    data(){
        return {
            loading: false,
            name: '',
            desc: ''
        }
    },
    methods: {
        saveGroup(){
            let url = "/ui/budget-items/groups";
            if(this.group){
                if(!this.group.id){
                    this.errorNotify("Не удалось сохранить группу");
                    return false;
                }
                url += '/' + this.group.id;
            }

            url += '/save'

            this.loading = true;

            this.axiosPOST(url, {
                name: this.name,
                type: this.category,
                desc: this.desc
            }).then(r => {
                if(r.success){
                    this.$emit('vuedals:close');
                    EventBus.$emit('app:update');
                }
            }).finally(() => {
                this.loading = false;
            })
        },

        removeGroup(){
            if(!this.group || !this.group.id) return;
            const url = `/ui/budget-items/groups/${this.group.id}/delete`;
            this.loading = true;
            this.axiosPOST(url).then(r => {
                this.$emit('vuedals:close');
            }).catch(r => {
                this.errorNotify('Не удалось удалить группу');
            }).finally(() => {
                this.loading = false;
            })
        }
    },

    created(){
        if(this.group){
            this.name = this.group?.name
            this.desc = this.group?.desc
        }
    }
}
</script>
