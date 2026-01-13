<template>
    <div class="center-modal__content choose-budget-item loader-wrapper p-4 pt-5">
        <a href="#" @click.prevent="$emit('vuedals:close', null)" class="close"><b-icon-x/></a>

        <projects-module :choose-callback="chooseProject"></projects-module>

        <div class="toolbar mb-0 mt-4">
            <button class="btn btn-primary btn-sm" @click="createProject">Создать направление</button>
        </div>
    </div>
</template>

<script>
import ProjectsModule from "../../Settings/ProjectSettings/ProjectsModule";
import CreatePopup from "../../Settings/ProjectSettings/modals/ProjectCreate";
import { EventBus } from "../../../../index";
export default {
    name: "ProjectPopup",
    components: { ProjectsModule },

    methods: {
        chooseProject(project){
            console.log('choose project', {project})
            this.$emit('vuedals:close', { project })
        },

        createProject(){
            this.$emit('vuedals:new', {
                name: 'center-modal',
                dismissable: false,
                escapable: true,
                component: CreatePopup,
                onClose: () => {
                    EventBus.$emit('projects.get')
                }
            });
        }
    }
}
</script>
