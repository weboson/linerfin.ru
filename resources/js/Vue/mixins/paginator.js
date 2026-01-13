export default {
    data(){
        return {
            perPageCount: 5,
            per_page_counts: [5, 10, 25, 40, 75, 100],

            morePages: false,

            page: 1,
            lastPage: 1,
        }
    },

    computed: {
        pages(){
            let pages = [];

            for(let i = 1; i <= this.lastPage; i++){
                pages.push(i);
            }

            return pages;
        },
    },

    methods: {

        choosePage(num){
            this.page = num;
        },

        // количество на одной странице
        choosePPC(ppc){
            this.perPageCount = ppc;
            this.page = 1;
        }
    }
}
