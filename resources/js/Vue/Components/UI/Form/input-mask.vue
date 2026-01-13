<template>
    <input type="text" v-model="input" ref="field">
</template>

<script>

import Inputmask from 'inputmask';

export default {
    name: "input-mask",
    props: {
        mask: String,
        regex: String,
        value: String
    },
    data(){
        return {
            input: '',
        }
    },
    watch: {
        input: function(){
            this.$emit('input', this.input);
        }
    },
    mounted(){
        this.input = this.value;

        let im = new Inputmask(this.regex ? { regex: this.regex } : this.mask);
        im.mask(this.$refs.field);
    }
}
</script>
