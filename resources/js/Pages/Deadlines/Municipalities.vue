<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, watch, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// Props
const props = defineProps({
    municipalities: Array,
    selectedMunicipality: Object,
    selectedDate: String,
    deadlines: Object,
    users: Array,
    assignments: Array,
    flash: Object
})

const page = usePage()

/* -----------------
   State
-------------------*/

const form = ref({
    municipality_id: props.selectedMunicipality?.id || '',
    deadline_date: props.selectedDate || '',
    notes: '',
    assigned_user_id: '',
    company_ids: [],
})

const currentDate = ref(props.selectedDate ? new Date(props.selectedDate) : new Date())
const selectedDeadline = ref(null)
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')
const showAssignmentModal = ref(false)
const showDeadlineModal = ref(false)
const selectedDateAssignments = ref([])
const selectedDateDeadline = ref(null)
const allCompaniesSelected = ref(false)

/* -----------------
   Helpers
-------------------*/

const formatDateISO = (date) => {
    if (!date) return ''
    if (!(date instanceof Date)) date = new Date(date)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const isPastDate = (date) => {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return new Date(date) < today
}

/* -----------------
   Toast Notifications
-------------------*/

const showNotification = (message, type = 'success') => {
    toastMessage.value = message
    toastType.value = type
    showToast.value = true

    setTimeout(() => {
        showToast.value = false
    }, 3000)
}

watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) showNotification(newFlash.success, 'success')
    if (newFlash?.error) showNotification(newFlash.error, 'error')
}, { immediate: true })

/* -----------------
   Calendar
-------------------*/

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    const days = []

    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push({ day: null, date: null })
    }

    for (let i = 1; i <= lastDay.getDate(); i++) {
        const date = new Date(year, month, i)
        const dateString = formatDateISO(date)
        const deadline = props.deadlines?.[dateString] || null
        const assignments = props.assignments?.filter(a => a.deadline_date === dateString) || []

        days.push({
            day: i,
            date: dateString,
            hasDeadline: !!deadline,
            deadline,
            assignments,
            assignmentCount: assignments.length,
            isToday: formatDateISO(new Date()) === dateString,
            isSelected: form.value.deadline_date === dateString,
            isPast: isPastDate(dateString)
        })
    }

    return days
})

const monthYear = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    })
})

/* -----------------
   Navigation
-------------------*/

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
    updateUrl()
}

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
    updateUrl()
}

const goToToday = () => {
    currentDate.value = new Date()
    form.value.deadline_date = formatDateISO(new Date())
    updateUrl()
}

const updateUrl = () => {
    const params = {
        date: formatDateISO(currentDate.value)
    }

    if (form.value.municipality_id) {
        params.municipality_id = form.value.municipality_id
    }

    router.get(route('deadlines.municipalities.index', params), {}, {
        preserveState: true,
        replace: true
    })
}

/* -----------------
   Assignment Functions
-------------------*/

const unassignedCompanies = computed(() => {
    if (!props.selectedMunicipality || !form.value.deadline_date) return []

    const assignedCompanyIds = props.assignments
        .filter(a => a.municipality_id === props.selectedMunicipality.id &&
            a.deadline_date === form.value.deadline_date)
        .map(a => a.company_id)

    return props.selectedMunicipality.companies.filter(
        company => !assignedCompanyIds.includes(company.id)
    )
})

const toggleAllCompanies = () => {
    if (allCompaniesSelected.value) {
        // If already selected, clear selection
        form.value.company_ids = []
    } else {
        // Select all unassigned companies
        form.value.company_ids = unassignedCompanies.value.map(company => company.id)
    }
}

const deleteAssignment = (id) => {
    if (confirm('Are you sure you want to delete this assignment?')) {
        router.delete(`/deadlines/assignments/${id}`, {
            onSuccess: () => {
                showNotification('Assignment deleted successfully!', 'success')
                router.reload({ only: ['assignments'] })
            },
            onError: () => {
                showNotification('Failed to delete assignment', 'error')
            }
        })
    }
}

/* -----------------
   Deadline Functions
-------------------*/

const selectDate = (date) => {
    if (!date || isPastDate(date)) return
    form.value.deadline_date = date
    selectedDeadline.value = props.deadlines?.[date] || null
    form.value.notes = selectedDeadline.value?.notes || ''
}

const submitDeadline = () => {
    if (!form.value.municipality_id) {
        showNotification('Please select a municipality first', 'error')
        return
    }
    if (!form.value.deadline_date) {
        showNotification('Please select a date', 'error')
        return
    }
    if (isPastDate(form.value.deadline_date)) {
        showNotification('Cannot create deadlines for past dates', 'error')
        return
    }

    const url = selectedDeadline.value
        ? `/deadlines/municipalities/${selectedDeadline.value.id}`
        : '/deadlines/municipalities'

    const method = selectedDeadline.value ? 'put' : 'post'

    router[method](url, {
        municipality_id: form.value.municipality_id,
        deadline_date: form.value.deadline_date,
        notes: form.value.notes
    }, {
        onSuccess: () => {
            form.value.notes = ''
            selectedDeadline.value = null
            showDeadlineModal.value = false
            showNotification(selectedDeadline.value ? 'Deadline updated!' : 'Deadline created!', 'success')
            router.reload({ only: ['deadlines'] })
        },
        onError: (errors) => {
            showNotification('Failed to save deadline: ' + Object.values(errors).join(', '), 'error')
        }
    })
}

const createDeadlineWithAssignments = () => {
    if (!form.value.municipality_id) {
        showNotification('Please select a municipality first', 'error')
        return
    }
    if (!form.value.deadline_date) {
        showNotification('Please select a date', 'error')
        return
    }
    if (isPastDate(form.value.deadline_date)) {
        showNotification('Cannot create deadlines for past dates', 'error')
        return
    }
    if (!form.value.assigned_user_id) {
        showNotification('Please select a user to assign', 'error')
        return
    }
    if (form.value.company_ids.length === 0) {
        showNotification('Please select at least one company', 'error')
        return
    }

    // Use the correct route name that matches your web.php
    router.post(route('deadlines.create-with-assignments'), {
        municipality_id: form.value.municipality_id,
        deadline_date: form.value.deadline_date,
        notes: form.value.notes,
        assigned_user_id: form.value.assigned_user_id,
        company_ids: form.value.company_ids
    }, {
        onSuccess: () => {
            form.value.notes = ''
            form.value.assigned_user_id = ''
            form.value.company_ids = []
            allCompaniesSelected.value = false
            showDeadlineModal.value = false
            showNotification('Deadline and assignments created successfully!', 'success')
            router.reload({ only: ['deadlines', 'assignments'] })
        },
        onError: (errors) => {
            showNotification('Failed to create deadline: ' + Object.values(errors).join(', '), 'error')
        }
    })
}

const deleteDeadline = (id) => {
    if (confirm('Are you sure you want to delete this deadline?')) {
        router.delete(`/deadlines/municipalities/${id}`, {
            onSuccess: () => {
                selectedDeadline.value = null
                form.value.notes = ''
                showNotification('Deadline deleted successfully!', 'success')
                router.reload({ only: ['deadlines'] })
            },
            onError: () => {
                showNotification('Failed to delete deadline', 'error')
            }
        })
    }
}

/* -----------------
   Municipality Functions
-------------------*/

const municipalityChanged = () => {
    if (!form.value.municipality_id) return

    const params = new URLSearchParams()
    params.append('municipality_id', form.value.municipality_id)
    params.append('date', formatDateISO(currentDate.value))

    router.get(`/deadlines/municipalities?${params.toString()}`, {}, {
        preserveState: true,
        replace: true
    })
}

const clearMunicipality = () => {
    form.value.municipality_id = ''
    form.value.deadline_date = ''
    form.value.notes = ''
    form.value.assigned_user_id = ''
    form.value.company_ids = []
    selectedDeadline.value = null
    allCompaniesSelected.value = false

    const params = new URLSearchParams()
    params.append('date', formatDateISO(currentDate.value))

    router.get(`/deadlines/municipalities?${params.toString()}`, {}, {
        preserveState: true,
        replace: true
    })
}

/* -----------------
   Assignment Modal
-------------------*/

const openAssignmentModal = (date) => {
    if (!date) return
    selectedDateAssignments.value = props.assignments?.filter(a => a.deadline_date === date) || []
    selectedDateDeadline.value = props.deadlines?.[date] || null
    showAssignmentModal.value = true
}

const closeAssignmentModal = () => {
    showAssignmentModal.value = false
    selectedDateAssignments.value = []
    selectedDateDeadline.value = null
}

const openDeadlineModal = () => {
    if (!form.value.municipality_id) {
        showNotification('Please select a municipality first', 'error')
        return
    }
    showDeadlineModal.value = true
}

const closeDeadlineModal = () => {
    showDeadlineModal.value = false
    form.value.assigned_user_id = ''
    form.value.company_ids = []
    form.value.notes = ''
    allCompaniesSelected.value = false
}

/* -----------------
   Company Status
-------------------*/

const companySubmissionMap = computed(() => {
    const map = {}
    if (!props.selectedMunicipality || !props.selectedMunicipality.companies) return map

    props.selectedMunicipality.companies.forEach(company => {
        const isAssigned = props.assignments.some(a =>
            a.company_id === company.id &&
            a.municipality_id === props.selectedMunicipality.id
        )

        map[company.id] = { isAssigned }
    })

    return map
})

const getCompanyStatus = (companyId) => {
    const status = companySubmissionMap.value[companyId]
    return status?.isAssigned ? 'assigned' : 'unassigned'
}

/* -----------------
   Watchers
-------------------*/

watch(() => props.selectedMunicipality, (newVal) => {
    if (newVal) form.value.municipality_id = newVal.id
})

watch(() => props.selectedDate, (newVal) => {
    if (newVal) {
        form.value.deadline_date = newVal
        currentDate.value = new Date(newVal)
    }
})

watch(() => form.value.company_ids, (newVal) => {
    allCompaniesSelected.value = newVal.length === unassignedCompanies.value.length && unassignedCompanies.value.length > 0
}, { deep: true })

watch(() => unassignedCompanies.value, () => {
    // Reset select all when unassigned companies change
    allCompaniesSelected.value = false
    form.value.company_ids = []
}, { deep: true })

onMounted(() => {
    if (props.selectedDate) {
        selectDate(props.selectedDate)
    }
})
</script>

<template>
    <AppLayout>
        <!-- Toast Notification -->
        <div v-if="showToast" :class="[
            'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg border-l-4 transition-all duration-300',
            toastType === 'success' ? 'bg-green-50 border-green-500 text-green-800' :
            toastType === 'error' ? 'bg-red-50 border-red-500 text-red-800' :
            'bg-yellow-50 border-yellow-500 text-yellow-800'
        ]">
            <div class="flex items-center">
                <span class="font-medium">{{ toastMessage }}</span>
                <button @click="showToast = false" class="ml-4 text-gray-500 hover:text-gray-700">
                    ×
                </button>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div v-if="showAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">
                        Assignments for {{ formatDate(form.deadline_date) }}
                    </h3>
                    <button @click="closeAssignmentModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <!-- Deadline Info -->
                    <div v-if="selectedDateDeadline" class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-2">Deadline Information</h4>
                        <p class="text-sm text-blue-700">{{ selectedDateDeadline.notes }}</p>
                    </div>

                    <div v-if="selectedDateAssignments.length > 0" class="space-y-4">
                        <div v-for="assignment in selectedDateAssignments" :key="assignment.id"
                             class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 text-lg">{{ assignment.company_name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Assigned to:</span> {{ assignment.user_name }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Municipality:</span> {{ assignment.municipality_name }}
                                    </p>
                                    <p v-if="assignment.notes" class="text-sm text-gray-500 mt-2">
                                        <span class="font-medium">Notes:</span> {{ assignment.notes }}
                                    </p>
                                </div>
                                <button
                                    @click="deleteAssignment(assignment.id)"
                                    class="ml-4 p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Remove assignment"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <div class="text-4xl mb-4">📋</div>
                        <p class="text-lg">No assignments for this date</p>
                    </div>
                </div>

                <div class="flex justify-end p-6 border-t border-gray-200">
                    <button @click="closeAssignmentModal" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Deadline Creation Modal -->
        <div v-if="showDeadlineModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800">
                        Create Deadline and Assignments
                    </h3>
                    <button @click="closeDeadlineModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Municipality</label>
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="font-medium text-gray-800">{{ selectedMunicipality.name }}</p>
                                    <p class="text-sm text-gray-600">{{ selectedMunicipality.code }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deadline Date</label>
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="font-medium text-gray-800">{{ formatDate(form.deadline_date) }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assign to User</label>
                                <select
                                    v-model="form.assigned_user_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                    required
                                >
                                    <option value="">-- Select User --</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deadline Notes</label>
                                <textarea
                                    v-model="form.notes"
                                    placeholder="Add deadline notes..."
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 resize-none"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Companies</label>
                            <div class="mb-3 flex items-center">
                                <input
                                    type="checkbox"
                                    :checked="allCompaniesSelected"
                                    @change="toggleAllCompanies"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    :disabled="unassignedCompanies.length === 0"
                                >
                                <span class="ml-2 text-sm text-gray-700">Select all companies</span>
                                <span class="ml-auto text-sm text-gray-500">
                                    {{ form.company_ids.length }} of {{ unassignedCompanies.length }} selected
                                </span>
                            </div>

                            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 max-h-64 overflow-y-auto">
                                <div v-if="unassignedCompanies.length === 0" class="text-center py-4 text-gray-500">
                                    All companies are already assigned for this date
                                </div>
                                <div v-else class="space-y-2">
                                    <div v-for="company in unassignedCompanies" :key="company.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="company.id"
                                            v-model="form.company_ids"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                        <span class="ml-3 text-sm text-gray-700">{{ company.name }} ({{ company.code }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 p-6 border-t border-gray-200">
                    <button @click="closeDeadlineModal" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="createDeadlineWithAssignments"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!form.assigned_user_id || form.company_ids.length === 0"
                    >
                        Create Deadline & Assignments
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Municipality Management</h1>
                    <p class="text-gray-600 mt-2">Manage deadlines and assignments for municipalities</p>
                </div>
                <button @click="goToToday" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Today
                </button>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-8">
                    <!-- Municipality Selection -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Select Municipality</label>
                        <div class="flex items-center space-x-4">
                            <select
                                v-model="form.municipality_id"
                                @change="municipalityChanged"
                                class="flex-1 rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                            >
                                <option value="">-- Select Municipality --</option>
                                <option
                                    v-for="municipality in municipalities"
                                    :key="municipality.id"
                                    :value="municipality.id"
                                >
                                    {{ municipality.name }} ({{ municipality.code }})
                                </option>
                            </select>
                            <button
                                v-if="form.municipality_id"
                                @click="clearMunicipality"
                                class="px-4 py-3 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div v-if="form.municipality_id && props.selectedMunicipality" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Panel - Companies -->
                        <div class="lg:col-span-1">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                    Companies in {{ props.selectedMunicipality.name }}
                                    <span class="text-sm text-gray-500">({{ props.selectedMunicipality.companies.length }})</span>
                                </h3>

                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="company in props.selectedMunicipality.companies"
                                        :key="company.id"
                                        :class="[
                                            'p-4 rounded-lg border transition-colors',
                                            getCompanyStatus(company.id) === 'assigned'
                                                ? 'bg-green-50 border-green-200'
                                                : 'bg-white border-gray-200'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-medium text-gray-800">{{ company.name }}</h4>
                                                <p class="text-sm text-gray-600">{{ company.code }}</p>
                                            </div>
                                            <span
                                                :class="[
                                                    'px-2 py-1 rounded-full text-xs font-medium',
                                                    getCompanyStatus(company.id) === 'assigned'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-gray-100 text-gray-800'
                                                ]"
                                            >
                                                {{ getCompanyStatus(company.id) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel - Calendar -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <!-- Calendar Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <button @click="prevMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>

                                    <h2 class="text-xl font-semibold text-gray-800">{{ monthYear }}</h2>

                                    <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Calendar Grid -->
                                <div class="grid grid-cols-7 gap-2 mb-2">
                                    <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day"
                                         class="text-center text-sm font-medium text-gray-500 py-2">
                                        {{ day }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-7 gap-2">
                                    <div
                                        v-for="(day, index) in daysInMonth"
                                        :key="index"
                                        :class="[
                                            'h-24 p-2 border rounded-lg transition-colors cursor-pointer relative overflow-hidden',
                                            day.isToday ? 'border-blue-300 bg-blue-50' : 'border-gray-200',
                                            day.isSelected ? 'ring-2 ring-blue-500 ring-inset' : '',
                                            day.isPast ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'
                                        ]"
                                        @click="!day.isPast && selectDate(day.date)"
                                        @dblclick="!day.isPast && openAssignmentModal(day.date)"
                                    >
                                        <!-- Day Number -->
                                        <div class="flex items-center justify-between mb-1">
                                            <span :class="[
                                                'text-sm font-medium',
                                                day.isToday ? 'text-blue-600' : 'text-gray-700'
                                            ]">
                                                {{ day.day }}
                                            </span>
                                            <span v-if="day.isToday" class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                        </div>

                                        <!-- Deadline Indicator -->
                                        <div v-if="day.hasDeadline" class="mb-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Deadline
                                            </span>
                                        </div>

                                        <!-- Assignments -->
                                        <div v-if="day.assignmentCount > 0" class="space-y-1">
                                            <div class="flex items-center text-xs text-green-600">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ day.assignmentCount }} assignment{{ day.assignmentCount > 1 ? 's' : '' }}
                                            </div>
                                        </div>

                                        <!-- Hover Actions -->
                                        <div v-if="!day.isPast && day.date" class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 flex items-center justify-center opacity-0 hover:opacity-100 transition-all">
                                            <button
                                                @click.stop="openAssignmentModal(day.date)"
                                                class="p-1 bg-white rounded shadow-sm text-xs text-gray-600 hover:text-gray-800"
                                            >
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                                    <button
                                        @click="openDeadlineModal"
                                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="!form.deadline_date || isPastDate(form.deadline_date)"
                                    >
                                        Create Deadline & Assign
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 text-gray-500">
                        <div class="text-6xl mb-4">🏢</div>
                        <p class="text-lg">Please select a municipality to view deadlines and assignments</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
