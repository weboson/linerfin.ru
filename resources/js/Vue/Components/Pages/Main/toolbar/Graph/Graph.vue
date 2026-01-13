<template>
    <div class="transactions__graph graph" :class="hide ? '' : 'opened'">
        <transition name="slide">
            <div class="graph__wrapper loader-wrapper" v-if="!hide">
                <header class="graph__header">
                    <div class="graph__toggle-type">
                        <button @click="changeShowMod(false)" :class="!showMod ? 'enabled': ''"><b-icon-graph-up/></button>
                        <button @click="changeShowMod(true)" :class="showMod ? 'enabled': ''"><b-icon-pie-chart/></button>
                    </div>
                    <div class="title">Движение денежных средств {{ filterPeriod }}</div>
                    <div class="graph__legend" v-if="!showMod">
                        <div class="legend__balance">Факт</div>
                        <div class="legend__plan">План</div>
                        <div class="legend__future">Прогноз</div>
                    </div>
                    <div class="graph__category" v-else>
                        <div class="btn btn-secondary" @click="showDropdown = !showDropdown">
                            <b-icon-gear/>
                            <span class="ml-1">Настройка</span>
                        </div>
                        <transition name="fade">
                            <div class="graph-category__dropdown" v-if="showDropdown">
                                <b-form-radio v-for="(title, value) in categories" v-model="category" name="pieCategory" :value="value" :key="value">{{ title }}</b-form-radio>
                                <div class="graph__pie-types" v-if="category !== 'bills'">
                                    <hr>
                                    <b-form-radio v-for="(title, value) in pieTypes" v-model="pieType" name="pieType" :value="value" :key="value">{{ title }}</b-form-radio>
                                </div>
                            </div>
                        </transition>
                    </div>
                </header>

                <transition name="fade">
                    <graph-line v-if="!showMod" :filters="filters"></graph-line>
                    <graph-pie v-else :filters="filters" :type="pieType" :category="category"></graph-pie>
                </transition>
            </div>
        </transition>
        <footer>
            <a href="#" @click.prevent="toggleShow">
                <b-icon-chevron-down class="icon-left"></b-icon-chevron-down>
                <span v-if="hide">Показать график</span>
                <span v-else>Скрыть график</span>
            </a>
        </footer>
    </div>
</template>

<script>
import Formats from '../../../../../mixins/formats';

// components
import GraphLine from './GraphLine';
import GraphPie from './GraphPie';

export default {
    name: "Graph",
    mixins: [Formats],
    components: { GraphLine, GraphPie },

    props: {
        filters: Object
    },

    data(){
        return {
            hide: false,
            showMod: false,
            category: 'budget-items',
            categories: {
                'budget-items': 'По статьям',
                projects: 'По проектам',
                bills: 'По счетам'
            },
            pieType: 'income',
            pieTypes: {
                income: 'Приход',
                expense: 'Расход'
            },

            showDropdown: false,
        }
    },

    computed: {
        filterPeriod(){

            let period = this.filters?.filterPeriod;
            if(!period || !period[0] || !period[1] )
                return '';

            return this.periodString(period[0], period[1], {
                fullMonthPrefix: 'за '
            });
        }
    },

    methods: {
        toggleShow(){
            this.hide = !this.hide;
            if(this.hide)
                localStorage.setItem('linerfinHideGraph', '1');
            else
                localStorage.removeItem('linerfinHideGraph');
        },
        changeShowMod(mod){
            this.showMod = !!mod;
            if(this.showMod)
                localStorage.setItem('linerfinShowModGraph', '1');
            else
                localStorage.removeItem('linerfinShowModGraph');
        }
    },

    created(){
        this.hide = !!localStorage.getItem('linerfinHideGraph');
        this.showMod = !!localStorage.getItem('linerfinShowModGraph');
    }
}
</script>
