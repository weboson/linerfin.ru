<template>
    <div class="price-format" :class="priceClass">
        <span class="num">
            <span v-if="showPlus && amount > 0">+</span>{{ mainAmount }}
        </span>
        <div class="penny">
            {{ pennyAmount }}
        </div>
    </div>
</template>

<script>
export default {
    name: "price-format-ui",
    props: {
        amount: {
            type: Number,
            default: 0
        },
        showPlus: Boolean,
        defineClass: Boolean
    },

    computed: {
        mainAmount(){
            let amount = Number.parseInt(this.amount);
            amount = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");

            if(!amount) return "0";

            return amount;
        },

        pennyAmount(){
            let penny = this.amount % 1;
            if(!penny) return '00';
            penny = ~~(penny * 100);

            return ("00" + penny).slice(-2);
        },

        priceClass(){
            if(!this.defineClass) return '';
            if(this.amount > 0)
                return 'income';
            if(this.amount < 0)
                return 'expense';

            return '';
        }
    }
}
</script>
