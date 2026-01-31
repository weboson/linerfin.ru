<script>
import InputFileMixin from "./input-file-mixin";

export default {
    name: "input-file",
    mixins: [InputFileMixin]
}
</script>

<template>
    <div class="input-file-wrapper" ref="inputFileWrapper">
        <div class="loader loader__white-bg centered" v-if="uploading">
            <b-spinner variant="primary" label="Загрузка"></b-spinner>
        </div>

        <label>
            <input type="file" @change="loadHandler()" ref="file" :accept="accept">
            <div class="dragdrop" :class="dragHover ? 'bg-grey' : ''"
                 ref="dragwrapper"
                 @dragover="dragHover = true">

                <div class="dragdrop-mask" v-show="dragHover" @dragleave="dragHover = false"
                     @drop="dragHover = false"></div>

                     <!-- Choose file -->
                <template v-if="!fileExist">
                    <slot>
                        <span class="text-primary">Выберите файл</span> или перетащите его сюда
                    </slot>
                </template>

                <!-- Preview -->
                <div v-else-if="previewImage" :style="`background-image: url(${previewImage})`"
                     class="image-preview">

                    <a @click.prevent="resetFile" href="#" class="reset">
                        <b-icon-x/>
                    </a>

                </div>

                <!-- Show filename -->
                <template v-else>
                    <span class="filename">
                        {{ fileName }}
                    </span>
                    <a href="#" @click.prevent="resetFile">
                        <b-icon-x/>
                    </a>
                </template>
            </div>
        </label>
    </div>
</template>

<style lang="sass" scoped>
.input-file-wrapper
    position: relative

.loader
    position: absolute
    top: 0
    left: 0
    right: 0
    bottom: 0
    background: rgba(255, 255, 255, 0.9)
    z-index: 10
    display: flex
    align-items: center
    justify-content: center
    
.dragdrop
    border: 2px dashed #dee2e6
    border-radius: 4px
    padding: 20px
    text-align: center
    cursor: pointer
    transition: all 0.3s ease
    position: relative
    min-height: 150px
    display: flex
    align-items: center
    justify-content: center
    
    &:hover
        border-color: #0662C1
        background-color: rgba(6, 98, 193, 0.05)
    
    &.bg-grey
        background-color: #f8f9fa
    
    .dragdrop-mask
        position: absolute
        top: 0
        left: 0
        right: 0
        bottom: 0
        background: rgba(6, 98, 193, 0.1)
        border: 2px solid #0662C1
        border-radius: 4px
    
    input[type="file"]
        display: none
    
    .filename
        display: inline-block
        max-width: 80%
        overflow: hidden
        text-overflow: ellipsis
        white-space: nowrap
        vertical-align: middle
    
    .image-preview
        width: 100%
        height: 150px
        background-size: contain
        background-position: center
        background-repeat: no-repeat
        background-color: #f8f9fa
        border-radius: 4px
        position: relative
        
        .reset
            position: absolute
            top: 5px
            right: 5px
            background: rgba(0, 0, 0, 0.5)
            color: white
            border-radius: 50%
            width: 24px
            height: 24px
            display: flex
            align-items: center
            justify-content: center
            text-decoration: none
            
            &:hover
                background: rgba(0, 0, 0, 0.7)
</style>