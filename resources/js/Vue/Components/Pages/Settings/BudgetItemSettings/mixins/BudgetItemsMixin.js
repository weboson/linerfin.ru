import axios from "~@vueMixins/axios";

export default {
    mixins: [axios],
    methods: {
        getGroupsAndTypes(category){
            return this.axiosGET(`/ui/budget-items/${category}/groups`)
        }
    }
}
