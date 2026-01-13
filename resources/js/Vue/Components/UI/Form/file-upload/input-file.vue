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
