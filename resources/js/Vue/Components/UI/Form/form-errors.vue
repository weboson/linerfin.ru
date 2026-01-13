<template>
    <ul class="form-errors" v-if="formErrors">
       <li v-for="error in formErrors" class="text-danger">{{ error }}</li>
    </ul>
</template>

<script>
export default {
    name: "form-errors",
    props: {
        errors: { type: Object, default: () => {} },
        name: String
    },

    computed: {
        formErrors(){
            return this.errors[this.name] || [];
        }
    }
}
</script>

<style lang="sass">
    .form-errors
        list-style: none
        padding: 0
        margin: 5px 0 10px 0
        font-size: 14px
</style>
