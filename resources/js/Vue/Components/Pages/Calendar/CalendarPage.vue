<template>
    <div class="top-header-wrapper calendar-namespace">
        <div class="top-header" :class="topHeaderOffset">
            <header class="title">
                <img src="/assets/images/icons/calendar.svg">
                <span>Платежный календарь</span>
            </header>
        </div>

        <div class="container-fluid">
            Раздел в разработке
        </div>
    </div>
</template>

<script>
import PageOffset from "../../../mixins/page-offset";
import Axios from "../../../mixins/axios";

export default {
    mixins: [PageOffset, Axios],
    name: "CalendarPage",
}
</script>
