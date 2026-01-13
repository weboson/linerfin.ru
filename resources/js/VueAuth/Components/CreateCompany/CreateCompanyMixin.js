export default {

    methods: {
        old(property, def){
            def = def || '';
            if(window.oldFormData && window.oldFormData[property])
                return window.oldFormData[property];

            return null;
        },

        error(property){
            if(this.errors[property]){
                if(typeof this.errors[property] === 'object')
                    return this.errors[property].join(`<br>`);
                return this.errors[property];
            }

            return null;
        },

        setError(property, error){
            let errors = Object.assign({}, this.errors);
            errors[property] = error;
            this.errors = errors;
        },

        getOlds(){
            const properties = [
                'company_name', 'company_type', 'company_subdomain', 'company_inn', 'company_kpp',
                'company_address', 'company_legal_address', 'checking_account', 'bank_bik',
                'bank_name', 'bank_correspondent', 'bank_swift', 'bank_inn', 'bank_kpp'];

            properties.forEach(value => {
                if(!!this.old(value))
                    this[value] = this.old(value);
            })
        },
    },

}
