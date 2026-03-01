<template>
    <div class="vuedal-wrapper">
        <div class="vuedal-header">
            Загрузить выписку из банка
        </div>
        <div class="vuedal-content">

            <div class="form-group">
                <label>Счет</label>
                <select-ui v-model="checkingAccount" :required="true" el-class="form-control" class="d-block"
                    :options="checkingAccounts" subtitle-key="num" placeholder="Выберите счёт для импорта" />
            </div>

            <div class="form-group">
                <label>Файл выписки (Excel)</label>
                <input type="file" class="form-control" :class="{ 'is-invalid': fileError }" @change="handleFileUpload"
                    accept=".xlsx,.xls,.csv" ref="fileInput">
                <small class="text-muted">Поддерживаемые форматы: .xlsx, .xls, .csv (макс. 10MB)</small>
                <div v-if="fileError" class="invalid-feedback d-block">{{ fileError }}</div>
            </div>

            <div v-if="uploading || uploadComplete" class="mt-3">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                        :style="{ width: uploadProgress + '%' }">
                        {{ uploadProgress }}%
                    </div>
                </div>
                <p class="text-center mt-2">{{ uploadStatus }}</p>
            </div>

            <div v-if="importResult" class="alert alert-success mt-3">
                <b-icon-check-circle class="mr-2"></b-icon-check-circle>
                {{ importResult }}
            </div>

            <div v-if="warnings && warnings.length" class="alert alert-warning mt-3">
                <h6>Предупреждения:</h6>
                <ul class="mb-0 small">
                    <li v-for="(warning, index) in warnings" :key="index">
                        {{ warning }}
                    </li>
                </ul>
            </div>

            <!-- Кнопки внутри vuedal-content, как в оригинале -->
            <div class="form-group text-right mt-4 mb-0">
                <button type="button" class="btn btn-outline-secondary mr-2" @click="close">
                    Закрыть
                </button>
                <button type="button" class="btn btn-primary" @click="save" :disabled="!canSave || uploading">
                    <span v-if="uploading">
                        <b-icon-arrow-repeat class="icon-left spin"></b-icon-arrow-repeat>
                        Загрузка...
                    </span>
                    <span v-else>
                        <b-icon-cloud-upload class="icon-left"></b-icon-cloud-upload>
                        Импортировать
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import Mixin from "./mixin";
import { EventBus } from '../../../../index';
import axiosMixin from '../../../../mixins/axios';

export default {
    name: "ImportBankStatement",
    mixins: [Mixin, axiosMixin],

    data() {
        return {
            checkingAccount: null,
            file: null,
            fileError: null,
            uploading: false,
            uploadComplete: false,
            uploadProgress: 0,
            uploadStatus: 'Подготовка...',
            importResult: null,
            warnings: []
        }
    },

    computed: {
        ...mapState({
            checkingAccounts: state => state.checkingAccounts || []
        }),

        canSave() {
            return this.checkingAccount && this.file && !this.uploading;
        }
    },

    methods: {
        handleFileUpload(event) {
            const file = event.target.files[0];
            this.fileError = null;
            this.importResult = null;
            this.warnings = [];
            this.uploadComplete = false;

            if (!file) {
                this.file = null;
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                this.fileError = 'Файл слишком большой. Максимальный размер 10MB';
                this.file = null;
                this.$refs.fileInput.value = null;
                return;
            }

            const extension = file.name.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls', 'csv'].includes(extension)) {
                this.fileError = 'Неподдерживаемый формат файла. Используйте .xlsx, .xls или .csv';
                this.file = null;
                this.$refs.fileInput.value = null;
                return;
            }

            this.file = file;
        },

        async save() {
            if (!this.canSave) return;

            this.uploading = true;
            this.uploadComplete = false;
            this.uploadProgress = 0;
            this.uploadStatus = 'Подготовка к загрузке...';

            const formData = new FormData();
            formData.append('checking_account_id', this.checkingAccount);
            formData.append('file', this.file);

            try {
                const response = await this.axiosPOST('/transactions/import', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: (progressEvent) => {
                        if (progressEvent.total) {
                            this.uploadProgress = Math.round(
                                (progressEvent.loaded * 100) / progressEvent.total
                            );

                            if (this.uploadProgress === 100) {
                                this.uploadStatus = 'Готово!';
                                this.uploadComplete = true;
                            } else {
                                this.uploadStatus = `Загрузка файла... ${this.uploadProgress}%`;
                            }
                        }
                    }
                });

                if (response.data && response.data.data) {
                    const result = response.data.data;
                    this.importResult = result.message || 'Импорт успешно завершен';

                    if (result.imported_count) {
                        this.importResult += ` Добавлено записей: ${result.imported_count}`;
                    }

                    if (result.warnings && result.warnings.length) {
                        this.warnings = result.warnings;
                    }

                    this.$notify({
                        type: 'success',
                        title: 'Успешно',
                        text: this.importResult
                    });

                    EventBus.$emit('transactions:update');
                }
            } catch (error) {
                this.uploading = false;
                this.uploadComplete = false;
                this.uploadProgress = 0;

                let errorMessage = 'Произошла ошибка при импорте';

                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response?.data?.error) {
                    errorMessage = error.response.data.error;
                } else if (error.message) {
                    errorMessage = error.message;
                }

                this.$notify({
                    type: 'danger',
                    title: 'Ошибка импорта',
                    text: errorMessage
                });
            } finally {
                this.uploading = false;
            }
        },

        close() {
            // Используем метод cancel из миксина
            this.cancel();
        }
    }
}
</script>

<style scoped>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* Стили для кнопок как в оригинале */
.text-right {
    text-align: right;
}

.mt-4 {
    margin-top: 1.5rem;
}

.mb-0 {
    margin-bottom: 0;
}

.mr-2 {
    margin-right: 0.5rem;
}

.btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: all 0.15s ease-in-out;
}

.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.btn-primary:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
    background-color: transparent;
}

.btn-outline-secondary:hover {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.icon-left {
    margin-right: 0.25rem;
}
</style>