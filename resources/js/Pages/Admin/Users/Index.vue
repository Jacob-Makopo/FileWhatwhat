<template>
    <AppLayout>
        <div class="px-6 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage system roles and their permissions</p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="showCreateRoleModal = true"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Add Role
                    </button>
                    <button
                        @click="showCreatePermissionModal = true"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Add Permission
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'roles'"
                        :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'roles'
                ? 'border-emerald-500 text-emerald-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
                    >
                        Roles
                    </button>
                    <button
                        @click="activeTab = 'permissions'"
                        :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'permissions'
                ? 'border-emerald-500 text-emerald-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
                    >
                        Permissions
                    </button>
                </nav>
            </div>

            <!-- Roles Tab -->
            <div v-if="activeTab === 'roles'" class="space-y-6">
                <!-- Roles Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="role in roles"
                        :key="role.id"
                        class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ role.name }}</h3>
                            <div class="flex space-x-2">
                                <button
                                    @click="editRole(role)"
                                    class="text-blue-600 hover:text-blue-900"
                                    title="Edit Role"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="confirmDeleteRole(role)"
                                    class="text-red-600 hover:text-red-900"
                                    title="Delete Role"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <span class="text-sm text-gray-600">{{ role.users_count }} users</span>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-gray-700">Permissions:</h4>
                            <div class="flex flex-wrap gap-1">
                <span
                    v-for="permission in role.permissions"
                    :key="permission.id"
                    class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800"
                >
                  {{ permission.name }}
                </span>
                                <span v-if="role.permissions.length === 0" class="text-xs text-gray-500">
                  No permissions assigned
                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Tab -->
            <div v-if="activeTab === 'permissions'" class="space-y-6">
                <!-- Permissions Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ permission.name }}</h3>
                            <div class="flex space-x-2">
                                <button
                                    @click="editPermission(permission)"
                                    class="text-blue-600 hover:text-blue-900"
                                    title="Edit Permission"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="confirmDeletePermission(permission)"
                                    class="text-red-600 hover:text-red-900"
                                    title="Delete Permission"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <span class="text-sm text-gray-600">{{ permission.roles_count }} roles</span>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-gray-700">Assigned to roles:</h4>
                            <div class="flex flex-wrap gap-1">
                <span
                    v-for="role in permission.roles"
                    :key="role.id"
                    class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800"
                >
                  {{ role.name }}
                </span>
                                <span v-if="permission.roles.length === 0" class="text-xs text-gray-500">
                  Not assigned to any roles
                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Role Modal -->
        <Modal :show="showCreateRoleModal || showEditRoleModal" @close="closeRoleModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingRole ? 'Edit Role' : 'Create New Role' }}
                </h2>

                <form @submit.prevent="editingRole ? updateRole() : createRole()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                        <input
                            v-model="roleForm.name"
                            type="text"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                            <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                <input
                                    :id="`role-permission-${permission.id}`"
                                    v-model="roleForm.permissions"
                                    :value="permission.name"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                <label :for="`role-permission-${permission.id}`" class="ml-2 text-sm text-gray-700">
                                    {{ permission.name }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeRoleModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                        >
                            {{ editingRole ? 'Update' : 'Create' }} Role
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Create/Edit Permission Modal -->
        <Modal :show="showCreatePermissionModal || showEditPermissionModal" @close="closePermissionModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingPermission ? 'Edit Permission' : 'Create New Permission' }}
                </h2>

                <form @submit.prevent="editingPermission ? updatePermission() : createPermission()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Permission Name</label>
                        <input
                            v-model="permissionForm.name"
                            type="text"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="e.g., view dashboard"
                        >
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closePermissionModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                        >
                            {{ editingPermission ? 'Update' : 'Create' }} Permission
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Role Confirmation Modal -->
        <Modal :show="showDeleteRoleModal" @close="showDeleteRoleModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Confirm Role Deletion</h2>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete the role "{{ roleToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDeleteRoleModal = false"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteRole"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete Role
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Delete Permission Confirmation Modal -->
        <Modal :show="showDeletePermissionModal" @close="showDeletePermissionModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Confirm Permission Deletion</h2>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete the permission "{{ permissionToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDeletePermissionModal = false"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deletePermission"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete Permission
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    roles: Array,
    permissions: Array
})

// State
const activeTab = ref('roles')
const showCreateRoleModal = ref(false)
const showEditRoleModal = ref(false)
const showCreatePermissionModal = ref(false)
const showEditPermissionModal = ref(false)
const showDeleteRoleModal = ref(false)
const showDeletePermissionModal = ref(false)
const editingRole = ref(null)
const editingPermission = ref(null)
const roleToDelete = ref(null)
const permissionToDelete = ref(null)

const roleForm = reactive({
    name: '',
    permissions: []
})

const permissionForm = reactive({
    name: ''
})

// Methods
const editRole = (role) => {
    editingRole.value = role
    roleForm.name = role.name
    roleForm.permissions = role.permissions.map(p => p.name)
    showEditRoleModal.value = true
}

const editPermission = (permission) => {
    editingPermission.value = permission
    permissionForm.name = permission.name
    showEditPermissionModal.value = true
}

const confirmDeleteRole = (role) => {
    roleToDelete.value = role
    showDeleteRoleModal.value = true
}

const confirmDeletePermission = (permission) => {
    permissionToDelete.value = permission
    showDeletePermissionModal.value = true
}

const closeRoleModal = () => {
    showCreateRoleModal.value = false
    showEditRoleModal.value = false
    editingRole.value = null
    roleForm.name = ''
    roleForm.permissions = []
}

const closePermissionModal = () => {
    showCreatePermissionModal.value = false
    showEditPermissionModal.value = false
    editingPermission.value = null
    permissionForm.name = ''
}

const createRole = () => {
    router.post(route('admin.roles.store'), roleForm, {
        onSuccess: () => {
            closeRoleModal()
        }
    })
}

const updateRole = () => {
    router.put(route('admin.roles.update', editingRole.value.id), roleForm, {
        onSuccess: () => {
            closeRoleModal()
        }
    })
}

const createPermission = () => {
    router.post(route('admin.permissions.store'), permissionForm, {
        onSuccess: () => {
            closePermissionModal()
        }
    })
}

const updatePermission = () => {
    router.put(route('admin.permissions.update', editingPermission.value.id), permissionForm, {
        onSuccess: () => {
            closePermissionModal()
        }
    })
}

const deleteRole = () => {
    router.delete(route('admin.roles.destroy', roleToDelete.value.id), {
        onSuccess: () => {
            showDeleteRoleModal.value = false
        }
    })
}

const deletePermission = () => {
    router.delete(route('admin.permissions.destroy', permissionToDelete.value.id), {
        onSuccess: () => {
            showDeletePermissionModal.value = false
        }
    })
}
</script>
