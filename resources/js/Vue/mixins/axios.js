import qs from 'qs';
import formErrors from '~@vueComponents/UI/Form/form-errors.vue';

export default {
    data(){
        return {
            axiosErrors: {}
        }
    },
    components: {
        formErrors
    },
    methods: {

        axiosPOST(url, postData, options){
            return this.axios('post', url, postData, options);
        },

        axiosGET(url, options){
            return this.axios('get', url, null, options);
        },


        axios(method, url, data, options){

            options = options || {};
            console.debug(`${method} request >`, { url, options });
            this.axiosErrors = [];

            // prepare data
            if(data && !(data instanceof  FormData))
                data = qs.stringify(data);
            else if(!data)
                data = '';

            Object.assign(options, { url, method, data });

            return new Promise((resolve, reject) => {

                axios(options)
                    .then(response => {

                        let data = response.data;
                        console.debug(`${method} response <`, { url, response });

                        if(data.success)
                            resolve(data);

                        else{
                            if(typeof data === 'string')
                                this.debugMsg(data);
                            if(data.errors)
                                this.axiosErrors = data.errors;

                            if(data.msg){

                                if(data.msg === 'Authorize required!')
                                    document.location.reload();

                                this.errorMsg(data.msg);
                            }
                            else
                                this.errorMsg('Произошла ошибка, обратитесь к администратору', 'Упс!');

                            reject(response);
                        }

                    })
                    .catch( r => {
                        if(r.status === 401)
                            document.location.reload();
                        this.debugMsg(r);
                        if(r.data.errors)
                            this.axiosErrors = r.data.errors;

                        reject(r);
                    })
            });
        },


        queryString(object, prefix){
            let str = [],
                p;
            for (p in object) {
                if (object.hasOwnProperty(p)) {
                    let k = prefix ? prefix + "[" + p + "]" : p,
                        v = object[p];

                    if(v !== null && typeof v === "object" && !Array.isArray(v)){
                        str.push(this.queryString(v, k));
                    }
                    else{
                        let value;
                        if(v === true)
                            value = '1';
                        else if(v === null || v === false)
                            value = '';
                        else if(Array.isArray(v)){
                            for(let pp in v)
                                str.push(`${k}[]=` + encodeURIComponent(v[pp]));

                            continue;
                        }
                        else
                            value = encodeURIComponent(v);

                        str.push(k + "=" + value);
                    }
                }
            }
            return str.join("&");
        },


        errorMsg(msg){
            this.$bvToast.toast(msg, {
                title: 'Ошибка',
                autoHideDelay: 10000,
                variant: 'danger',
                toaster: `b-toaster-bottom-right`,
                appendToast: true
            })
        },

        debugMsg(innerHtml){

            let debugBlock = document.querySelector('#debug_msg');

            if(debugBlock)
                debugBlock.remove();

            let console = document.createElement('div');

            console.id = 'debug_msg';
            console.setAttribute('class', 'p-2');
            console.setAttribute('style', 'position: fixed; background: #fff; z-index: 999; font-size: 15px;');
            console.innerHTML = innerHtml;
            console.onclick = function(e){
                this.remove();
            };

            document.body.prepend(console);

            setTimeout(function(){
                document.querySelector('#debug_msg').remove();
            }, 30000)
        }
    }
}
