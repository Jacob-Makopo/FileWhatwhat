<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

// Props from backend
const props = defineProps({
    companies: Array,
    municipalities: Array,
    uploads: Array,
    filters: Object
})

// Form state
const form = ref({
    municipality_id: props.filters?.municipality_id || '',
    status: props.filters?.status || 'all'
})

// Filter companies
const filterCompanies = () => {
    router.get('/deadlines/companies', form.value, {
        preserveState: true,
        replace: true
    })
}

// Reset filters
const resetFilters = () => {
    form.value = {
        municipality_id: '',
        status: 'all'
    }
    filterCompanies()
}

// Check if company has submitted in the last 30 days
const hasCompanySubmitted = (companyId) => {
    if (!props.uploads || props.uploads.length === 0) return false

    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)
    // Normalize to start of day for accurate comparison
    thirtyDaysAgo.setHours(0, 0, 0, 0)

    return props.uploads.some(upload => {
        if (upload.company_id !== companyId) return false

        const submittedDate = new Date(upload.submitted_at)
        submittedDate.setHours(0, 0, 0, 0) // Normalize to start of day

        return submittedDate >= thirtyDaysAgo
    })
}

// Get last submission date for a company
const getLastSubmissionDate = (companyId) => {
    if (!props.uploads || props.uploads.length === 0) return null

    const companyUploads = props.uploads.filter(upload => upload.company_id === companyId)
    if (companyUploads.length === 0) return null

    const latestUpload = companyUploads.reduce((latest, current) => {
        return new Date(current.submitted_at) > new Date(latest.submitted_at) ? current : latest
    })

    return new Date(latestUpload.submitted_at).toLocaleDateString()
}

// Filtered companies based on selected filters
const filteredCompanies = computed(() => {
    let filtered = props.companies

    // Filter by municipality
    if (form.value.municipality_id) {
        filtered = filtered.filter(company => company.municipality_id == form.value.municipality_id)
    }

    // Filter by status
    if (form.value.status === 'submitted') {
        filtered = filtered.filter(company => hasCompanySubmitted(company.id))
    } else if (form.value.status === 'pending') {
        filtered = filtered.filter(company => !hasCompanySubmitted(company.id))
    }

    return filtered
})

// Count companies by status
const statusCounts = computed(() => {
    const total = props.companies.length
    const submitted = props.companies.filter(company => hasCompanySubmitted(company.id)).length
    const pending = total - submitted

    return { total, submitted, pending }
})

// Get municipality name by ID
const getMunicipalityName = (municipalityId) => {
    if (!municipalityId) return 'Unknown'
    const municipality = props.municipalities.find(m => m.id == municipalityId)
    return municipality ? municipality.name : 'Unknown'
}

// Watch for changes in filters prop
watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        form.value.municipality_id = newFilters.municipality_id || ''
        form.value.status = newFilters.status || 'all'
    }
}, { immediate: true })
</script>

<template>
    <AppLayout>
        <h2 class="text-2xl font-bold">Companies Submission Status</h2>

        <!-- Filters -->
        <div class="mt-6 rounded-xl border bg-white p-6 shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Municipality</label>
                    <select v-model="form.municipality_id" @change="filterCompanies" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
                        <option value="">All Municipalities</option>
                        <option v-for="m in municipalities" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select v-model="form.status" @change="filterCompanies" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
                        <option value="all">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button
                        @click="filterCompanies"
                        class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition"
                    >
                        Apply Filters
                    </button>
                    <button
                        @click="resetFilters"
                        class="bg-gray-600 text-white px-4 py-2 rounded-xl hover:bg-gray-700 transition"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Status Summary -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl border bg-white p-6 shadow text-center">
                <div class="text-3xl font-bold text-blue-600">{{ statusCounts.total }}</div>
                <div class="text-sm text-gray-600">Total Companies</div>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow text-center">
                <div class="text-3xl font-bold text-green-600">{{ statusCounts.submitted }}</div>
                <div class="text-sm text-gray-600">Submitted (Last 30 days)</div>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ statusCounts.pending }}</div>
                <div class="text-sm text-gray-600">Pending Submission</div>
            </div>
        </div>

        <!-- Companies Table -->
        <div class="mt-6 overflow-hidden rounded-xl border bg-white shadow">
            <div class="px-4 py-3 border-b bg-slate-50 font-medium">
                Companies ({{ filteredCompanies.length }})
            </div>

            <div v-if="!filteredCompanies.length" class="p-6 text-center text-slate-600">
                No companies found matching your criteria.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                    <tr class="text-left">
                        <th class="px-4 py-3">Company Name</th>
                        <th class="px-4 py-3">Municipality</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Last Submission</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="company in filteredCompanies" :key="company.id" class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ company.name }}</td>
                        <td class="px-4 py-3">{{ getMunicipalityName(company.municipality_id) }}</td>

                        <td class="px-4 py-3">
                <span :class="{
                  'bg-green-100 text-green-800': hasCompanySubmitted(company.id),
                  'bg-yellow-100 text-yellow-800': !hasCompanySubmitted(company.id)
                }" class="px-2 py-1 rounded-full text-xs">
                  {{ hasCompanySubmitted(company.id) ? 'Submitted' : 'Pending' }}
                </span>
                        </td>

                        <td class="px-4 py-3">
                            {{ getLastSubmissionDate(company.id) || '-' }}
                        </td>

                        <td class="px-4 py-3">
                            <button
                                @click="router.get('/uploads', { company_id: company.id })"
                                class="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                View Uploads
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
