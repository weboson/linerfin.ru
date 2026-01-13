export default {
    data(){
        return {
            pageOffset: 0
        }
    },
    computed: {
        topHeaderOffset(){
            if(this.pageOffset)
                return 'with-offset';
            return '';
        }
    },
    methods: {
        scanPageOffset(){
            this.pageOffset = window.pageYOffset;
        },
    },
    created(){
        window.addEventListener('scroll', this.scanPageOffset);
    },
    destroyed() {
        window.removeEventListener('scroll', this.scanPageOffset);
    }
}
