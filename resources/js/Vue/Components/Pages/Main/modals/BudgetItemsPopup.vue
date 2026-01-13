<template>
    <div class="center-modal__content choose-budget-item loader-wrapper">
        <a href="#" @click.prevent="$emit('vuedals:close', null)" class="close"><b-icon-x/></a>
        <budget-items :category="this.category" :choosing="true" @choose="chooseItem"/>
    </div>
</template>

<script>
import BudgetItems from "../../Settings/BudgetItemSettings/BudgetItems";

export default {
    name: "BudgetItemsPopup",
    components: { BudgetItems },
    props: {
        category: {
            type: String,
            default: 'income'
        }
    },
    methods: {
        chooseItem(args){
            if(args instanceof Event || !args.item) return;
            this.$emit('vuedals:close', { item: args.item });
        },
        closePopup(){
            this.$emit('vuedals:close', null);
        }
    }
}
</script>


<style lang="sass">
    .vuedals .vuedal.center-modal.choose-budget-item-popup
        //width: 900px

    .vuedals .vuedal.center-modal .center-modal__content.choose-budget-item
        padding: 0
        width: 900px

        .header-title
            padding: 20px 20px 0

        a.close
            position: absolute
            top: 20px
            right: 20px
</style>
