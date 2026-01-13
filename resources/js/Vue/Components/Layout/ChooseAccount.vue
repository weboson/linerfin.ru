<template>
    <div class="choose-organization" tabindex="0" @blur="choosingAccount = false" :class="choosingAccount ? 'opened' : ''">
        <div class="choose-btn" @click="choosingAccount = !choosingAccount">
            <span>{{ $store.getters.accountName }}</span>
            <b-icon-chevron-down class="icon-right"></b-icon-chevron-down>
        </div>
        <transition name="fade">
            <div class="choose-organization__dropdown" v-if="choosingAccount">
                <a v-for="account in $store.getters.userAccounts" :href="`http://${account.subdomain}.${domain}/`">
                    {{ account.name }}
                </a>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "ChooseAccount",
    data(){
        return {
            choosingAccount: false
        }
    },

    computed: {
        domain(){
            return window?.APPDATA?.DOMAIN || 'linerfin.ru';
        }
    }
}
</script>
