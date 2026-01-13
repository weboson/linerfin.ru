<template>
    <div class="user-profile"  tabindex="0"
         :class="accountMenu ? 'opened' : ''">
        <div @click="accountMenu = !accountMenu" class="username">{{ userFullName }}</div>
        <div @click="accountMenu = !accountMenu" class="subtitle">
            <span>
                Профиль
            </span>
            <b-icon-chevron-down class="icon-right"></b-icon-chevron-down>
        </div>
        <transition name="fade">
            <div class="user-profile__menu" v-if="accountMenu">
                <router-link to="/settings">
                    <span>Настройки</span>
                </router-link>

<!--                <label>
                    <b-form-checkbox v-model="dark" name="dark-mod" switch>
                        <span>Темный режим</span>
                    </b-form-checkbox>
                </label>-->

                <a href="/logout">
                    <span>Выйти</span>
                </a>
            </div>
        </transition>
    </div>
</template>

<script>
import Formats from '../../mixins/formats';

export default {
    name: "ProfileButton",
    mixins: [ Formats ],
    props: {
       darkView: Boolean
    },
    data(){
        return {
            accountMenu: false,
            dark: false
        }
    },

    computed: {
        userFullName(){
            let user = this.$store.state.user;
            if(!user)
                return '';

            return this.getFullName(user, { initials: true });
        }
    },

    watch: {
        darkView(){
            this.dark = !!this.darkView;
        },

        dark(){
            this.$emit('dark-mod', this.dark);
        }
    },

    created(){
        this.dark = this.darkView;
    }
}
</script>
