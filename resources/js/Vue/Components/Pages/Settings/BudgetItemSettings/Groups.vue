<template>
    <div class="budget-items__groups">
        <header>
            Группы
            <span class="d-inline-block text-primary ml-2" v-b-tooltip.hover title="Организуйте статьи для удобства">
                <b-icon-question-circle/>
            </span>
        </header>
        <div class="content">
            <div class="budget-items__group all" @click="choose('all')"
                 :class="selectedAll ? 'selected' : ''">
                <div class="group-name">Все</div>
            </div>
            <div class="budget-items__group" v-for="group in groups"
                 :class="group.id === selectedId ? 'selected' : ''">
                <div class="group-name" @click="choose(group)">{{ group.name }}</div>
                <div class="actions" @click="groupPopup(group)">
                    <b-icon-pencil/>
                </div>
            </div>

            <div class="budget-items__group archived text-secondary" @click="choose('archive')"
                 :class="selectedArchive ? 'selected' : ''">
                <div class="group-name">Архив</div>
            </div>
        </div>
        <footer>
            <a href="#" class="budget-items__groups-btn" @click.prevent="groupPopup(null)">
                <span>Новая группа</span>
            </a>
        </footer>
    </div>
</template>

<script>
export default {
    props: {
        groups: Array
    },

    data(){
        return {
            selectedAll: true,
            selectedArchive: false,
            selectedId: null
        }
    },

    methods: {
        choose(group){
            this.$emit('choose', group);
            this.selectedAll = group === 'all';
            this.selectedArchive = group === 'archive';

            if(typeof group === 'object'){
                this.selectedId = group?.id || null;
            }
            else
                this.selectedId = null;
        },

        groupPopup(group){
            this.$emit('group-popup', group);
        }
    }
}
</script>
