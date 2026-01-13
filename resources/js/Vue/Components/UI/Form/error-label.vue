<template>
    <div class="error-label">
        <div class="error" v-for="error in errorMessage">{{ error }}</div>
    </div>
</template>

<script>
export default {
    name: "error-label",
    props: {
        errors: Object,
        field: String
    },
    computed: {
        errorMessage(){
            if(this.errors.hasOwnProperty(this.field))
                return this.errors[this.field];
            return [];
        }
    }
}
</script>
