<script>
import axios from "../../../../mixins/axios";
export default {
    name: "balance-correcting",
    mixins: [ axios ],
    props: {
        id: {},
        old_balance: {}
    },

    data(){
        return {
            balance: 0
        }
    },

    methods: {
        save(){
            const url = `/ui/banks/${this.id}/set-balance`;
            const data = { balance: this.balance };
            this.axiosPOST(url, data).then(r => {
                this.$emit('vuedals:close', r);
            });
        }
    },

    created(){
        if(this.old_balance)
            this.balance = this.old_balance;
    }
}
</script>


<template>
    <div class="center-modal__content">
        <header>Корректировка баланса</header>
        <form @submit.prevent="save">
            <div class="form-group">
                <input v-model="balance" type="text" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
            <button @click.prevent="$emit('vuedals:close')" class="btn btn-outline-secondary btn-sm">Отмена</button>
        </form>
    </div>
</template>
