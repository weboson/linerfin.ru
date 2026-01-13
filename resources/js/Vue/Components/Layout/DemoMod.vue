<template>
    <section class="demo-mod">

        <transition name="fade">
            <div v-if="showPopup" class="demo-mod__popup-wrapper">
                <div class="demo-mod__popup">
                    <a @click.prevent="togglePopup" class="close">
                        <b-icon-x/>
                    </a>
                    <h3>
                        Добро пожаловать в LinerFin!
                    </h3>

                    <p>
                        Вы находитесь в <b>демо-режиме</b> и можете попробовать возможности LinerFin перед началом работы.
                    </p>

                    <!--                <p class="small text-secondary">
                                        Для завершения демо-режима нажмите на кнопку
                                        <span style="white-space: nowrap">Завершить демо-режим</span>
                                    </p>-->

                    <!--                <footer class="toolbar mb-0 mt-3">
                                        <div class="left">
                                            <a href="#" class="btn btn-link text-secondary p-0 small">Завершить демо-режим</a>
                                        </div>
                                        <div class="right">
                                            <a href="#" class="btn btn-primary">Перейти в демо-режим</a>
                                        </div>
                                    </footer>-->
                    <footer class="mt-3">
                        <a @click.prevent="togglePopup" href="#" class="btn btn-primary">Перейти в демо-режим</a> <br>
                        <a :href="offDemoModLink" class="btn btn-link text-secondary small p-0 mt-2">Завершить демо-режим</a>
                    </footer>
                </div>
            </div>

            <div v-else class="demo-mod__bar">
                <div class="container">
                    <div class="demo-mod__bar-content">
                        <div class="text">
                            <div class="title">Вы находитесь в демо-режиме</div>
                            <a @click.prevent="togglePopup" href="#">Подробнее</a>
                        </div>
                        <div class="action">
                            <a :href="offDemoModLink" class="btn btn-sm btn-primary">
                                <b-icon-check-circle class="icon-left mr-2" style="font-size: 18px"/>
                                <span>Завершить демо-режим</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </section>
</template>

<script>
export default {
    name: "DemoMod",
    data(){
        return {
            showPopup: true,
        }
    },

    methods: {
        togglePopup(){
            this.showPopup = !this.showPopup;
            if(this.showPopup)
                localStorage.removeItem('linerfinDemoModPopupClosed');
            else
                localStorage.setItem('linerfinDemoModPopupClosed', '1');
        }
    },

    computed: {
        domain(){
            return window?.APPDATA?.DOMAIN || 'linerfin.ru';
        },

        offDemoModLink(){
            return `http://my.${this.domain}/new-company`;
        }
    },

    created(){
        if(localStorage.getItem('linerfinDemoModPopupClosed'))
            this.showPopup = false;
    }
}
</script>

<style lang="sass">

    // Fix vuedals overlap
    /*.wrapper.wrapper__demo-mod{
            .vuedals{
                height: calc(100% - 95px);
            }
        }*/

    // Demo mod label
    section.demo-mod

        .demo-mod__bar
            position: fixed
            bottom: 0
            left: 0
            width: 100%
            background: #fff
            box-shadow: 0px -2px 12px 1px rgba(0,0,0,.05)
            padding: 20px
            z-index: 1049

            &-content
                display: flex
                justify-content: space-between
                align-items: center


            .text
                .title
                    font-weight: bold
                    font-size: 20px




        .demo-mod__popup-wrapper
            position: fixed
            top: 0
            left: 0
            width: 100%
            height: 100%
            background-color: rgba(0,0,0,.6)
            z-index: 100001

            .demo-mod__popup
                position: absolute
                top: 50%
                left: 50%
                transform: translate(-50%, -50%)

                width: 600px
                max-width: 100%

                background: #fff
                padding: 40px 30px 30px
                border-radius: 3px

                > .close
                    position: absolute
                    top: 20px
                    right: 20px
                    opacity: .6


                h3
                    font-weight: bold
                    margin-bottom: 20px


                p
                    font-size: 17px


                .small
                    font-size: 14px


                footer.toolbar
                    align-items: center

</style>
