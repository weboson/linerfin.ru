<template>
    <div class="container-fluid top-header__content">
        <budget-items category="income"></budget-items>
        <budget-items category="expense" class="mt-5"></budget-items>
    </div>
</template>

<script>

import BudgetItems from "./BudgetItems";

export default {
    name: "BudgetItem",
    components: {
        BudgetItems
    },

    data(){
        return {

        }
    }
}
</script>
