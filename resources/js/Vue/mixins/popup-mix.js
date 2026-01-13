import ConfirmPopup from './popups/confirm-popup';
import PromptPopup from './popups/prompt-popup';

export default {
    methods: {

        // Confirm popup
        confirmPopup(text, data){
            let props = { text };
            if(data && typeof data === 'object')
                props = Object.assign(props, data);


            return new Promise((resolve, reject) => {
                this.$emit('vuedals:new', {
                    name: 'center-modal',
                    dismissable: false,
                    escapable: true,
                    component: ConfirmPopup,
                    props: props,

                    onClose: (data) => {
                        if(data.confirm)
                            resolve(data);
                        else
                            reject();
                    },

                    onDismiss() {
                        reject()
                    }
                });
            });
        },

        promptPopup(text, def, data){
            let props = { text, default: def };
            if(data && typeof data === 'object')
                props = Object.assign(props, data);

            return new Promise((resolve, reject) => {
                this.$emit('vuedals:new', {
                    name: 'center-modal',
                    dismissable: false,
                    escapable: true,
                    component: PromptPopup,
                    props: props,

                    onClose: (data) => {
                        if(data.confirm)
                            resolve(data);
                        else
                            reject(data);
                    },

                    onDismiss() {
                        reject()
                    }
                });
            });
        }
    }
}
