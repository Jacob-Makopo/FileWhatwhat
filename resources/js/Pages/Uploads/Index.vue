
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

// =====================================
// Props (server data)
// =====================================
const props = defineProps({
    filters: Object,
    uploads: Object, // Changed from submissions to uploads
    companies: Array,
    municipalities: Array,
    pendingDeadlines: Array,
})

// =====================================
// Form & UI State
// =====================================
const form = ref({
    municipality_id: '',
    selected_company_ids: [],
    original_files: [],       // multiple .eml/.msg
    workings_file: null,      // single
    systems_import_file: null // single
})

const step = ref(1) // 1: context, 2: files
const isSubmitting = ref(false)

// search inputs
const muniQuery = ref('')
const companyQuery = ref('')

// ========= Derived: selections =========
const selectedMunicipality = computed(() =>
    (props.municipalities || []).find(m => String(m.id) === String(form.value.municipality_id)) || null
)
const companiesInSelected = computed(() =>
    (props.companies || []).filter(c => String(c?.municipality_id) === String(form.value.municipality_id))
)
const filteredMunicipalities = computed(() => {
    const list = props.municipalities || []
    const q = muniQuery.value.trim().toLowerCase()
    return q ? list.filter(m => (m?.name || '').toLowerCase().includes(q)) : list
})
const filteredCompanies = computed(() => {
    const list = companiesInSelected.value
    const q = companyQuery.value.trim().toLowerCase()
    return q ? list.filter(c => (c?.name || '').toLowerCase().includes(q)) : list
})
const selectedCompanies = computed(() =>
    (props.companies || []).filter(c => form.value.selected_company_ids.includes(c.id))
)

// ========= Proper Re-upload Detection =========
// This will track which companies have existing uploads in the database
const companiesWithExistingUploads = ref(new Set())

// Fetch existing uploads for the selected municipality
const fetchExistingUploads = async () => {
    if (!selectedMunicipality.value) {
        companiesWithExistingUploads.value.clear()
        return
    }

    try {
        const response = await fetch(`/api/municipalities/${selectedMunicipality.value.id}/existing-uploads`)
        if (response.ok) {
            const data = await response.json()
            companiesWithExistingUploads.value = new Set(data.existing_company_ids || [])
        }
    } catch (error) {
        console.error('Error fetching existing uploads:', error)
        companiesWithExistingUploads.value.clear()
    }
}

// Watch for municipality changes to fetch existing uploads
watch(() => form.value.municipality_id, async (newMuniId) => {
    form.value.selected_company_ids = []
    companyQuery.value = ''
    showMissing.value = true
    showUpdated.value = true
    reuploadReasons.value = {}

    if (newMuniId) {
        await fetchExistingUploads()
    } else {
        companiesWithExistingUploads.value.clear()
    }
})

// Check if a company has existing uploads (proper re-upload detection)
const hasExistingUploads = (company) => {
    return companiesWithExistingUploads.value.has(company.id)
}

// ========= Deadlines (for selected muni only) =========
const deadlineForSelected = computed(() => {
    if (!selectedMunicipality.value) return null
    const name = selectedMunicipality.value.name
    return (props.pendingDeadlines || []).find(d => String(d?.municipality) === String(name)) || null
})

// Updated logic for pending/updated companies based on ACTUAL database records
const pendingCompanyNames = computed(() => deadlineForSelected.value?.pending_companies || [])
const updatedCompanyNames = computed(() => {
    const allNames = companiesInSelected.value.map(c => c.name)
    const pending = new Set(pendingCompanyNames.value)
    return allNames.filter(n => !pending.has(n))
})

// Use proper re-upload detection instead of simple name comparison
const isCompanyUpdated = (company) => {
    return hasExistingUploads(company)
}

// ========= Collapsible panels (Missing / Updated) =========
const showMissing = ref(true)
const showUpdated = ref(true)

const totalCompaniesCount = computed(() => companiesInSelected.value.length)
const pendingCount = computed(() => pendingCompanyNames.value.length)
const updatedCount = computed(() => companiesInSelected.value.filter(c => hasExistingUploads(c)).length)
const progressPct = computed(() => {
    const total = totalCompaniesCount.value
    return total ? Math.round((updatedCount.value / total) * 100) : 0
})

// ========= Re-upload reasons (only for companies with existing uploads) =========
const reasonOptions = [
    'Feedback / Amendments',
    'Corrections to Data',
    'Non-payment Follow-up',
    'Additional/Updated Period',
    'Wrong File Previously Uploaded',
    'Wrong Company Previously Selected',
    'Wrong Period/Date Range',
    'Replacement (Corrupted/Unreadable)',
    'Format/Template Compliance Fix',
    'Late Discovery of Missing Records',
    'Policy/Fee Override Change',
    'Other'
]

// { [companyId]: { type: string, note?: string } }
const reuploadReasons = ref({})

const companiesNeedingReason = computed(() =>
    selectedCompanies.value.filter(c => hasExistingUploads(c))
)

const reasonsMissing = computed(() =>
    companiesNeedingReason.value.filter(c => {
        const r = reuploadReasons.value[c.id]
        if (!r || !r.type) return true
        if (r.type === 'Other' && !r.note?.trim()) return true
        return false
    })
)

const hasReuploadConflicts = computed(() => companiesNeedingReason.value.length > 0)
const hasAllReasons = computed(() => reasonsMissing.value.length === 0)

// Clean up reasons when selection changes
watch(() => form.value.selected_company_ids.slice(), (ids) => {
    // Drop reason entries for unselected companies
    const keep = new Set(ids)
    Object.keys(reuploadReasons.value).forEach(k => {
        if (!keep.has(Number(k))) delete reuploadReasons.value[k]
    })
    // Add placeholders for new selections with existing uploads
    companiesNeedingReason.value.forEach(c => {
        if (!reuploadReasons.value[c.id]) {
            reuploadReasons.value[c.id] = { type: '', note: '' }
        }
    })
}, { immediate: true })

// ========= File helpers =========
const onFilePick = (e, field) => {
    const files = Array.from(e.target?.files || [])
    if (!files.length) return
    if (field === 'original_files') {
        form.value.original_files = [...form.value.original_files, ...files]
    } else {
        form.value[field] = files[0]
    }
}
const removeOriginalAt = (i) => form.value.original_files.splice(i, 1)

// ========= Preview (simple) =========
const preview = ref({ open: false, name: '', url: '', type: '', text: '' })

const extType = (name = '') => {
    const ext = String(name).split('.').pop()?.toLowerCase()
    if (['eml','msg'].includes(ext)) return 'email'
    if (['pdf'].includes(ext)) return 'pdf'
    if (['png','jpg','jpeg','gif','webp','bmp'].includes(ext)) return 'image'
    if (['txt','csv','json','xml','html','htm'].includes(ext)) return 'text'
    if (['xls','xlsx','doc','docx','ppt','pptx'].includes(ext)) return 'office'
    return 'other'
}

const openPreview = async (url, name) => {
    const type = extType(name)
    preview.value = { open: true, name, url, type, text: '' }
    if (['email','text','office'].includes(type)) {
        try {
            const res = await fetch(url)
            preview.value.text = await res.text()
        } catch {
            preview.value.text = 'Preview not available. Download instead.'
        }
    }
}
const closePreview = () => (preview.value = { open: false, name: '', url: '', type: '', text: '' })

const onKey = (e) => { if (e.key === 'Escape' && preview.value.open) closePreview() }
onMounted(() => document.addEventListener('keydown', onKey))
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))

// ========= Actions =========
const toggleCompany = (id) => {
    const set = new Set(form.value.selected_company_ids)
    set.has(id) ? set.delete(id) : set.add(id)
    form.value.selected_company_ids = [...set]
}
const selectAllCompanies = () => {
    const ids = filteredCompanies.value.map(c => c.id)
    form.value.selected_company_ids = Array.from(new Set([...form.value.selected_company_ids, ...ids]))
}
const clearCompanies = () => (form.value.selected_company_ids = [])

// Guarded step navigation
const canProceedToFiles = computed(() =>
    !!form.value.municipality_id && form.value.selected_company_ids.length > 0
)
const goStep = (n) => {
    if (n === 1) { step.value = 1; return }
    if (n === 2 && canProceedToFiles.value) { step.value = 2; return }
    alert('Please select a municipality and at least one company before proceeding to Files.')
}

// Final submit with guard (includes reasons)
const submitAll = () => {
    if (!canProceedToFiles.value) {
        alert('Context incomplete. Select a municipality and at least one company.')
        step.value = 1
        return
    }
    if (!form.value.original_files.length) {
        alert('Please add at least one email file (EML/MSG).')
        return
    }
    if (hasReuploadConflicts.value && !hasAllReasons.value) {
        alert('Please provide a reason for each company being re-uploaded.')
        return
    }

    const fd = new FormData()
    fd.append('municipality_id', form.value.municipality_id)
    form.value.selected_company_ids.forEach(id => fd.append('company_ids[]', id))
    form.value.original_files.forEach(f => fd.append('original_files[]', f))
    if (form.value.workings_file) fd.append('workings_file', form.value.workings_file)
    if (form.value.systems_import_file) fd.append('systems_import_file', form.value.systems_import_file)

    // Attach reasons only for companies with existing uploads
    companiesNeedingReason.value.forEach(c => {
        const r = reuploadReasons.value[c.id] || {}
        fd.append(`reupload_reasons[${c.id}][type]`, r.type || '')
        fd.append(`reupload_reasons[${c.id}][note]`, r.note || '')
    })

    isSubmitting.value = true
    router.post('/uploads', fd, {
        forceFormData: true,
        onFinish: () => { isSubmitting.value = false },
        onSuccess: () => {
            form.value = { municipality_id: '', selected_company_ids: [], original_files: [], workings_file: null, systems_import_file: null }
            step.value = 1
            muniQuery.value = ''
            companyQuery.value = ''
            reuploadReasons.value = {}
            companiesWithExistingUploads.value.clear()
        },
        onError: (errors) => {
            alert('Error submitting uploads: ' + (errors.message || 'Please try again'))
        }
    })
}

const removeUpload = (id) => {
    if (!confirm('Delete this upload?')) return
    router.delete(`/uploads/${id}`, {
        onSuccess: () => {
            // Refresh the data
            router.reload()
        }
    })
}

// Utils
const fmt = (d) => d ? new Date(d).toLocaleDateString() : '—'

// Fetch existing uploads on component mount if municipality is already selected
onMounted(() => {
    if (form.value.municipality_id) {
        fetchExistingUploads()
    }
})
</script>

<template>
    <AppLayout>
        <div class="wrap">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Uploads</h1>
                    <p>Simple, reliable intake for municipalities and companies.</p>
                </div>
                <nav class="steps">
                    <button :class="['step', step===1 && 'active']" @click="goStep(1)"><span>1</span> Context</button>
                    <button :class="['step', step===2 && 'active']" @click="goStep(2)"><span>2</span> Files</button>
                </nav>
            </div>

            <div class="grid">
                <!-- Main -->
                <main class="main">
                    <!-- STEP 1: CONTEXT -->
                    <section v-if="step===1" class="card">
                        <div class="section">
                            <label class="label">Municipality</label>
                            <input class="input" v-model="muniQuery" placeholder="Search municipality…" />
                            <div class="list">
                                <button
                                    v-for="m in filteredMunicipalities"
                                    :key="m.id"
                                    type="button"
                                    class="row"
                                    @click="form.municipality_id=m.id; muniQuery=m.name">
                                    <span class="name">{{ m.name }}</span>
                                    <span v-if="form.municipality_id===m.id" class="pill">Selected</span>
                                </button>
                                <div v-if="!filteredMunicipalities.length" class="empty">No results</div>
                            </div>
                            <p v-if="form.municipality_id" class="hint">
                                Chosen: {{ selectedMunicipality?.name }}
                            </p>
                        </div>

                        <div class="section">
                            <div class="row between">
                                <label class="label">Companies</label>
                                <div class="actions">
                                    <button class="ghost" @click="selectAllCompanies" :disabled="!filteredCompanies.length">Select all</button>
                                    <button class="ghost" @click="clearCompanies" :disabled="!form.selected_company_ids.length">Clear</button>
                                </div>
                            </div>
                            <input class="input" v-model="companyQuery" :disabled="!form.municipality_id" placeholder="Search companies…" />
                            <div v-if="form.municipality_id" class="list">
                                <label v-for="c in filteredCompanies" :key="c.id" class="row">
                                    <span class="name">
                                        {{ c.name }}
                                        <span v-if="hasExistingUploads(c)" class="tag tag-warning">Has Existing Uploads</span>
                                    </span>
                                    <input type="checkbox" :checked="form.selected_company_ids.includes(c.id)" @change="toggleCompany(c.id)" />
                                </label>
                                <div v-if="!filteredCompanies.length" class="empty">No results</div>
                            </div>

                            <div v-if="selectedCompanies.length" class="chips">
                                <span v-for="c in selectedCompanies" :key="c.id" class="chip">
                                    {{ c.name }}
                                    <span v-if="hasExistingUploads(c)" class="tag tag-warning tiny">Re-upload</span>
                                    <button class="x" @click="toggleCompany(c.id)" aria-label="Remove">×</button>
                                </span>
                            </div>
                        </div>

                        <div class="footer">
                            <button class="primary" @click="goStep(2)" :disabled="!canProceedToFiles">Next: Files</button>
                        </div>
                    </section>

                    <!-- STEP 2: FILES -->
                    <section v-else class="card">
                        <div class="section note" v-if="selectedCompanies.length">
                            <strong>Selected companies:</strong> {{ selectedCompanies.map(c=>c.name).join(', ') }}
                        </div>

                        <!-- Re-upload reasons block (shows only for companies with existing uploads) -->
                        <div v-if="hasReuploadConflicts" class="section reupload">
                            <div class="reupload-head">
                                <strong>Re-uploads detected:</strong>
                                <span class="badge badge-warning">{{ companiesNeedingReason.length }}</span>
                            </div>
                            <p class="tiny muted">These companies have existing uploads in the system. Provide a reason for re-upload.</p>

                            <div class="reupload-list">
                                <div v-for="c in companiesNeedingReason" :key="'ru-'+c.id" class="reupload-row">
                                    <div class="reupload-name">{{ c.name }}</div>
                                    <div class="reupload-control">
                                        <select class="input" v-model="(reuploadReasons[c.id] ||= { type:'', note:'' }).type">
                                            <option value="" disabled>Select a reason…</option>
                                            <option v-for="opt in reasonOptions" :key="opt" :value="opt">{{ opt }}</option>
                                        </select>
                                    </div>
                                    <div class="reupload-note" v-if="reuploadReasons[c.id]?.type === 'Other'">
                                        <input class="input" v-model="reuploadReasons[c.id].note" placeholder="Describe the reason…" />
                                    </div>
                                </div>
                            </div>

                            <p v-if="!hasAllReasons" class="tiny warn-text">Select a reason for each re-upload (and add a note for "Other").</p>
                        </div>

                        <div class="section">
                            <label class="label">Email Files (EML/MSG) <span class="req">*</span></label>
                            <input class="input" type="file" multiple accept=".eml,.msg" @change="e=>onFilePick(e,'original_files')" />
                            <div v-if="form.original_files.length" class="chips mt">
                                <span v-for="(f,i) in form.original_files" :key="i" class="chip">
                                    {{ f.name }}
                                    <button class="x" @click="removeOriginalAt(i)" aria-label="Remove">×</button>
                                </span>
                            </div>
                            <p class="hint">{{ form.original_files.length }} file(s) selected</p>
                        </div>

                        <div class="grid2 section">
                            <div>
                                <label class="label">Workings File</label>
                                <input class="input" type="file" @change="e=>onFilePick(e,'workings_file')" />
                                <p v-if="form.workings_file" class="hint">Selected: {{ form.workings_file.name }}</p>
                            </div>
                            <div>
                                <label class="label">Systems Import File</label>
                                <input class="input" type="file" @change="e=>onFilePick(e,'systems_import_file')" />
                                <p v-if="form.systems_import_file" class="hint">Selected: {{ form.systems_import_file.name }}</p>
                            </div>
                        </div>

                        <div class="footer between">
                            <button class="ghost" @click="goStep(1)">Back</button>
                            <button
                                class="primary"
                                @click="submitAll"
                                :disabled="!form.original_files.length || isSubmitting || (hasReuploadConflicts && !hasAllReasons)"
                                :title="(hasReuploadConflicts && !hasAllReasons) ? 'Provide reasons for re-uploads' : ''"
                            >
                                {{ isSubmitting ? 'Submitting...' : 'Submit' }}
                            </button>
                        </div>
                    </section>

                    <!-- UPLOADS TABLE -->
                    <section class="card" v-if="(props.uploads?.data||[]).length">
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Municipality</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Email Files</th>
                                    <th>System Import</th>
                                    <th>Workings</th>
                                    <th>Extracted Dates</th>
                                    <th>Import Date</th>
                                    <th>Submitted</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="u in props.uploads.data" :key="u.id">
                                    <td class="font-mono">{{ u.reference }}</td>
                                    <td>{{ u.municipality?.name }}</td>
                                    <td>{{ u.company?.name }}</td>
                                    <td>
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-800': u.status === 'Pending',
                                            'bg-blue-100 text-blue-800': u.status === 'Processing',
                                            'bg-green-100 text-green-800': u.status === 'Completed',
                                            'bg-red-100 text-red-800': u.status === 'Rejected'
                                        }" class="px-2 py-1 rounded-full text-xs">
                                            {{ u.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <template v-if="u.original_file_names?.length">
                                            <div class="col">
                                                <a v-for="(n,i) in u.original_file_names" :key="i"
                                                   :href="u.original_file_urls && u.original_file_urls[i]"
                                                   target="_blank"
                                                   class="link">{{ n }}</a>
                                            </div>
                                        </template>
                                        <span v-else>—</span>
                                    </td>
                                    <td>
                                        <a v-if="u.systems_import_file_name"
                                           :href="u.systems_import_file_url"
                                           target="_blank"
                                           class="link">{{ u.systems_import_file_name }}</a>
                                        <span v-else>—</span>
                                    </td>
                                    <td>
                                        <a v-if="u.workings_file_name"
                                           :href="u.workings_file_url"
                                           target="_blank"
                                           class="link">{{ u.workings_file_name }}</a>
                                        <span v-else>—</span>
                                    </td>
                                    <td>
                                        <div v-if="u.extracted_dates?.length" class="col tiny">
                                            <div v-for="(d,i) in u.extracted_dates" :key="i">
                                                {{ fmt(d) }}
                                                <span v-if="u.original_file_names?.[i]" class="muted"> ({{ u.original_file_names[i] }})</span>
                                            </div>
                                        </div>
                                        <span v-else>—</span>
                                    </td>
                                    <td>{{ fmt(u.system_import_date) }}</td>
                                    <td>{{ fmt(u.submitted_at) }}</td>
                                    <td class="right">
                                        <button class="danger" @click="removeUpload(u.id)">Delete</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="empty card" v-else>
                        <div class="center">
                            <div class="icon">📄</div>
                            <div class="title">No uploads yet</div>
                            <div class="desc">Your recent uploads will appear here after submission.</div>
                        </div>
                    </section>
                </main>

                <!-- Sidebar: Deadlines + Re-upload indicator -->
                <aside class="side">
                    <section class="card deadline-card">
                        <div class="side-head">Deadlines & Status</div>

                        <template v-if="!selectedMunicipality">
                            <p class="muted">Select a municipality to view its status.</p>
                        </template>

                        <template v-else>
                            <div class="row between small">
                                <strong>{{ selectedMunicipality.name }}</strong>
                                <span class="muted">{{ deadlineForSelected?.deadline_date || 'No deadline' }}</span>
                            </div>

                            <!-- Totals + progress -->
                            <div class="deadline-totals">
                                <span class="badge badge-neutral">Total: {{ totalCompaniesCount }}</span>
                                <span class="badge badge-success">Uploaded: {{ updatedCount }}</span>
                                <span class="badge badge-danger">Pending: {{ pendingCount }}</span>
                            </div>
                            <div
                                class="meter"
                                role="progressbar"
                                aria-label="Upload progress"
                                :aria-valuemin="0"
                                :aria-valuemax="100"
                                :aria-valuenow="progressPct">
                                <div class="fill" :style="{ width: progressPct + '%' }"></div>
                            </div>

                            <!-- Company Status -->
                            <div class="panel panel-info">
                                <button class="panel-head" type="button" @click="showMissing = !showMissing">
                                    <div class="panel-title">
                                        <span class="dot bg-blue-500"></span>
                                        Pending Submission
                                    </div>
                                    <div class="panel-meta">
                                        <span class="badge badge-danger">{{ pendingCount }}</span>
                                        <span class="chev" :class="{ open: showMissing }">▾</span>
                                    </div>
                                </button>
                                <transition name="collapse">
                                    <div v-show="showMissing" class="panel-body">
                                        <ul v-if="pendingCompanyNames.length" class="subbullets">
                                            <li v-for="(n,i) in pendingCompanyNames" :key="'p'+i" class="ellipsis">{{ n }}</li>
                                        </ul>
                                        <p v-else class="muted tiny">All companies have been submitted.</p>
                                    </div>
                                </transition>
                            </div>

                            <div class="panel panel-success">
                                <button class="panel-head" type="button" @click="showUpdated = !showUpdated">
                                    <div class="panel-title">
                                        <span class="dot bg-green-500"></span>
                                        Already Uploaded
                                    </div>
                                    <div class="panel-meta">
                                        <span class="badge badge-success">{{ updatedCount }}</span>
                                        <span class="chev" :class="{ open: showUpdated }">▾</span>
                                    </div>
                                </button>
                                <transition name="collapse">
                                    <div v-show="showUpdated" class="panel-body">
                                        <ul v-if="updatedCount > 0" class="subbullets">
                                            <li v-for="c in companiesInSelected.filter(hasExistingUploads)" :key="c.id" class="ellipsis">
                                                {{ c.name }}
                                            </li>
                                        </ul>
                                        <p v-else class="muted tiny">No uploads yet for this municipality.</p>
                                    </div>
                                </transition>
                            </div>

                            <!-- Re-upload need indicator -->
                            <div v-if="hasReuploadConflicts" class="reupload-indicator">
                                <div class="ri-head">
                                    <span class="ri-dot"></span>
                                    <strong>{{ companiesNeedingReason.length }} re-upload{{ companiesNeedingReason.length>1?'s':'' }} need reason</strong>
                                </div>
                                <ul class="ri-list">
                                    <li v-for="c in companiesNeedingReason" :key="'ril-'+c.id">
                                        <span class="ellipsis">{{ c.name }}</span>
                                        <span class="ri-reason" :class="{'ri-missing': !(reuploadReasons[c.id]?.type)}">
                                            {{ reuploadReasons[c.id]?.type || 'No reason' }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </section>
                </aside>
            </div>

            <!-- Preview modal -->
            <div v-if="preview.open" class="modal">
                <div class="dialog" role="dialog" aria-modal="true" :aria-label="'Preview '+preview.name">
                    <header class="dialog-head">
                        <div class="title">{{ preview.name }}</div>
                        <div class="spacer"></div>
                        <a :href="preview.url" download class="ghost small">Download</a>
                        <button class="ghost small" @click="closePreview">Close</button>
                    </header>
                    <div class="dialog-body">
                        <template v-if="preview.type==='pdf'">
                            <embed :src="preview.url" type="application/pdf" class="pdf" />
                        </template>
                        <template v-else-if="preview.type==='image'">
                            <img :src="preview.url" :alt="preview.name" class="img" />
                        </template>
                        <template v-else-if="preview.type==='email' || preview.type==='text' || preview.type==='office'">
                            <pre class="pre">{{ preview.text }}</pre>
                        </template>
                        <template v-else>
                            <div class="muted center">Preview not available.</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Add this new style for warning tag */
.tag-warning {
    background: #fff7ed;
    color: #9a3412;
    border-color: #fed7aa;
}

.tiny {
    font-size: 11px;
    padding: 1px 4px;
}

.bg-blue-500 { background-color: #3b82f6; }
.bg-green-500 { background-color: #10b981; }

.panel-info .panel-head {
    background: #f0f9ff;
}

/* Rest of your existing styles remain the same */
.wrap { max-width: 1200px; margin: 0 auto; padding: 16px; }
.header { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:12px; }
.header h1 { margin:0; font-size:24px; font-weight:700; color:#0f172a; }
.header p { margin:4px 0 0; color:#64748b; font-size:13px; }
.steps { display:flex; gap:8px; }
.step { display:inline-flex; align-items:center; gap:8px; border:1px solid #e2e8f0; background:#fff; padding:8px 12px; border-radius:12px; font-size:13px; color:#334155; }
.step span { display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:999px; background:#e2e8f0; font-weight:700; font-size:12px; }
.step.active { border-color:#2563eb; color:#0b51d0; }
.step.active span { background:#2563eb; color:#fff; }

.grid { display:grid; grid-template-columns: 1fr; gap:16px; }
@media (min-width: 1024px) { .grid { grid-template-columns: 3fr 1fr; gap:20px; } }

.main { display:flex; flex-direction:column; gap:16px; }
.side { position: sticky; top:16px; align-self:start; }

.card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); padding:16px; }
.section { margin-bottom:16px; }
.footer { display:flex; gap:12px; justify-content:flex-end; margin-top:8px; }
.footer.between { justify-content:space-between; }

.label { font-size:13px; color:#334155; font-weight:600; margin-bottom:6px; display:block; }
.input { width:100%; border:1px solid #cbd5e1; border-radius:12px; padding:8px 10px; font-size:14px; outline:none; }
.input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }

.primary { background:#16a34a; color:#fff; border:none; border-radius:12px; padding:10px 14px; font-weight:600; }
.primary:disabled { background:#94a3b8; cursor:not-allowed; }
.ghost { border:1px solid #e2e8f0; background:#fff; border-radius:10px; padding:6px 10px; font-size:12px; color:#334155; }
.ghost.small { padding:4px 8px; font-size:12px; }
.danger { border:1px solid #fecaca; color:#b91c1c; background:#fff; border-radius:8px; padding:4px 8px; font-size:12px; }

.pill { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; font-size:11px; padding:2px 8px; border-radius:999px; }
.hint { font-size:12px; color:#64748b; margin-top:6px; }
.req { color:#ef4444; }

.tag { margin-left:8px; font-size:11px; padding:2px 6px; border-radius:999px; border:1px solid transparent; }
.tag-success { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }

.list { border:1px solid #e2e8f0; border-radius:12px; max-height:220px; overflow:auto; margin-top:8px; }
.row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:8px 10px; width:100%; background:#fff; border:none; border-bottom:1px solid #f1f5f9; text-align:left; }
.row:last-child { border-bottom:none; }
.row:hover { background:#f8fafc; }
.row.small { padding:6px 8px; }
.row.between { justify-content:space-between; }
.name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.empty { padding:10px; font-size:12px; color:#94a3b8; text-align:center; }

.chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
.chip { display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; color:#334155; border-radius:999px; padding:6px 10px; font-size:12px; }
.chip .x { border:none; background:transparent; font-size:14px; line-height:1; color:#64748b; }

.mt { margin-top:6px; }

.table-wrap { overflow:auto; }
.table { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
.table thead th { position:sticky; top:0; background:#f8fafc; color:#475569; text-align:left; font-weight:700; padding:10px; border-bottom:1px solid #e2e8f0; }
.table td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:top; color:#0f172a; }
.table tbody tr:nth-child(even) td { background:#fafafa; }
.right { text-align:right; }
.col { display:flex; flex-direction:column; gap:4px; }
.col.tiny { font-size:12px; }
.link { color:#2563eb; text-decoration:none; }
.link:hover { text-decoration:underline; }
.muted { color:#64748b; }
.tiny { font-size:12px; }
.small { font-size:12px; }


/* two-column helper inside Files step */
.grid2 { display:grid; grid-template-columns:1fr; gap:12px; }
@media (min-width: 640px) { .grid2 { grid-template-columns: 1fr 1fr; } }

/* ====== Re-upload reasons block ====== */
.reupload { border:1px dashed #f59e0b; background:#fffbeb; border-radius:12px; padding:12px; }
.reupload-head { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
.reupload-list { display:flex; flex-direction:column; gap:8px; margin-top:8px; }
.reupload-row { display:grid; grid-template-columns: 1.4fr 1fr; gap:8px; }
.reupload-name { align-self:center; font-weight:600; color:#0f172a; }
.reupload-control select.input { min-width: 220px; }
.reupload-note { grid-column: 1 / -1; }
.warn-text { color:#b45309; }

/* ====== Sidebar ====== */
.side-head { font-weight:700; color:#0f172a; margin-bottom:8px; }
.subbullets { list-style:disc; padding-left:18px; margin-top:4px; display:flex; flex-direction:column; gap:2px; }
.ellipsis { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* ====== Empty State ====== */
.empty .center { text-align:center; padding:24px 8px; }
.empty .icon { font-size:28px; }
.empty .title { font-weight:600; color:#0f172a; margin-top:8px; }
.empty .desc { color:#64748b; font-size:13px; }

/* ====== Modal ====== */
.modal { position:fixed; inset:0; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; padding:16px; z-index:50; }
.dialog { width:100%; max-width:900px; background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.12); overflow:hidden; }
.dialog-head { display:flex; align-items:center; gap:8px; padding:12px 16px; border-bottom:1px solid #eef2f7; }
.dialog-head .title { font-weight:700; color:#0f172a; }
.dialog-head .spacer { flex:1; }
.dialog-body { padding:16px; max-height:70vh; overflow:auto; }
.pdf { width:100%; height:70vh; border:0; }
.img { max-width:100%; max-height:70vh; display:block; margin:0 auto; border-radius:8px; }
.pre { white-space:pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size:12px; line-height:1.45; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; }

/* ====== Deadline sidebar enhancements ====== */
.deadline-card { border-color:#e2e8f0; }

.deadline-totals {
    display:flex; flex-wrap:wrap; gap:6px; margin:8px 0 10px;
}
.badge {
    display:inline-flex; align-items:center; gap:6px;
    border-radius:999px; font-size:11px; padding:4px 8px; font-weight:600;
    border:1px solid transparent;
}
.badge-neutral { background:#f8fafc; color:#334155; border-color:#e2e8f0; }
.badge-danger  { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
.badge-success { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
.badge-warning { background:#fff7ed; color:#9a3412; border-color:#fed7aa; }

.meter {
    height:8px; border-radius:999px; background:#f1f5f9; border:1px solid #e5e7eb;
    overflow:hidden; margin-bottom:12px;
}
.meter .fill {
    height:100%; width:0%; background:linear-gradient(90deg,#22c55e,#16a34a);
    transition: width .25s ease;
}

/* Collapsible panels */
.panel { border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; margin-top:10px; }
.panel-head {
    width:100%; display:flex; align-items:center; justify-content:space-between;
    background:#fff; padding:10px 12px; border:0; cursor:pointer; text-align:left;
}
.panel-title { display:flex; align-items:center; gap:8px; font-weight:600; font-size:13px; }
.panel-meta { display:flex; align-items:center; gap:8px; }
.chev { transition: transform .2s ease; font-size:12px; color:#64748b; }
.chev.open { transform: rotate(180deg); }

.panel-danger .panel-head { background:#fff7f7; }
.panel-success .panel-head { background:#f6fef9; }

.panel-body { padding:10px 12px; background:#fff; }

/* Tiny dot colour accents */
.panel-danger .dot { width:8px; height:8px; border-radius:999px; background:#ef4444; display:inline-block; }
.panel-success .dot { width:8px; height:8px; border-radius:999px; background:#16a34a; display:inline-block; }

/* Smooth collapse */
.collapse-enter-from, .collapse-leave-to { max-height:0; opacity:.0; }
.collapse-enter-to, .collapse-leave-from { max-height:300px; opacity:1; }
.collapse-enter-active, .collapse-leave-active { transition: all .18s ease; overflow:hidden; }

/* Sidebar re-upload indicator */
.reupload-indicator { margin-top:12px; padding:10px; border:1px dashed #f59e0b; background:#fffbeb; border-radius:10px; }
.ri-head { display:flex; align-items:center; gap:8px; margin-bottom:6px; color:#9a3412; }
.ri-dot { width:8px; height:8px; background:#f59e0b; border-radius:999px; display:inline-block; }
.ri-list { display:flex; flex-direction:column; gap:4px; font-size:12px; }
.ri-reason { margin-left:6px; color:#065f46; }
.ri-reason.ri-missing { color:#9a3412; font-style:italic; }
</style>
