import moment from "moment";

export default {

    methods: {
        getFullName(object, options){
            options = options || {};

            let fullname = [];


            if(options.initials){
                let fullname = "";

                if(object.name)
                    fullname += (object.name.substring(0, 1) + ".");
                if(object.patronymic)
                    fullname += (object.patronymic.substring(0, 1) + ".");
                if(object.surname)
                    fullname += " "+ object.surname;

                return fullname;
            }

            if(object.surname)
                fullname.push(object.surname);
            if(object.name)
                fullname.push(object.name);
            if(object.patronymic)
                fullname.push(object.patronymic);

            return fullname.join(' ');
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


        formatPhone(phone, mask){
            phone = phone.replace(/[^0-9+]/g, '');
            let format = '';

            // /(?:([\d]{1,}?))??(?:([\d]{1,3}?))??(?:([\d]{1,3}?))??(?:([\d]{2}))??([\d]{2})$/;
            let result = phone.match(/([+]?[0-9])([\d]{3})([\d]{3})([\d]{2})([\d]{2,})/);
            if(result){
                if(result[1])
                    format = result[1];
                if(result[2])
                    format += ' (' + result[2] + ') ';
                if(result[3]){
                    if(mask)
                        result[3] = result[3][0] + '**';
                    format += result[3];
                }
                if(result[4]){
                    if(mask)
                        result[4] = '**';
                    format += '-' + result[4];
                }
                if(result[5])
                    format += '-' + result[5];
                if(result[6])
                    format += '-' + result[6];
            }
            return format || phone;
        },


        // return date period as string
        periodString(dateFrom, dateTo, options){

            options = options || {};
            dateTo.setHours(23,59,59,999);

            // check valid
            if(dateFrom.getTime() > dateTo.getTime()){
                console.log('Date period invalid', { dates: {dateFrom, dateTo }})
                return '';
            }

            // detect full month
            const monthStart = moment(dateFrom).startOf('month');
            const monthEnd = moment(dateFrom).endOf('month');

            if(monthStart.valueOf() === dateFrom.getTime() && monthEnd.valueOf() === dateTo.getTime()){
                let returnString = monthStart.format('MMMM YYYY');
                returnString = returnString[0].toUpperCase() + returnString.slice(1);

                if(options?.fullMonthPrefix)
                    returnString = options.fullMonthPrefix + returnString;
                return returnString;
            }

            // return period
            let format = options?.format || 'DD.MM.YYYY';
            let periodString = [];
            periodString.push(moment(dateFrom).format(format));
            periodString.push(moment(dateTo).format(format));

            // if period matches
            if(periodString[0] === periodString[1]) {
                if(options?.useCalendar)
                    return moment(dateFrom).calendar(null,{
                        lastDay : '[Вчера]',
                        sameDay : '[Сегодня]',
                        nextDay : '[Завтра]',
                        nextWeek : 'dddd',
                        sameElse : 'DD.MM.YYYY'
                    });

                return (options?.fullMonthPrefix || '') + periodString[0];
            }

            return periodString.join(' — ');
        }
    }
}
