<template>
    <div class="top-header-wrapper">
        <div class="top-header" :class="topHeaderOffset">
            <header class="title">
                <img src="/assets/images/icons/reports.svg">
                <span>Отчёты</span>
            </header>
        </div>



        <div class="container-fluid">

<!--            <div class="toolbar">
                <div class="left">
                    <a @click.prevent="createPopup"
                       href="#" class="btn btn-primary">
                        <b-icon-person-plus></b-icon-person-plus>
                        Добавить контрагента
                    </a>
                </div>
                <div class="right">
                    <a @click.prevent
                       href="#" class="btn btn-outline-secondary disabled">
                        <b-icon-download class="icon-left"/>
                        Импорт
                    </a>
                    <a @click.prevent
                       href="#" class="btn btn-outline-secondary disabled">
                        <b-icon-upload class="icon-left"/>
                        Экспорт
                    </a>
                </div>
            </div>-->

            <div class="toolbar">
                <div class="left">
                    <router-link class="btn btn-secondary text-left" to="/reports/flow-of-funds">
                        <div class="title h5">
                            Отчёт ДДС
                        </div>
                        <div class="desc text-muted">
                            Движение денежных средств
                        </div>
                    </router-link>

                    <router-link class="btn btn-secondary text-left" to="/reports/pnl">
                        <div class="title h5">
                            Отчет о прибыли и убытках
                        </div>
                        <div class="desc text-muted">
                            P&L
                        </div>
                    </router-link>


                    <a href="#" class="btn btn-secondary text-left disabled">
                        <div class="title h5">
                            Баланс
                        </div>
                        <div class="desc text-muted">

                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import PageOffsetMixin from '../../../mixins/page-offset';
export default {
    name: "Reports",
    mixins: [PageOffsetMixin]
}
</script>

<style scoped>

</style>
