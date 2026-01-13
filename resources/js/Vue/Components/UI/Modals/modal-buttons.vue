<template>
    <footer class="modal-buttons">
        <button class="btn btn-primary" :class="disableSavingClass"
                @click.prevent="$emit('save')">
            {{ buttonSaveText }}
        </button>
        <button class="btn btn-outline-secondary" :class="disabled ? 'disabled' : ''"
                @click.prevent="$emit('cancel')">
            {{ buttonCancelText }}
        </button>
    </footer>
</template>

<script>
export default {
    name: "modal-buttons",
    props: ['saveText', 'cancelText', 'disableSaving', 'disabled'],
    computed: {
        buttonSaveText(){
            return this.saveText || 'Сохранить';
        },
        buttonCancelText(){
            return this.cancelText || 'Отменить';
        },
        disableSavingClass(){
            if(this.disableSaving || this.disabled)
                return 'disabled';

            return '';
        }
    }
}
</script>
