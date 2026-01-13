<template>
    <div class="center-modal__content budget-item-popup">
        <header v-if="item">Изменить статью {{ itemCategoryStr }}</header>
        <header v-else>Новая статья {{ itemCategoryStr }}</header>
        <form @submit.prevent="saveBudgetItem">
            <div class="form-group">
                <input-ui placeholder="Название статьи" v-model="name" required/>
            </div>
            <div class="form-group">
                <input-ui placeholder="Описание" v-model="comment"/>
            </div>

            <div class="form-group" v-if="groups.length">
                <input-autocomplete placeholder="Группа" v-model="groupTitle" @choose="chooseGroup" :options="groups" value-key="id" title-key="name" disable-timeout chevron/>
                <!--<select-ui :options="groups" v-model="group" placeholder="Группа"
                           class="w-100" el-class="form-control"/>-->
            </div>

            <div class="form-group">
                <label>Тип {{ itemCategoryStr }}</label>
                <ul class="budget-item-type">
                    <li v-for="itemType in types">
                        <b-form-radio name="itemType" v-model="type" :value="itemType.id">
                            <span class="type-name">{{ itemType.name }}</span>
                            <span class="type-tag" :class="'type-' + itemType.type">
                                {{ getTypeTag(itemType) }}
                            </span>
                        </b-form-radio>
                    </li>
                </ul>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Создать</button>
            <button @click.prevent="$emit('vuedals:close')" class="btn btn-outline-secondary btn-sm">Отмена</button>
        </form>
    </div>
</template>

<script>
import Axios from "../../../../../mixins/axios";
import Utils from "../../../../../mixins/utils";
import {EventBus} from "../../../../../index";

export default {
    mixins: [ Axios, Utils ],
    name: "ProjectCreate",

    props: {
        item: Object,
        groups: Array,
        category: String
    },
    data(){
        return {
            name: '',
            group: null,
            groupTitle: null,
            comment: '',

            types: [],
            type: null,
        }
    },

    computed: {
        itemCategoryStr(){
            switch (this.category){
                case 'income':
                    return 'дохода'
                case 'expense':
                    return 'расхода'
                case 'transfer':
                    return 'перевода'
            }
            return ''
        }
    },

    methods: {
        saveBudgetItem(){

            let url = ["/ui/budget-items"];
            if(this.item){
                if(!this.item.id){
                    this.errorNotify("Не удалось сохранить статью");
                    return false;
                }
                url.push(this.item.id);
            }

            // url.push(this.category)
            url.push('save')

            this.axiosPOST(url.join('/'), {
                name: this.name,
                category: this.category,
                comment: this.comment,
                type_id: this.type,
                group_id: this.group
            }).then(r => {
                if(r.success){
                    this.$emit('vuedals:close', { item: r.item });
                    EventBus.$emit('app:update');
                }
            })
        },


        chooseGroup(group){
            this.group = group.id
            this.groupTitle = group.name
        },

        getTypeTag(type){
            switch (type.type){
                case 'investment':
                    return 'инвестиционная'
                case 'operation':
                    return 'операционная'
                case 'financial':
                    return 'финансовая'
                case 'cost':
                    return 'себестоимость'
            }
            return ''
        }
    },

    created(){
        if(this.item){
            const item = this.item
            this.name = item.name
            this.group = item.group_id
            this.groupTitle = item?.group?.name
            this.type = item.type_id
            this.comment = item.comment
        }

        this.types = this.$store.state.budgetItemTypes.filter(i => i.category === this.category)
    }
}
</script>

<style lang="sass">
    .budget-item-popup
        ul.budget-item-type
            list-style: none
            margin: 0 0 30px 0
            padding: 0

            li + li
                margin-top: 7px





        .type-tag
            display: inline-block
            margin-left: 3px
            padding: 0 7px
            font-size: 13px
            border: 1px solid
            border-radius: 20px

            @mixin withColor($color)
                border-color: $color
                background-color: lighten($color, 50)
                color: darken($color, 20)


            &.type-investment
                @include withColor(#65d25a)


            &.type-operation
                @include withColor(#527ad9)


            &.type-financial
                @include withColor(#dcc44f)


            &.type-cost
                display: none



</style>
