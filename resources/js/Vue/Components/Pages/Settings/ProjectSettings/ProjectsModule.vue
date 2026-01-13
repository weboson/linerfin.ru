<template>
    <section class="projects">

        <transition name="fade-out">
            <div class="loader centered" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>
        </transition>

        <div class="projects__top">
            <div class="projects__types">
                <a href="#" class="projects__type"
                   v-for="(type, index) in filterTypes"
                   @click.prevent="chooseFilterType(index)"
                   :class="index === filterType ? 'selected' : ''">
                    {{ type.name }}
                </a>
            </div>
            <div class="projects__search">
                <div class="prepend">
                    <b-icon-search></b-icon-search>
                </div>
                <input type="text" v-model="search" placeholder="Поиск">
            </div>
        </div>

        <div class="projects__list" v-if="projects.length">

            <div class="project"
                 v-for="project in projects">
                <div>
                    <b-form-checkbox v-model="selectedProjects" :key="project.id" :value="project.id" />
                </div>
                <div class="project__name" @click="chooseProject(project)">
                    {{ project.name }}
                    <span v-if="project.archived" class="archived">
                                архивный
                            </span>
                </div>
                <div class="project__actions">
                    <template v-if="project.archived">
                        <a href="#" class="restore-action"
                           v-on:click="saveChanges(project.id, {archived: 0})">
                            <b-icon-arrow-counterclockwise class="icon-left"/>
                            Восстановить
                        </a>
                        <a href="#" class="remove-action"
                           v-on:click.prevent="deleteProject(project)">
                            <b-icon-x class="icon-left"/>
                            Удалить
                        </a>
                    </template>
                    <a v-else href="#" class="archive-action"
                       v-on:click="saveChanges(project.id, {archived: 1})">
                        <b-icon-archive class="icon-left"/>
                        В архив
                    </a>
                </div>
            </div>
        </div>

        <div class="nothing" v-else>
            <template v-if="search.length">Ничего не найдено</template>
            <template v-else>
                <template v-if="!filterTypeData.params.archived">
                    Нет ни одного направления. <a href="#" @click.prevent="createProjectPopup">Создать</a>
                </template>
                <template v-else>
                    Архив пуст
                </template>
            </template>
        </div>

    </section>
</template>


<script>
import Axios from "../../../../mixins/axios";
import {EventBus} from "../../../../index";

export default {
    name: "ProjectsModule",
    mixins: [ Axios ],

    props: {
        chooseCallback: Function
    },

    data(){
        return {
            projectStack: [],

            selectedProjects: [],

            filterType: 0,
            filterTypes: [
                { name: 'Активные', params: {} },
                { name: 'Архивные', params: { archived: 1 } },
                { name: 'Все', params: { all: 1 } },
            ],

            search: '',
            loading: true
        }
    },

    computed: {
        getParams(){
            if(!this.filterTypeData)
                return '';

            let filterType = this.filterTypeData;
            if(filterType.params)
                return this.queryString(filterType.params);

            return '';
        },

        filterTypeData(){
            if(this.filterTypes[this.filterType])
                return this.filterTypes[this.filterType];

            return { name: '', params: ''}
        },

        projects(){
            let projects = this.projectStack;

            if(this.search){
                let findProjects = [];
                for(let i in projects){
                    let project = projects[i];
                    if(typeof project.name === 'string'){
                        if(~project.name.toLowerCase().indexOf(this.search.toLowerCase()))
                            findProjects.push(project);
                    }
                }

                return findProjects;
            }

            return projects;
        }
    },

    methods: {
        getProjects(){
            this.loading = true;

            let url = '/ui/projects';

            if(this.getParams)
                url += '?' + this.getParams;

            console.log(url);

            this.axiosGET(url).then(r => {
                this.projectStack = r.projects || [];

                setTimeout(() => {
                    this.loading = false
                }, 300);
            })
        },

        saveChanges(project_id, data){
            if(!data || !project_id) return;

            this.axiosPOST('/ui/projects/' + project_id + '/update', data)
                .then(() => {
                    this.getProjects();
                });
        },

        deleteProject(project){
            if(!confirm(`Подтвердите удаление направления ${project.name}`))
                return;

            this.axiosPOST('/ui/projects/' + project.id + "/delete")
                .then(() => { this.getProjects(); });
        },

        chooseFilterType(type){
            this.filterType = type;
            this.getProjects();
        },


        chooseProject(project){
            this.chooseCallback && this.chooseCallback(project);
        }
    },

    created() {
        this.getProjects();
        EventBus.$on('projects.get', this.getProjects)
    },

    destroyed() {
        EventBus.$off('projects.get', this.getProjects)
    }
}
</script>
