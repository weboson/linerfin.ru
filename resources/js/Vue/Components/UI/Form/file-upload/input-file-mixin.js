import utils from "../../../../mixins/utils";
import axios from "../../../../mixins/axios";


/*
 * @events
 *  input       < give attachment_id
 *  input-uuid  < give attachment_uuid
 *  upload      < ?
 */


export default {
    mixins: [utils, axios],

    // Properties
    props: {

        value: {}, // attachment_id
        attachmentData: {}, // attachment data

        accept: { default: '*/*'}, // input accept attribute

        // Access options
        public: {
            type: Boolean,
            default: false
        },
        accountPublic: {
            // public for this account
            type: Boolean,
            default: false
        },

        maxSize: {
            type: String,
            default: null
        },


        // Allowed extensions
        extensions: {
            type: Array
        },


        // Autoload options
        autoload: {
            type: Boolean,
            default: true
        },


        // callback
        uploadHandler: Function,
        scope: {
            type: Object,
            default: null
        },

        removeBackground: Boolean
    },


    // Component data
    data(){
        return {
            dragAndDropCapable: false, // поддержка Drag&Drop
            file: null,
            attachment: {},
            previewImage: null,

            dragHover: false,
            uploading: false
        }
    },


    // Getters
    computed: {
        fileName(){
            if(this.fileExist){
                if(Object.keys(this.attachment).length)
                    return this.attachment?.name;
                else
                    return this.file.name;
            }
            return '';
        },

        fileExist(){
            if(Object.keys(this.attachment).length)
                return true;
            return this.file && this.file instanceof File;
        }
    },



    // Methods
    methods: {

        // Load handler
        loadHandler(file){
            if(!file)
                [file] = this.$refs.file.files;

            // check file type
            if(!this.acceptFileType(file.type)){
                this.errorNotify('Неверный тип файла');
                return;
            }

            // check file size
            if(this.maxSize && file.size > Number.parseInt(this.maxSize)){
                this.errorNotify('Превышен максимальный размер файла');
                return;
            }

            this.file = file;

            // create preview
            if(this.file){
                this.previewImage = URL.createObjectURL(this.file);
            }

            // Autoload disabled
            if(!this.autoload)
                return;

            // Autoload enabled
            if(this.uploadHandler) // custom handler
                this.uploadHandler(this.file, this.scope);
            else
                this.uploadFile();
        },


        acceptFileType(type){
            if(!this.accept) return true;
            let accept = this.accept.split(',');
            return ~accept.indexOf(type);
        },


        // Upload file (default)
        uploadFile(){

            if(this.uploading){
                console.log('already uploading');
                return;
            }

            if(!this.file){
                console.error('File not found');
                return;
            }

            let formData = new FormData;
            formData.set('attachment', this.file);

            const requestOptions = { headers: { 'Content-Type': 'multipart/form-data' } };

            let url = '/ui/attachments';
            if(this.removeBackground) url += '?remove-background';
            this.uploading = true;
            this.axiosPOST(url, formData, requestOptions).then(data => {
                if(data.attachment_id){
                    this.$emit('input', data.attachment_id);
                    this.$emit('input-uuid', data.uuid);
                }

                this.$emit('upload', data?.attachment);
            }).finally(() => { this.uploading = false; });
        },


        // Check drag&drop support
        determineDragAndDropCapable(){

            let div = document.createElement('div'),
                exist = ( ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div) )
                    && 'FormData' in window
                    && 'FileReader' in window;

            div.remove();

            return exist;
        },


        // Reset file
        resetFile(){
            if(this.attachment?.uuid){
                /*if(!confirm('Вы действительно хотите удалить файл?'))
                    return;

                this.axiosPOST(`/ui/attachments/${this.attachment.uuid}/remove`).then(data => {
                    this.attachment = {};
                    this.$emit('input', null)
                });*/
                this.attachment = {};
                this.$emit('input', null);
            }

            this.$refs.file.value = '';
            this.file = null;
        },


        // dragleave
        onDragleave(e){
            if(e.target === this.$refs.dragwrapper)
                this.dragHover = false;
        }

    },


    // Watchers
    watch: {
        attachment: function(){
            if(this.attachment?.uuid)
                this.previewImage = `/ui/attachments/${this.attachment.uuid}`;
        },

        attachmentData: function(){
            if(this.attachmentData && Object.keys(this.attachmentData).length)
                this.attachment = this.attachmentData;
            else
                this.attachment = {};
        }
    },



    // Callback on mount
    mounted(){

        // Load attachment preview
        if(this.attachmentData){
            this.attachment = this.attachmentData;
        }

        // Drag&Drop functional
        this.dragAndDropCapable = this.determineDragAndDropCapable();
        if(!this.dragAndDropCapable) return;

        // Bind events of drag&drop
        ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function(evt){
            this.$refs.inputFileWrapper.addEventListener(evt, function(e){
                e.preventDefault();
                e.stopPropagation();
            }.bind(this), false);
        }.bind(this));


        // Event of drop
        this.$refs.inputFileWrapper.addEventListener('drop', function(e){
            this.loadHandler(e.dataTransfer.files[0]);
        }.bind(this));

    }
}
