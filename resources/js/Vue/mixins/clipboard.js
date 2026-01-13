export default {
    methods: {
        fallbackCopyToClipboard(text) {
            let textArea = document.createElement("textarea");
            textArea.value = text;

            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                let successful = document.execCommand('copy'),
                    msg = successful ? 'successful' : 'unsuccessful';
                // console.log('Fallback: Copying text command was ' + msg);
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }

            document.body.removeChild(textArea);
        },

        copyToClipboard(text) {
            return new Promise((resolve, reject) => {
                if (!navigator.clipboard) {
                    this.fallbackCopyToClipboard(text);
                    resolve();
                }
                navigator.clipboard.writeText(text).then(resolve, reject);
            })
        }
    }
}
