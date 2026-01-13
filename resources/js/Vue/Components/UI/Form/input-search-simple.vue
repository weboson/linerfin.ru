<template>
    <div class="input-search-wrapper">
        <div class="prepend" :class="elClass"> <b-icon-search/> </div>
        <input type="text" v-model="search" placeholder="Поиск"
               :class="elClass" @keyup="$emit('keyup')">
    </div>
</template>

<script>
export default {
    name: "input-search-simple",
    props: ['value', 'el-class'],
    data(){
        return {
            search: ''
        }
    },
    watch: {
        search: function(){
            this.$emit('input', this.search);
        }
    },

    created() {
        this.search = this.value;
    }
}
</script>
