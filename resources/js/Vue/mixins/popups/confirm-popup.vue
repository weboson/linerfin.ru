<template>
    <div class="center-modal__content">
        <div class="confirm-popup-content">
            <div class="confirm-popup__text">
                {{ text || "Подтвердите совершение действия" }}
            </div>

            <footer class="confirm-popup__actions">
                <button class="btn btn-primary" @click="$emit('vuedals:close', { confirm: true })">
                    {{ confirmText || 'Подтвердить '}}
                </button>
                <button class="btn btn-outline-secondary" @click="$emit('vuedals:close', { confirm: false })">
                    {{ cancelText || 'Отмена '}}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>
export default {
    name: "confirm-popup",
    props: {
        text: String,
        confirmText: String,
        cancelText: String
    }
}
</script>
