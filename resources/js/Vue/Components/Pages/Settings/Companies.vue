<script>

import CompanyPopup from './company-popup';
import axios from "../../../mixins/axios";
import utils from "../../../mixins/utils";
import { EventBus } from "../../../index";

export default {
    name: "Companies",
    mixins: [ utils, axios ],
    computed: {
        companies(){
            return this.$store.getters.userAccounts || [];
        }
    },

    methods: {
        getCompanyLink(company){
            if(!company || !company.subdomain)
                return null;
            let domain = window.APPDATA?.DOMAIN || 'linerfin.ru';
            return 'http://' + company.subdomain + '.' + domain + '/';
        },

        editPopup(company){
            company = company || {};
            this.$emit('vuedals:new', {
                name: 'right-modal',
                dismissable: false,
                escapable: true,
                props: { data: company },

                component: CompanyPopup,
                onClose: (data) => {
                    if(data && data.account){
                        let msg = 'Компания ' + data.account?.name + ' успешно сохранена';
                        this.successNotify(msg);
                    }

                    EventBus.$emit('app:update');
                }
            })
        },

        removeCompany(company){
            if(!confirm('Компания будет удалена вместе с транзакциями, ' +
                'счетами, контрагентами и другими данными ' +
                'принадлежащие компании. Подтвердите удаление'))
                return;

            this.axiosPOST('/ui/companies/' + company.id + '/remove').then(r => {
                EventBus.$emit('app:update');
            });
        }
    }
}
</script>


<template>
    <div class="container-fluid top-header__content settings__companies">

        <div class="toolbar">
            <div class="left">
                <a @click.prevent="editPopup({})" href="#" class="btn btn-primary">
                    <b-icon-plus class="left-icon"/>
                    <span>Добавить компанию</span>
                </a>

                <div class="text-secondary ml-4 align-self-center">
                    {{ companies.length }} компаний
                </div>
            </div>
        </div>


        <div class="companies-list">
            <div class="companies-list__item" v-for="company in companies">
                <h5>{{ company.name }}</h5>
                <table class="custom-table th-170">
                    <tr>
                        <th>ИНН</th>
                        <td>{{ company.inn }}</td>
                    </tr>
                    <tr>
                        <th>Юр. адрес</th>
                        <td>{{ company.legal_address }}</td>
                    </tr>
                    <tr>
                        <th>ОГРН</th>
                        <td>{{ company.ogrn }}</td>
                    </tr>
                    <tr>
                        <th>КПП</th>
                        <td>{{ company.kpp }}</td>
                    </tr>
                    <tr>
                        <th>Почтовый адрес</th>
                        <td>{{ company.address }}</td>
                    </tr>
                    <tr>
                        <th>Ссылка</th>
                        <td>
                            <a :href="getCompanyLink(company)" target="_blank">
                                {{ getCompanyLink(company) }}
                            </a>
                        </td>
                    </tr>
                </table>
                <footer class="toolbar">
                    <div class="left">
                        <a @click.prevent="editPopup(company)" href="#">Редактировать</a>
                    </div>
                    <div class="right">
                        <a @click.prevent="removeCompany(company)" href="#" class="text-danger">Удалить</a>
                    </div>
                </footer>
            </div>
        </div>

    </div>
</template>
