<template>
    <section id="sidebar" :class="minify ? 'minify' : ''">
        <a href="#" class="sidebar-minify" @click.prevent="toggleSidebarMinify">
            <img src="/assets/images/icons/arrow-left.svg">
            <span>Скрыть</span>
        </a>
        <nav>
            <ul>
                <li>
                    <router-link :to="{name: 'main-page'}" class="nav-link" active-class="active" exact>
                        <img :src="iconPath + 'home.svg'">
                        <span>Главная</span>
                    </router-link>
                </li>
<!--                <li>
                    <router-link to="/calendar" class="nav-link" active-class="active">
                        <img src="/assets/images/icons/calendar.svg">
                        <span>Календарь</span>
                    </router-link>
                </li>-->
                <li>
                    <router-link :to="{name: 'counterparties'}" class="nav-link" active-class="active">
                        <img :src="iconPath + 'contractors.svg'">
                        <span>Контрагенты</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/bills" class="nav-link" active-class="active">
                        <img :src="iconPath + 'bill.svg'">
                        <span>Счета</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/reports" class="nav-link" active-class="active">
                        <img :src="iconPath + 'reports.svg'">
                        <span>Отчеты</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/docs" class="nav-link" active-class="active">
                        <img :src="iconPath + 'doc.svg'">
                        <span>Документы</span>
                    </router-link>
                </li>
                <li>
                    <router-link :to="{name: 'settings'}" class="nav-link" active-class="active">
                        <img :src="iconPath + 'settings.svg'">
                        <span>Настройки</span>
                    </router-link>
                </li>
            </ul>
        </nav>
    </section>
</template>

<script>
import SelectUI from "../UI/Form/select-ui";

export default {
    name: "SidebarComponent",
    components: { 'select-ui': SelectUI },

    props: {
        dark: Boolean,
    },

    data(){
        return {
            minify: false
        }
    },

    methods: {
        toggleSidebarMinify(){
            this.minify = !this.minify;
            if(!this.minify)
                localStorage.removeItem('minify-sidebar');
            else
                localStorage.setItem('minify-sidebar', '1');
        }
    },

    computed: {
        iconPath(){
            return this.dark ? '/assets/images/dark/icons/' : '/assets/images/icons/';
        }
    },


    created(){
        this.minify = !!localStorage.getItem('minify-sidebar')
    }
}
</script>
