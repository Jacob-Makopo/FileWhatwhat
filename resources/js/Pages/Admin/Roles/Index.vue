<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const page = usePage()
const roles = ref([])
const permissions = ref([])
const filters = ref({ search: '' })
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedRole = ref(null)
const form = ref({
    name: '',
    permissions: []
})

// Check permissions
function hasPermission(permission) {
    return page.props.auth.user?.permissions?.includes(permission) || false
}

// Load roles
function loadRoles() {
    axios.get('/admin/roles/data', { params: filters.value })
        .then(response => {
            roles.value = response.data.roles.data
        })
        .catch(error => {
            console.error('Error loading roles:', error)
            alert('Error loading roles: ' + error.response?.data?.message || error.message)
        })
}

// Create role
function createRole() {
    axios.post('/admin/roles', form.value)
        .then(response => {
            loadRoles()
            showCreateModal.value = false
            resetForm()
            alert('Role created successfully')
        })
        .catch(error => {
            console.error('Error creating role:', error)
            alert('Error creating role: ' + error.response?.data?.message || error.message)
        })
}

// Update role
function updateRole() {
    axios.put(`/admin/roles/${selectedRole.value.id}`, form.value)
        .then(response => {
            loadRoles()
            showEditModal.value = false
            resetForm()
            alert('Role updated successfully')
        })
        .catch(error => {
            console.error('Error updating role:', error)
            alert('Error updating role: ' + error.response?.data?.message || error.message)
        })
}

// Delete role
function deleteRole(role) {
    if (confirm('Are you sure you want to delete this role?')) {
        axios.delete(`/admin/roles/${role.id}`)
            .then(response => {
                loadRoles()
                alert('Role deleted successfully')
            })
            .catch(error => {
                console.error('Error deleting role:', error)
                alert('Error deleting role: ' + error.response?.data?.message || error.message)
            })
    }
}

// Reset form
function resetForm() {
    form.value = {
        name: '',
        permissions: []
    }
    selectedRole.value = null
}

// Edit role
function editRole(role) {
    selectedRole.value = role
    form.value = {
        name: role.name,
        permissions: role.permissions.map(permission => permission.id)
    }
    showEditModal.value = true
}

onMounted(() => {
    loadRoles()
    // Load permissions
    axios.get('/admin/permissions/data')
        .then(response => {
            permissions.value = response.data.permissions
        })
        .catch(error => {
            console.error('Error loading permissions:', error)
            alert('Error loading permissions: ' + error.response?.data?.message || error.message)
        })
})
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
                <button
                    v-if="hasPermission('manage roles')"
                    @click="showCreateModal = true"
                    class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
                    Add Role
                </button>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <input
                    v-model="filters.search"
                    @input="loadRoles"
                    placeholder="Search roles..."
                    class="w-full p-2 border border-gray-300 rounded-lg"
                />
            </div>

            <!-- Roles Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th v-if="hasPermission('manage roles')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="role in roles" :key="role.id">
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ role.name }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                    <span v-for="permission in role.permissions" :key="permission.id" class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                        {{ permission.name }}
                                    </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ role.users_count }} users</td>
                        <td v-if="hasPermission('manage roles')" class="px-6 py-4 whitespace-nowrap">
                            <button @click="editRole(role)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button @click="deleteRole(role)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Create Role Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 w-96 max-h-96 overflow-y-auto">
                    <h2 class="text-xl font-bold mb-4">Create Role</h2>
                    <form @submit.prevent="createRole">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" required class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Permissions</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-2">
                                <div v-for="permission in permissions" :key="permission.id" class="flex items-center mb-2">
                                    <input
                                        :id="'permission-' + permission.id"
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="form.permissions"
                                        class="mr-2"
                                    >
                                    <label :for="'permission-' + permission.id" class="text-sm">{{ permission.name }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Create</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Role Modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 w-96 max-h-96 overflow-y-auto">
                    <h2 class="text-xl font-bold mb-4">Edit Role</h2>
                    <form @submit.prevent="updateRole">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="form.name" required class="w-full p-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Permissions</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-2">
                                <div v-for="permission in permissions" :key="permission.id" class="flex items-center mb-2">
                                    <input
                                        :id="'edit-permission-' + permission.id"
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="form.permissions"
                                        class="mr-2"
                                    >
                                    <label :for="'edit-permission-' + permission.id" class="text-sm">{{ permission.name }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
