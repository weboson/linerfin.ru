<template>
    <div>

        <div class="header-title h5">
            {{ headerTitle }}
        </div>

        <section class="budget-items loader-wrapper">

            <div class="loader centered" v-if="loading">
                <b-spinner variant="primary" label="Загрузка"></b-spinner>
            </div>

            <budget-groups @choose="chooseGroup" :groups="groups"
                           @group-popup="editGroupPopup"></budget-groups>

            <div class="budget-items__content">

                <div class="content">
                    <table class="budget-items__table custom-table layout-fixed" :class="choosing ? 'with-choosing' : ''">
                        <thead>
                        <tr v-if="!selectedItems.length">
                            <th></th>
                            <th>{{ headerTitle }}</th>
                            <th>Вид деятельности</th>
                            <th>Группа</th>
                        </tr>
                        <tr v-else class="edit-label">
                            <td>{{ selectedItems.length }}</td>
                            <td colspan="3" class="actions">
                                <a href="#" v-if="selectedItems.length === 1 && selectedItem"
                                   @click.prevent="createBudgetItemPopup(selectedItem)">Редактировать</a>
                                <a href="#" v-if="!archiveSelected" @click.prevent="archiveItems" class="text-secondary">В архив</a>
                                <a href="#" v-else @click.prevent="publishItems" class="text-success">Опубликовать</a>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in budgetItemStack">
                            <td><b-form-checkbox name="items" v-model="selectedItems" :value="item.id"/></td>
                            <td @click.prevent="chooseItem(item)">{{ item.name }}</td>
                            <td @click.prevent="chooseItem(item)">{{ getBudgetActivityType(item) }}</td>
                            <td @click.prevent="chooseItem(item)">{{ item.group ? item.group.name : '' }}</td>
                        </tr>
                        <tr v-if="!budgetItemStack.length">
                            <td colspan="4" class="nothing">{{ archiveSelected ? 'Архив пуст' : 'Ничего не найдено' }}</td>
                        </tr>

                        <tr v-if="!archiveSelected" class="footer">
                            <td colspan="4" :class="!budgetItemStack.length ? 'text-center' : ''">
                                <a href="#" class="btn btn-primary btn-sm"
                                   @click.prevent="createBudgetItemPopup()">
                                    Создать
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>
</template>

<script>
import Axios from "../../../../mixins/axios";
import Utils from "../../../../mixins/utils";
import CreatePopup from "./modals/CreatePopup";
import CreateGroup from "./modals/CreateGroup";
import BudgetGroups from "./Groups";

export default {
    name: "BudgetItemIncome",
    mixins: [ Axios, Utils ],
    components: { BudgetGroups },

    props: {
        category: {
            default: 'income',
            type: String
        },
        choosing: {
            type: Boolean,
            default: false
        }
    },

    data(){
        return {
            budgetItemStack: [], // budget item list
            selectedItems: [], // selected items
            search: '', // search string

            group: null,
            groups: [],

            types: [],

            loading: true
        }
    },

    computed: {
        headerTitle(){
            switch (this.category){
                case 'income':
                    return 'Статьи прихода'
                case 'expense':
                    return 'Статьи расхода'
                case 'transfer':
                    return 'Статьи перевода'
            }
            return ''
        },


        selectedItem(){
            if(this.selectedItems.length !== 1)
                return null;

            const itemId = this.selectedItems[0];
            return this.budgetItemStack.find(i => i.id === itemId) || null;
        },

        archiveSelected(){
            return this.group === 'archive';
        }
    },

    methods: {
        getBudgetItems(){
            this.loading = true;

            let url = '/ui/budget-items/' + this.category;

            // query group
            if(this.group && this.group !== 'all'){
                if(this.group === 'archive')
                    url += "?archived=1";
                else if(typeof this.group === 'object')
                    url += "?group=" + this.group?.id;
            }


            this.axiosGET(url).then(r => {
                this.budgetItemStack = r.budget_items || [];
                this.groups = r.groups || [];
                this.types = r.types || [];

                setTimeout(() => {
                    this.loading = false
                }, 300);
            })
        },

        saveChanges(item_id, data){
            if(!data || !item_id) return;

            this.axiosPOST('/ui/budget-items/' + item_id + '/update', data)
                .then(() => {
                    this.getBudgetItems();
                });
        },

        archiveItems(){
            const ids = this.selectedItems.join(',');
            this.axiosPOST('/ui/budget-items/archive', { ids })
                .then(response => {
                    this.selectedItems = [];
                    this.getBudgetItems();
                })
                .catch(response => {
                    this.errorNotify('Не удалось архивировать статьи');
                })
        },

        publishItems(){
            const ids = this.selectedItems.join(',');
            this.axiosPOST('/ui/budget-items/publish', { ids })
                .then(response => {
                    this.selectedItems = [];
                    this.getBudgetItems();
                })
                .catch(response => {
                    this.errorNotify('Не удалось архивировать статьи');
                })
        },

        createBudgetItemPopup(item){
            if(item) item = Object.assign({}, item);
            this.selectedItems = [];

            this.$emit('vuedals:new', {
                name: 'center-modal',
                dismissable: false,
                escapable: true,
                component: CreatePopup,
                props: {
                    item,
                    groups: this.groups,
                    category: this.category
                },
                onClose: () => {
                    this.getBudgetItems();
                }
            });
        },

        editGroupPopup(group){
            this.$emit('vuedals:new', {
                name: 'center-modal',
                dismissable: false,
                escapable: true,
                props: {
                    group: group || null,
                    category: this.category
                },
                component: CreateGroup,
                onClose: () => {
                    this.getBudgetItems();
                }
            });
        },


        // Choose budget group
        chooseGroup(group){
            this.group = group || null;
            this.getBudgetItems();
        },


        // Choose budget item
        chooseItem(item){
            console.log(item);
            if(this.choosing)
                this.$emit('choose', { item })
            else {
                let index = this.selectedItems.indexOf(item.id);
                if(~index){
                    this.selectedItems.splice(index, 1);
                }
                else
                    this.selectedItems.push(item.id);
            }
        },


        // Get activity type
        getBudgetActivityType(budgetItem){
            if(budgetItem.type) {
                switch (budgetItem.type.type) {
                    case 'operation':
                        return 'операционная'

                    case 'investment':
                        return 'инвестиционная'

                    case 'financial':
                        return 'финансовая'

                    case 'cost':
                        return ''
                }
            }
            return '';
        }
    },

    created() {
        this.getBudgetItems();
    }
}
</script>
