<template>
    <div class="center-modal__content">
        <header>Создать направление</header>
        <form @submit.prevent="createProject">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Введите название направления" required
                       v-model="projectName">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Описание" v-model="description">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Создать</button>
            <button @click.prevent="$emit('vuedals:close')" class="btn btn-outline-secondary btn-sm">Отмена</button>
        </form>
    </div>
</template>

<script>
import Axios from "../../../../../mixins/axios";
import {EventBus} from "../../../../../index";

export default {
    mixins: [ Axios ],
    name: "ProjectCreate",
    data(){
        return {
            projectName: '',
            description: ''
        }
    },
    methods: {
        createProject(){
            this.axiosPOST('/ui/projects/create', {
                name: this.projectName,
                comment: this.description
            }).then(r => {
                if(r.success){
                    this.$emit('vuedals:close');
                    EventBus.$emit('app:update');
                }
            })
        }
    }
}
</script>
