<script>
import utils from "../../../mixins/utils";

export default {
    name: "edit-final",
    mixins: [utils],
    props: {
        data: Object
    },

    computed: {
        finalTitle(){
            let d = this.data;
            if(d?.payerNotify && (d?.payerPhone || d.payerEmail))
                return 'Счёт отправлен';

            return "Счёт выставлен";
        },

        counterparty(){
            let d = this.data;
            if(d?.counterparty){
                return this.$store.state.counterparties.find(i => i.id === d.counterparty);
            }

            return {};
        }
    }
}
</script>


<template>
    <div class="text-center">

        <div class="mb-3 image">
            <img src="/assets/images/bill-successes.svg">
        </div>

        <header class="h4">
            {{ finalTitle }}
        </header>

        <div v-if="data.num" class="number">
            <span class="text-secondary">
                Номер счёта
            </span>
            {{ "№" + data.num }}
        </div>

        <div class="payer" v-if="counterparty">
            <span class="text-secondary">Кому</span>
            {{ counterparty.name }}
        </div>

        <div class="payer-phone" v-if="data.payerPhone">
            <span class="text-secondary">Телефон</span>
            {{ data.payerPhone }}
        </div>

        <div class="payer-email" v-if="data.payerEmail">
            <span class="text-secondary">E-mail</span>
            {{ data.payerEmail }}
        </div>

        <div class="pay-before" v-if="data.payBefore">
            <span class="text-secondary">Оплатить до</span>
            {{ formatDate(data.payBefore) }}
        </div>

        <div class="comment">
            <div class="text-secondary">Комментарий</div>
            {{ data.comment || '-' }}
        </div>

        <footer class="mt-4 text-center">
            <router-link class="btn btn-primary" :to="'/bills/' + data.id || ''" exact>
                Открыть счёт
            </router-link>

            <br>

            <router-link class="btn btn-link btn-sm" to="/bills" exact>
                Вернуться к списку счетов
            </router-link>
        </footer>

    </div>
</template>
