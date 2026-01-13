<template>
    <div class="select-ui" :class="(showOptions ? 'opened' : '') + (required ? ' required' : '')"
         tabindex="0" @blur="hideOptions">

        <input type="hidden" :name="name" v-model="value" :required="!!required">

        <div class="currentValue" @click="toggleOptions" :class="elementClass">
            <div>{{ valueText }}</div>

            <div class="placeholder" v-if="placeholder && !valueText">
                {{ placeholder }}
            </div>

            <div class="right-title" v-if="selectedRightTitle !== null">
                <price-format-ui v-if="rightTitleType === 'price'" :amount="selectedRightTitle"/>
                <span v-else>{{ selectedRightTitle }}</span>
            </div>
        </div>

        <div class="icon">
            <a @click.prevent="clear"
               v-if="!this.required && this.value"
               href="#" class="clear"><b-icon-x class="clear"/></a>
            <b-icon-chevron-down @click="toggleOptions" class="chevron"/>
        </div>

        <transition name="fade">
            <div class="options" v-if="showOptions">
                <div class="option" v-for="option in options"
                     :value="optionValue(option)"
                     @click="chooseValue(option)">
                    <div class="option__title">{{ optionTitle(option) }}</div>
                    <div class="option__subtitle" v-if="subtitleKey">
                        {{ optionSubtitle(option) }}
                    </div>
                    <div class="option__right-title" v-if="rightTitleKey">
                        <price-format-ui v-if="rightTitleType === 'price'" :amount="optionRightTitle(option)"/>
                        <span v-else>{{ optionRightTitle(option) }}</span>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>

export default {
    name: "select-ui",
    props: {
        value: [String, Number, Boolean], // Main value
        options: Array, // select's options

        // Field's keys
        valueKey: { default: 'id'},
        titleKey: { default: 'name'},
        subtitleKey: { default: null },
        rightTitleKey: { default: null },
        rightTitleType: { type: String, default: null },

        placeholder: String, // placeholder
        elClass: String,     // input-class
        required: Boolean,   // selection required

        name: String
    },
    computed: {
        valueText(){
            if(!this.value || !this.options)
                return '';

            for(let option of this.options){

                // for single-option
                if(typeof option === 'string' || typeof option === 'number'){
                    if(option.toString() === this.value.toString())
                        return option;
                }
                else if(option[this.valueKey].toString() === this.value.toString()){
                    return option[this.titleKey];
                }
            }

            return '';
        },
        elementClass(){
            return this.elClass;
        },

        // return selected option (maybe object)
        selectedOption(){
            let value = this.value,
                vKey = this.valueKey,
                options = this.options;

            if(value === null || !options || !vKey) return;
            if(vKey === 'id') value = parseInt(value);

            return options.find(o => o[vKey] === value);
        },


        selectedRightTitle(){
            let option = this.selectedOption,
                rightKey = this.rightTitleKey;

            if(option && rightKey && option[rightKey] !== undefined)
                return option[rightKey];

            return null;
        }
    },
    data(){
        return {
            showOptions: false,
        }
    },
    methods: {
        toggleOptions(e){
            this.showOptions = !this.showOptions;
        },

        hideOptions(e){
            this.showOptions = false;
        },

        chooseValue(option){

            // for single-option
            if(typeof option === 'string' || typeof option === 'number'){
                this.$emit('input', option);
                this.$emit('change', option);
                this.showOptions = false;

                return;
            }

            // object-option
            if(!option || option[this.valueKey] === undefined)
                return;

            this.$emit('input', "" + option[this.valueKey]);
            this.$emit('change', "" + option[this.valueKey]);
            this.showOptions = false;
        },

        optionValue(option){
            if(option) {
                if(typeof this.valueKey === 'function')
                    return this.valueKey(option);

                if(typeof option === 'object')
                    return option[this.valueKey] || '';
            }

            if(typeof option === 'string' || typeof option === 'number')
                return option;

            return option[this.valueKey];
        },

        optionTitle(option){

            if(option) {
                if(typeof this.titleKey === 'function')
                    return this.titleKey(option);

                if(typeof option === 'object')
                    return option[this.titleKey] || '';
            }

            if(typeof option === 'string' || typeof option === 'number')
                return option;

            return option[this.titleKey];
        },

        optionSubtitle(option){
            if(option) {
                if(typeof this.subtitleKey === 'function')
                    return this.subtitleKey(option);

                if(typeof option === 'object')
                    return option[this.subtitleKey] || '';
            }

            return '';
        },

        optionRightTitle(option){
            if(option) {
                if(typeof this.rightTitleKey === 'function')
                    return this.rightTitleKey(option);

                if(typeof option === 'object')
                    return option[this.rightTitleKey] || '';
            }

            return '';
        },

        clear(){
            this.$emit('input', null);
            this.showOptions = false;
        }
    },

    created(){
        this.valueKey = this.valueKey || this.valueKey;
        this.titleKey = this.titleKey || this.titleKey;

        if(this.required === true && !this.value){
            let firstOption = this.options[0];
            if(!firstOption){
                console.error('Undefined first option in select-ui');
            }
            else{
                this.$emit('input', this.optionValue(firstOption));
            }

        }
    }
}
</script>

