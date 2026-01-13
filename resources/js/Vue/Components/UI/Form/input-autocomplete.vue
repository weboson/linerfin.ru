<!-- example

    <input-autocomplete v-model="name" type="counterparty" @choose="autocomplete"
        value-key="id" :title-key="item => item.data.inn" strict-mod state="hello"/>
-->

<template>
    <div class="input-autocomplete-wrapper"
         ref="inputWrapper"
         :class="autocomplete.length ? 'choosing' : ''">

        <!-- input -->
        <input type="text"
               v-model="searchString" :name="name"
               class="form-control" :class="inputClass"
               :autofocus="inputAutofocus"
               :placeholder="defPlaceholder ? placeholder : ' '" ref="input"
               @input="searchDelay" @focus="searchDelay" @blur="clear">

        <!-- placeholder -->
        <div v-if="placeholder && !defPlaceholder" @click="$refs.input.focus()" class="input-autocomplete__placeholder text-secondary">{{ placeholder }}</div>

        <!-- reset btn -->
        <div v-if="!required && value" class="input-autocomplete__reset-btn" @click="resetValue"><b-icon-x/></div>

        <!-- chevron and loader -->
        <transition-group name="fade">
            <div v-if="loading" class="input-autocomplete__loader" key="0"><b-spinner small variant="primary" label="Spinning"/></div>
            <div v-else-if="chevron" class="chevron" :class="autocomplete.length ? 'opened' : ''" key="1"><b-icon-chevron-down/></div>
        </transition-group>


        <!-- find options -->
        <transition name="fade">
            <div class="autocomplete" v-if="autocomplete.length">
                <div class="autocomplete__item" v-for="item in autocomplete"
                     @click="choose(item)">
                    <div class="item__title">{{ getTitle(item) }}</div>
                    <div class="item__subtitle" v-if="getSubtitle(item)">{{ getSubtitle(item) }}</div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script>
import axios from "../../../mixins/axios";

export default {
    name: "input-autocomplete",
    mixins: [ axios ],

    props: {
        value: {},
        where: Object,
        options: Array,

        // view settings
        valueKey: { default: 'value' },
        titleKey: { default: 'name'},
        subtitleKey: { default: null },
        placeholder: {},
        elClass: { type: String },
        defPlaceholder: Boolean,
        chevron: Boolean,
        disableTimeout: Boolean,

        type: { type: String },

        // only found value
        strictMod: { type: Boolean },
        required: { type: Boolean },
        state: { default: null },

        name: String,
        inputAutofocus: {}
    },

    data(){
        return {
            searchTimer: null,
            searchString: '',
            autocomplete: [],
            loading: false,
        }
    },

    computed: {
        inputClass(){
            let className = this.elClass || '';
            if(this.placeholder && !this.defPlaceholder)
                className += ' with-placeholder';
            return className;
        }
    },

    methods: {
        searchDelay(e){

            this.$refs.inputWrapper.focus()

            let eventType = e?.type || 'keyup';
            this.$emit(eventType, this.searchString);

            // def event
            if(!this.strictMod)
                this.$emit('input', this.searchString);

            // run searching
            clearTimeout(this.searchTimer);

            if(eventType === 'focus')
                this.search(true);
            else if(!this.disableTimeout)
                this.searchTimer = setTimeout(this.search, 500);
            else
                this.search();
        },

        search(fromDB){

            // for static options
            if(!this.type){
                if(this.options)
                    this.autocomplete = this.options.filter(i => {
                        const value = '' + (i[this.valueKey] || '')
                        const title = '' + (i[this.titleKey] || '')
                        return ~value.indexOf(this.searchString) || ~title.indexOf(this.searchString)
                    });

                return;
            }

            let url = `/ui/autocomplete/${this.type}?`;
            let queryParams = {};
            fromDB && (queryParams['from-db'] = '');

            if(!fromDB || (fromDB && !this.chevron))
                queryParams.s = this.searchString;

            // add query conditions
            this.where && Object.assign(queryParams, this.where);

            url += this.queryString(queryParams);

            this.loading = true; // enable loader
            let request = this.axiosGET(url);

            request.then(data => {
                this.loading = false; // dis loader
                if(data.autocomplete) this.autocomplete = Object.values(data.autocomplete);
                this.$emit('search', this.autocomplete);
            });

            request.catch(data => {
                this.loading = false; // dis loader
                console.log('Error of searching', { type: this.type, search: this.searchString, data });
            });
        },

        choose(item){
            item = Object.assign({}, item); // fix bug
            this.$emit('input', this.getValue(item));
            this.$emit('choose', this.state !== null ? { state: this.state, item } : item);
            this.$emit('blur', this.getValue(item));
            this.searchString = this.getTitle(item) || this.getValue(item);
            this.autocomplete = [];
        },


        clear(){

            this.$emit('blur', this.value);
            setTimeout(() => {
                this.autocomplete = [];
            }, 300)
        },

        resetValue(){
            this.autocomplete = [];
            this.searchString = '';
            this.$emit('blur', '');
            this.$emit('input', '');
            this.$emit('choose', {});
        },

        // --- get values ---
        getValue(item){
            if(this.valueKey){
                if(typeof this.valueKey === 'function')
                    return this.valueKey(item);

                if(item[this.valueKey])
                    return item[this.valueKey];
            }
            return null;
        },
        getTitle(item){
            if(this.titleKey){
                if(typeof this.titleKey === 'function')
                    return this.titleKey(item);

                if(item[this.titleKey])
                    return item[this.titleKey];
            }
            return null;
        },
        getSubtitle(item){
            if(this.subtitleKey){
                if(typeof this.subtitleKey === 'function')
                    return this.subtitleKey(item);

                if(item[this.subtitleKey])
                    return item[this.subtitleKey];
            }
            return '';
        },

    },

    watch: {
        value: function(){
            // if(!this.titleKey)
                this.searchString = this.value;
        }
    },

    created(){
        this.searchString = this.value || '';
    }
}
</script>
