<template>
    <div class="center-modal__content">
        <div class="prompt-popup-content">
            <div class="prompt-popup__text">
                {{ text || "Введите значение" }}
            </div>

            <form @submit.prevent="commit">

                <div class="form-group">
                    <input :type="inputType" v-model="value" class="form-control">
                </div>

                <footer class="prompt-popup__actions">
                    <button class="btn btn-primary" type="submit">
                        {{ confirmText || 'Подтвердить '}}
                    </button>
                    <button class="btn btn-outline-secondary"
                            @click.prevent="cancel">
                        {{ cancelText || 'Отмена '}}
                    </button>
                </footer>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: "confirm-popup",
    props: {
        text: String,
        confirmText: String,
        cancelText: String,
        default: [String, Number],
        inputType: {
            type: String,
            default: 'text'
        },

        validator: Function
    },
    data(){
        return {
            value: ''
        }
    },

    methods: {

        commit(){
            if(this.validator){
                if(!this.validator(this.value))
                    return;
            }
            this.$emit('vuedals:close', { confirm: true, value: this.value });
        },


        cancel(){
            this.$emit('vuedals:close', { confirm: false })
        }

    },

    created(){
        if(this.default !== null && this.default !== undefined)
            this.value = this.default;
    }

}
</script>
