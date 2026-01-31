import utils from "../../../../mixins/utils";
import axios from "../../../../mixins/axios";

export default {
    mixins: [utils, axios],

    props: {
        value: {},
        attachmentData: {},

        accept: { default: '*/*'},

        public: {
            type: Boolean,
            default: false
        },
        accountPublic: {
            type: Boolean,
            default: false
        },

        maxSize: {
            type: String,
            default: null
        },

        extensions: {
            type: Array
        },

        autoload: {
            type: Boolean,
            default: true
        },

        uploadHandler: Function,
        scope: {
            type: Object,
            default: null
        },

        removeBackground: {
            type: Boolean,
            default: true
        }
    },

    data(){
        return {
            dragAndDropCapable: false,
            file: null,
            attachment: {},
            previewImage: null,
            dragHover: false,
            uploading: false
        }
    },

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
        },
        
        isImageFile() {
            if (!this.file) return false;
            const imageTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/bmp', 'image/webp'];
            return imageTypes.includes(this.file.type);
        }
    },

    methods: {
        loadHandler(file){
            if(!file)
                [file] = this.$refs.file.files;

            if(!this.acceptFileType(file.type)){
                this.errorNotify('Неверный тип файла');
                return;
            }

            if(this.maxSize && file.size > Number.parseInt(this.maxSize)){
                this.errorNotify('Превышен максимальный размер файла');
                return;
            }

            this.file = file;

            if(this.file){
                this.previewImage = URL.createObjectURL(this.file);
            }

            if(!this.autoload)
                return;

            if(this.uploadHandler)
                this.uploadHandler(this.file, this.scope);
            else
                this.uploadFile();
        },

        acceptFileType(type){
            if(!this.accept) return true;
            let accept = this.accept.split(',');
            return ~accept.indexOf(type);
        },

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

            const requestOptions = { 
                headers: { 'Content-Type': 'multipart/form-data' } 
            };

            let url = '/ui/attachments';
            
            // Добавляем параметр ТОЛЬКО если removeBackground = true
            if (this.removeBackground && this.isImageFile) {
                url += '?remove-background=true';
            }
            
            this.uploading = true;
            
            this.axiosPOST(url, formData, requestOptions)
                .then(data => {
                    if(data.attachment_id){
                        this.$emit('input', data.attachment_id);
                        this.$emit('input-uuid', data.uuid);
                    }

                    this.$emit('upload', data?.attachment);
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    this.errorNotify('Ошибка при загрузке файла');
                })
                .finally(() => { 
                    this.uploading = false; 
                });
        },

        determineDragAndDropCapable(){
            let div = document.createElement('div'),
                exist = ( ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div) )
                    && 'FormData' in window
                    && 'FileReader' in window;

            div.remove();

            return exist;
        },

        resetFile(){
            if(this.attachment?.uuid){
                this.attachment = {};
                this.$emit('input', null);
            }

            this.$refs.file.value = '';
            this.file = null;
            this.previewImage = null;
        },

        onDragleave(e){
            if(e.target === this.$refs.dragwrapper)
                this.dragHover = false;
        }
    },

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

    mounted(){
        if(this.attachmentData){
            this.attachment = $this.attachmentData;
        }

        this.dragAndDropCapable = this.determineDragAndDropCapable();
        if(!this.dragAndDropCapable) return;

        ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function(evt){
            this.$refs.inputFileWrapper.addEventListener(evt, function(e){
                e.preventDefault();
                e.stopPropagation();
            }.bind(this), false);
        }.bind(this));

        this.$refs.inputFileWrapper.addEventListener('drop', function(e){
            this.loadHandler(e.dataTransfer.files[0]);
        }.bind(this));
    }
}