import axios from "../../../mixins/axios";

export default {
    mixins: [ axios ],
    methods: {



        // Remove selected bills
        removeBills(ids){

            ids = ids || this.selected;
            if(!ids || !ids.length)
                return;

            if(!confirm('Удалить выбранные счета?'))
                return;

            this.axiosPOST('/ui/bills/delete', {
                ids: ids.join(',')
            }).then(r => {
                this.loading = false;
                this.selected = [];
                this.getBills();
            })
        },


        // Edit selected bill
        editBill(bill){

            let bill_id;

            if(bill && bill.id)
                bill_id = bill.id;

            else if(this?.selected.length && this.selected.length === 1)
                bill_id = this.selected[0];

            else return;

            let link = bill?.status === 'template' ? '/bills/templates/' + bill_id + '/edit' : '/bills/' + bill_id + '/edit';

            this.$router.push(link);
        },

        // Open bill
        openBill(bill){
            if(bill.status === 'template')
                this.$router.push('/bills/templates/' + bill.id + "/use")
            else if(bill.status === 'draft')
                this.$router.push('/bills/' + bill.id + '/edit');
            else
                this.$router.push('/bills/' + bill.id);
        },

        // Get template short preview
        getTemplatePreview(template, defStr){
            defStr = defStr || '-';
            if(!template || !template.num)
                return defStr;

            const previewArray = template.num.split(' ');
            let preview = [];
            previewArray.map(value => {
                preview.push(value.slice(0, 1));
            });

            return preview.join('') || defStr;
        },
    }
}
