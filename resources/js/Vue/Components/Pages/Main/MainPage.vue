<template>
    <div class="container-fluid">

        <balance :filters="filters"></balance>

        <section class="transactions">

            <!-- Toolbar part -->
            <toolbar v-model="filters" @input="updateTransactions"></toolbar>

            <!-- Transaction charts -->
            <graph :filters="filters"></graph>

            <!-- Transactions list -->
            <transactions :filters="filters"/>

        </section>

    </div>
</template>

<script>

import Balance from './Balance';
import Toolbar from "./toolbar/Toolbar";
import Graph from "./toolbar/Graph/Graph";
import Transactions from "./Transactions";
import Axios from "../../../mixins/axios";
import { EventBus } from "../../../index";

export default {
    name: "MainPage",
    mixins: [ Axios ],
    components: { Balance, Toolbar, Graph, Transactions },
    data(){
        return {
            filters: {},
            filterTimer: null
        }
    },

    methods: {
        updateTransactions(){
            clearTimeout(this.filterTimer);
            this.filterTimer = setTimeout(() => {
                EventBus.$emit('transactions:update');
            }, 300);
        }
    }

}
</script>
