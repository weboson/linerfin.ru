<template>
    <section class="top-header-wrapper settings-page">
        <div class="top-header with-nav" :class="topHeaderOffset">
<!--            <div class="breadcrumbs-wrapper">
                <a href="#" class="back-link">
                    <b-icon-arrow-left class="icon-left"/>
                    Назад
                </a>
                <ul class="breadcrumbs">
                   <li>Тарифы</li>
                   <li>Оплата</li>
                </ul>
            </div>-->
            <header class="title">
                Настройки
            </header>

            <footer>
                <nav>
                    <router-link to="/settings" exact>Аккаунт</router-link>
                    <router-link to="/settings/companies">Мои компании</router-link>
                    <router-link to="/settings/banks">Мои банки</router-link>
<!--                    <router-link to="/settings/users">Пользователи</router-link>-->
                    <router-link to="/settings/projects">Проекты</router-link>
                    <router-link to="/settings/budget-items">Статьи бюджета</router-link>
                </nav>
                <div class="logout-wrapper">
                    <a href="/sayonara" class="logout">
                        <b-icon-box-arrow-in-right class="icon-left"/>
                        Выход
                    </a>
                </div>
            </footer>
        </div>

        <router-view></router-view>

    </section>
</template>

<script>
import PageOffsetMixin from '../../../mixins/page-offset';

export default {
    name: "SettingsMain",
    mixins: [ PageOffsetMixin ]
}
</script>
