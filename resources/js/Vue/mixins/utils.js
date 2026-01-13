import moment from "moment";

export default {
    methods: {
        escapeHtml(text) {
            let map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        },


        randomInteger(min, max) {
            let rand = min - 0.5 + Math.random() * (max - min + 1);
            return Math.round(rand);
        },

        clipboardPrompt(text){
            text = text || '';
            prompt('Ctrl+C and Enter', text);
        },

        escapeHtmlDecode(text) {
            let map = {
                '&amp;' : '&',
                '&lt;'  : '<',
                '&gt;'  : '>',
                '&quot;' : '"',
                '&#039;' : "'"
            };

            return text.replace(/(&amp;|&lt;|&gt;|&quot;|&#039;)/g, function(m) { return map[m]; });
        },

        formatDate(date, withTime){
            if(toString.call(date) !== "[object Date]")
                date = new Date(date);
            if(toString.call(date) !== "[object Date]")
                return false;

            let formated = `${("00" + date.getDate()).slice(-2)}.${("00" + (date.getMonth() + 1)).slice(-2)}.${date.getFullYear()}`;

            if(withTime)
                formated += ` ${("00" + date.getHours()).slice(-2)}:${("00" + date.getMinutes()).slice(-2)}`;

            return formated;
        },

        formatPrice(price, currency){
            currency = currency || 'руб.'
            if(typeof price === 'number')
                price = price.toString();
            return price.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        },

        momentDate(data, format){
            format = format || 'DD.MM.YYYY';
            return moment(timestamp).format(format)
        },

        notify(text, title, autoHide, append, variant, position){

            title = title || 'LinerFin';
            autoHide = autoHide || 3000;
            append = append || true;
            variant = variant || 'info';
            position = position || 'bottom-right';

            this.$bvToast.toast(text, {
                title: title,
                autoHideDelay: 5000,
                variant,
                toaster: `b-toaster-${position}`,
                appendToast: append
            })
        },

        successNotify(text, title){
            this.notify(text, title, null, null, 'success');
        },

        errorNotify(text, title){
            this.notify(text, title, null, null, 'danger');
        },

    }
}
