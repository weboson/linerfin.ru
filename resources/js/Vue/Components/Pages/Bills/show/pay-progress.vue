<template>
    <div class="pay-progress">
        <div class="pay-progress__bar">
            <div class="pay-progress__bar-content" ref="barContent"></div>
        </div>
        <div class="pay-progress__title">
            <div class="mr-1">Оплачено</div>
            <price-format-ui class="without-currency" :amount="paidSum"/>
            <div class="mx-1">из</div>
            <price-format-ui :amount="billSum"/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        bill: {
            type: Object,
            required: true
        }
    },

    computed: {
        paidSum(){

            if(this.bill.transactions_sum_amount)
                return parseFloat(this.bill?.transactions_sum_amount || 0)


            let transactionAmount = 0;

            if(this.bill.transactions && this.bill.transactions.length) {
                this.bill.transactions.forEach(transaction => {
                    transactionAmount += parseFloat(transaction.amount);
                });
            }
            return transactionAmount;
        },
        billSum(){
            return parseFloat(this.bill?.sum || 0)
        }
    },

    watch: {
        bill(){
            let percent = 0
            if(this.billSum)
                percent = this.paidSum / this.billSum * 100
            this.$refs.barContent.style.width = percent + "%"
        }
    }
}
</script>

<style lang="sass">
.pay-progress

    &__title
        display: flex
        justify-content: flex-end
        font-size: 14px
        margin-top: 5px

    &__bar
        position: relative
        width: 200px
        border-radius: 20px
        height: 10px
        background-color: $c-grey-light
        overflow: hidden

        &-content
            position: absolute
            left: 0
            top: 0
            transition: width 300ms
            background: $c-green
            width: 0
            height: 100%
</style>
