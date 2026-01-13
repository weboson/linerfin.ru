<template>
    <div class="input-ui">
        <label>
            <input :type="type" class="form-control" placeholder=" " :required="required" :autofocus="autofocus"
                   v-model="val" :name="name"
                   @keyup="$emit('keyup', val)"
                   @blur="$emit('blur', val)">

            <span class="input-ui__placeholder text-secondary">{{ placeholder }}</span>
            <span class="input-ui__valid text-success">
                <b-icon-check2/>
            </span>
        </label>
        <div v-if="tooltip" class="input-ui__tip text-primary" v-b-tooltip.hover :title="tooltip">
            <b-icon-question-circle/>
        </div>
    </div>
</template>

<script>
export default {
    name: "input-ui",
    props: {
        value: {},
        name: {},
        type: {
            default: 'text'
        },
        required: Boolean,
        tooltip: String,
        placeholder: String,
        autofocus: Boolean
    },
    data(){
        return {
            val: ''
        }
    },
    created() {
        this.val = this.value;
    },
    watch: {
        val: function(){
            this.$emit('input', this.val);
        },
        value: function(){
            this.val = this.value;
        }
    }
}
</script>

<style lang="sass">
    .input-ui

        display: flex
        align-items: center

        label
            display: block
            position: relative
            overflow: hidden
            margin-bottom: 0
            width: 100%

            > span
                -webkit-user-select: none
                -moz-user-select: none
                -ms-user-select: none



        &__placeholder
            display: block
            position: absolute
            left: 13px
            top: 50%
            transform: translateY(-50%)
            white-space: nowrap

            transition-property: top, transform, font-size
            transition-duration: 250ms
            cursor: text


        .form-control
            padding: 18px 12px 4px


        input:focus, input:not(:placeholder-shown)
            + .input-ui__placeholder
                top: 5px
                transform: translateY(0)
                font-size: 12px



        // validation symbol
        &__valid
            display: block
            position: absolute

            top: 50%
            transform: translateY(-50%)

            right: 13px
            opacity: 0

            transition: opacity 250ms


        input[type=email]:valid, input[requred]:valid

            &:not(:placeholder-shown) + span + .input-ui__valid
                opacity: 1




        // help
        &__tip
            margin-left: 8px


</style>
