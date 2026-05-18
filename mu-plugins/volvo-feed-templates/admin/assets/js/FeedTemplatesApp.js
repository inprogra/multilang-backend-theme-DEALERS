const { createApp, ref, reactive, computed, onMounted, toRefs, nextTick } = Vue;

createApp({
    setup() {
        const config = window.volvoFeedTemplates;

        const state = reactive({
            templates: [],
            globalTemplates: [],
            activeTab: 'my',
            showModal: false,
            editingTemplate: null,
            toast: null,
            loading: false,
            previewData: '',
            showPreview: false,
            form: {
                id: '',
                name: '',
                slug: '',
                format: 'csv',
                car_type: 'both',
                fields: {},
                is_global: config.isMainBlog && config.isSuperAdmin
            }
        });

        const { templates, globalTemplates, activeTab, showModal, editingTemplate, toast, loading, previewData, showPreview, form } = toRefs(state);

        const availableSources = {
            id: 'ID',
            post_id: 'ID posta',
            title: 'Tytuł',
            description: 'Opis',
            price: 'Cena',
            discount_price: 'Cena promocyjna',
            image_url: 'Główne zdjęcie URL',
            images: 'Wszystkie zdjęcia URL',
            link: 'Link URL',
            brand: 'Marka',
            model: 'Model',
            version: 'Wersja',
            year: 'Rok',
            production_year: 'Rok produkcji',
            mileage: 'Przebieg',
            fuel_type: 'Rodzaj paliwa',
            gearbox: 'Skrzynia biegów',
            transmission: 'Napęd',
            drive: 'Napęd',
            body_type: 'Typ nadwozia',
            color: 'Kolor',
            door_count: 'Liczba drzwi',
            engine: 'Silnik',
            power: 'Moc',
            power_hp: 'Moc (KM)',
            vin: 'VIN',
            offer_number: 'Numer oferty',
            category: 'Kategoria',
            dealer_name: 'Nazwa dealera',
            dealer_phone: 'Telefon dealera',
            dealer_location: 'Lokalizacja dealera',
            dealer_id: 'ID dealera',
            showroom: 'Salon',
            currency: 'Waluta',
            is_featured: 'Wyróżniony',
            car_type: 'Typ samochodu'
        };

        const displayedTemplates = computed(() => {
            if (activeTab.value === 'global') {
                return globalTemplates.value;
            }
            return templates.value;
        });

        const showToast = (message, isError = false) => {
            toast.value = { message, isError };
            setTimeout(() => {
                toast.value = null;
            }, 3000);
        };

        const loadTemplates = async () => {
            try {
                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'vft_get_templates',
                        nonce: config.nonce,
                        blog_id: config.blogId
                    })
                });
                const data = await response.json();
                console.log('Loaded templates:', data);
                if (data.success) {
                    templates.value = data.data.templates || [];
                    globalTemplates.value = data.data.globalTemplates || [];
                    console.log('templates.value:', templates.value.length);
                    console.log('globalTemplates.value:', globalTemplates.value.length);
                }
            } catch (error) {
                console.error('Nie udało się załadować szablonów:', error);
            }
        };

        const getFieldCount = (template) => {
            if (!template.fields || typeof template.fields !== 'object') return 0;
            return Object.keys(template.fields).length;
        };

        const openCreateModal = () => {
            form.value = {
                id: '',
                name: '',
                slug: '',
                format: 'csv',
                car_type: 'both',
                fields: {},
                is_global: config.isMainBlog && config.isSuperAdmin
            };
            editingTemplate.value = null;
            showModal.value = true;
        };

        const openEditModal = (template) => {
            if (!form.value) {
                form.value = {
                    id: '',
                    name: '',
                    slug: '',
                    format: 'csv',
                    car_type: 'both',
                    fields: {},
                    is_global: false
                };
            }
            let fieldsObj = {};
            if (template.fields && typeof template.fields === 'object') {
                fieldsObj = { ...template.fields };
            } else if (Array.isArray(template.fields)) {
                template.fields.forEach(f => {
                    fieldsObj[f] = f;
                });
            }
            form.value.id = template.id || '';
            form.value.name = template.name || '';
            form.value.slug = template.slug || '';
            form.value.format = template.format || 'csv';
            form.value.car_type = template.car_type || 'both';
            form.value.is_global = template.is_global || false;
            Object.keys(form.value.fields).forEach(key => delete form.value.fields[key]);
            Object.assign(form.value.fields, fieldsObj);
            editingTemplate.value = template;
            showModal.value = true;
        };

        const addFieldMapping = () => {
            console.log('addFieldMapping called - form.value:', form.value, 'form.value.fields:', form.value?.fields);
            if (!form.value || !form.value.fields) {
                if (form.value) form.value.fields = {};
                return;
            }
            const usedKeys = Object.keys(form.value.fields);
            const allKeys = Object.keys(availableSources);
            console.log('usedKeys:', usedKeys, 'allKeys:', allKeys);
            const newSource = allKeys.find(key => !usedKeys.includes(key));
            console.log('newSource:', newSource);
            if (newSource) {
                form.value.fields[newSource] = newSource;
                nextTick(() => {
                    const fieldMappings = document.querySelectorAll('.vft-field-mapping-row');
                    if (fieldMappings.length > 0) {
                        fieldMappings[fieldMappings.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        };

        const getAvailableSourcesList = () => {
            if (!form.value || !form.value.fields) {
                if (form.value) form.value.fields = {};
                return Object.keys(availableSources);
            }
            const usedKeys = Object.keys(form.value.fields);
            return Object.keys(availableSources).filter(key => !usedKeys.includes(key));
        };

        const removeFieldMapping = (source) => {
            if (!form.value || !form.value.fields) return;
            const newFields = { ...form.value.fields };
            delete newFields[source];
            form.value.fields = newFields;
        };

        const handleSourceChange = (oldSource, newSource) => {
            if (!form.value || !form.value.fields) return;
            const oldOutput = form.value.fields[oldSource];
            delete form.value.fields[oldSource];
            form.value.fields[newSource] = oldOutput;
        };

        const openCopyModal = (template) => {
            const newName = prompt(config.i18n.copy + ' "' + template.name + '" - ' + config.i18n.templateName + ':', template.name + ' (kopia)');
            if (!newName) return;

            fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'vft_copy_template',
                    nonce: config.nonce,
                    template_id: template.id,
                    new_name: newName,
                    blog_id: config.blogId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Feed został skopiowany');
                    loadTemplates();
                } else {
                    showToast(data.data.message || 'Błąd', true);
                }
            });
        };

        const saveTemplate = async () => {
            if (!form.value || !form.value.name) {
                showToast('Nazwa feedu jest wymagana', true);
                return;
            }

            loading.value = true;

            try {
                const formData = new FormData();
                formData.append('action', 'vft_save_template');
                formData.append('nonce', config.nonce);
                formData.append('data', JSON.stringify(form.value));
                formData.append('blog_id', config.blogId);

                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                console.log('Save response:', data);
                if (data.success) {
                    showModal.value = false;
                    showToast('Feed zapisany pomyślnie');
                    loadTemplates();
                } else {
                    showToast(data.data?.message || 'Błąd podczas zapisywania', true);
                }
            } catch (error) {
                console.error('Save error:', error);
                showToast('Nie udało się zapisać feedu', true);
            } finally {
                loading.value = false;
            }
        };

        const deleteTemplate = async (template) => {
            if (!confirm(config.i18n.confirmDelete)) {
                return;
            }

            try {
                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'vft_delete_template',
                        nonce: config.nonce,
                        template_id: template.id,
                        blog_id: config.blogId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    showToast(config.i18n.delete);
                    loadTemplates();
                } else {
                    showToast(data.data.message || 'Błąd', true);
                }
            } catch (error) {
                showToast('Nie udało się usunąć szablonu', true);
            }
        };

        const getFeedUrl = (template, format = null) => {
            let path = template.slug;
            if (format) {
                path += '/' + format;
            }
            let baseUrl = config.siteUrl.replace(/\/wp$/i, '');
            return baseUrl + '/feeds/' + path + '/';
        };

        const copyUrl = (template) => {
            const url = getFeedUrl(template);
            navigator.clipboard.writeText(url).then(() => {
                showToast(config.i18n.urlCopied);
            });
        };

        const previewTemplate = async (template) => {
            try {
                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'vft_get_preview',
                        nonce: config.nonce,
                        template_id: template.slug,
                        blog_id: config.blogId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    previewData.value = data.data.preview;
                    showPreview.value = true;
                } else {
                    showToast(data.data.message || 'Błąd', true);
                }
            } catch (error) {
                showToast('Nie udało się wygenerować podglądu', true);
            }
        };

        const getFieldMappings = () => {
            if (!form.value || !form.value.fields) {
                if (form.value) form.value.fields = {};
                return [];
            }
            return Object.entries(form.value.fields).map(([source, tag]) => ({
                source,
                tag: tag || source
            }));
        };

        const availableSourcesFiltered = computed(() => {
            if (!form.value || !form.value.fields) return Object.keys(availableSources);
            const existingKeys = Object.keys(form.value.fields);
            return Object.keys(availableSources).filter(key => !existingKeys.includes(key));
        });

        onMounted(() => {
            loadTemplates();
        });

        return {
            templates,
            globalTemplates,
            activeTab,
            showModal,
            editingTemplate,
            toast,
            loading,
            previewData,
            showPreview,
            form,
            availableSources,
            displayedTemplates,
            config,
            getFieldCount,
            openCreateModal,
            openEditModal,
            openCopyModal,
            saveTemplate,
            deleteTemplate,
            copyUrl,
            getFeedUrl,
            previewTemplate,
            addFieldMapping,
            removeFieldMapping,
            getAvailableSourcesList,
            getFieldMappings,
            handleSourceChange
        };
    },
    template: `
        <div id="volvo-feed-templates-app">
            <div class="vft-header">
                <h1>{{ config.i18n.title }}</h1>
                <button v-if="activeTab === 'my'" class="vft-btn vft-btn-primary" @click="openCreateModal">
                    + {{ config.i18n.createNew }}
                </button>
            </div>

            <div v-if="globalTemplates.length > 0" class="vft-tabs">
                <button 
                    class="vft-tab" 
                    :class="{ active: activeTab === 'my' }"
                    @click="activeTab = 'my'"
                >
                    {{ config.i18n.myTemplates }} ({{ templates.length }})
                </button>
                <button 
                    class="vft-tab" 
                    :class="{ active: activeTab === 'global' }"
                    @click="activeTab = 'global'"
                >
                    {{ config.i18n.globalTemplates }} ({{ globalTemplates.length }})
                </button>
            </div>

            <div v-if="displayedTemplates.length === 0" class="vft-section">
                <div class="vft-empty-state">
                    <div class="vft-empty-state-icon">📁</div>
                    <p>{{ config.i18n.noTemplates }}</p>
                </div>
            </div>

            <div v-else class="vft-templates-grid">
                <div v-for="template in displayedTemplates" :key="template.id" class="vft-template-card">
                    <div class="vft-template-card-header">
                        <h3 class="vft-template-card-title">{{ template.name }}</h3>
                        <div>
                            <span class="vft-template-card-badge" :class="'vft-badge-' + template.format">
                                {{ template.format.toUpperCase() }}
                            </span>
                            <span v-if="template.is_global" class="vft-template-card-badge vft-badge-global" style="margin-left: 4px;">
                                Globalny
                            </span>
                        </div>
                    </div>
                    <div class="vft-template-card-body">
                        <div class="vft-template-card-info">
                            <div class="vft-template-info-item">
                                <strong>{{ config.i18n.carType }}:</strong>
                                <span v-if="template.car_type === 'both'">{{ config.i18n.both }}</span>
                                <span v-else-if="template.car_type === 'new'">{{ config.i18n.new }}</span>
                                <span v-else>{{ config.i18n.used }}</span>
                            </div>
                            <div class="vft-template-info-item">
                                <strong>{{ config.i18n.slug }}:</strong> {{ template.slug }}
                            </div>
                            <div class="vft-template-info-item">
                                <strong>{{ config.i18n.fields }}:</strong> {{ getFieldCount(template) }}
                            </div>
                        </div>
                    </div>
                    <div class="vft-template-card-footer">
                        <button class="vft-btn vft-btn-secondary vft-btn-sm" @click="copyUrl(template)">
                            {{ config.i18n.copyUrl }}
                        </button>
                        <button class="vft-btn vft-btn-secondary vft-btn-sm" @click="previewTemplate(template)">
                            {{ config.i18n.preview }}
                        </button>
                        <button v-if="activeTab === 'my'" class="vft-btn vft-btn-secondary vft-btn-sm" @click="openEditModal(template)">
                            {{ config.i18n.editTemplate }}
                        </button>
                        <button v-if="activeTab === 'my'" class="vft-btn vft-btn-secondary vft-btn-sm" @click="openCopyModal(template)">
                            {{ config.i18n.copy }}
                        </button>
                        <button v-if="activeTab === 'my' && !template.is_global" class="vft-btn vft-btn-danger vft-btn-sm" @click="deleteTemplate(template)">
                            {{ config.i18n.delete }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="showModal" class="vft-modal-overlay" @click.self="showModal = false">
                <div class="vft-modal" style="max-width: 800px;">
                    <div class="vft-modal-header">
                        <h2>{{ editingTemplate ? config.i18n.editTemplate : config.i18n.createNew }}</h2>
                        <button class="vft-modal-close" @click="showModal = false">&times;</button>
                    </div>
                    <div class="vft-modal-body">
                        <div class="vft-form-group">
                            <label class="vft-form-label">{{ config.i18n.templateName }}</label>
                            <input type="text" v-model="form.name" class="vft-form-input" />
                        </div>
                        <div class="vft-form-group">
                            <label class="vft-form-label">{{ config.i18n.slug }}</label>
                            <input type="text" v-model="form.slug" class="vft-form-input" />
                            <div class="vft-form-hint">Przyjazny URL (wygenerowany automatycznie jeśli puste)</div>
                        </div>
                        <div class="vft-form-group">
                            <label class="vft-form-label">{{ config.i18n.format }}</label>
                            <select v-model="form.format" class="vft-form-select">
                                <option value="csv">{{ config.i18n.csv }}</option>
                                <option value="xml">{{ config.i18n.xml }}</option>
                            </select>
                        </div>
                        <div class="vft-form-group">
                            <label class="vft-form-label">{{ config.i18n.carType }}</label>
                            <select v-model="form.car_type" class="vft-form-select">
                                <option value="both">{{ config.i18n.both }}</option>
                                <option value="new">{{ config.i18n.new }}</option>
                                <option value="used">{{ config.i18n.used }}</option>
                            </select>
                        </div>
                        <div class="vft-form-group">
                            <label class="vft-form-label">{{ config.i18n.fields }} - Mapowanie źródła danych do nazwy kolumny/znacznika</label>
                            <div style="margin-bottom: 12px;">
                                <button class="vft-btn vft-btn-secondary vft-btn-sm" @click="console.log('DEBUG: form.value=', form.value), addFieldMapping()">+ Dodaj pole</button>
                            </div>
                            <div class="vft-field-mappings">
                                <div v-for="(mapping, index) in getFieldMappings()" :key="mapping.source" class="vft-field-mapping-row">
                                    <select 
                                        class="vft-form-select" 
                                        style="flex: 1;"
                                        :value="mapping.source"
                                        @change="handleSourceChange(mapping.source, $event.target.value)"
                                    >
                                        <template v-for="(label, key) in availableSources" :key="key">
                                            <option v-if="getAvailableSourcesList().indexOf(key) === -1" :value="key" disabled>
                                                {{ label }} ({{ key }}) - użyte
                                            </option>
                                            <option v-else :value="key">
                                                {{ label }} ({{ key }})
                                            </option>
                                        </template>
                                    </select>
                                    <span style="padding: 0 8px;">→</span>
                                    <input 
                                        type="text" 
                                        class="vft-form-input" 
                                        style="flex: 1;"
                                        v-model="form.fields[mapping.source]"
                                        :placeholder="'Nazwa wyjściowa'"
                                    />
                                    <button class="vft-btn vft-btn-danger vft-btn-sm" @click="removeFieldMapping(mapping.source)">&times;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vft-modal-footer">
                        <button class="vft-btn vft-btn-secondary" @click="showModal = false">
                            {{ config.i18n.cancel }}
                        </button>
                        <button class="vft-btn vft-btn-primary" @click="saveTemplate" :disabled="loading">
                            {{ loading ? '...' : config.i18n.save }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="showPreview" class="vft-modal-overlay" @click.self="showPreview = false">
                <div class="vft-modal" style="max-width: 900px;">
                    <div class="vft-modal-header">
                        <h2>{{ config.i18n.preview }}</h2>
                        <button class="vft-modal-close" @click="showPreview = false">&times;</button>
                    </div>
                    <div class="vft-modal-body">
                        <pre class="vft-preview-box">{{ previewData }}</pre>
                    </div>
                    <div class="vft-modal-footer">
                        <button class="vft-btn vft-btn-secondary" @click="showPreview = false">
                            {{ config.i18n.cancel }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="toast" class="vft-toast" :class="{ error: toast.isError }">
                {{ toast.message }}
            </div>
        </div>
    `
}).mount('#volvo-feed-templates-app');
