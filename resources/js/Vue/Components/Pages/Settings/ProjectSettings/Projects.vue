<template>
    <div class="container-fluid top-header__content">
        <header>
            <div class="actions">
                <a href="#" class="btn btn-primary"
                   @click="createProjectPopup">
                    Создать направление
                </a>
            </div>
        </header>

        <div class="content loader-wrapper">
            <projects-module></projects-module>
        </div>
    </div>
</template>


<script>
import CreatePopup from "./modals/ProjectCreate";
import ProjectsModule from "./ProjectsModule";
import { EventBus } from "../../../../index";

export default {
    name: 'Projects',
    components: { ProjectsModule },

    methods: {
        createProjectPopup(){
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
