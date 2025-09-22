<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

// Inertia page props
const page = usePage()
const csrf = page.props.csrf_token

// Check if user has permission
function hasPermission(permission) {
    return page.props.auth.user?.permissions?.includes(permission) || false
}

// Function to check if a route is active
function isActive(path) {
    return page.url === path || page.url.startsWith(path + '/')
}

// Deadlines dropdown
const isActiveDeadlines = computed(() => {
    return isActive('/deadlines/municipalities') || isActive('/deadlines/companies')
})
const deadlinesOpen = ref(isActiveDeadlines.value)
watch(isActiveDeadlines, (val) => { if (val) deadlinesOpen.value = true })

// Admin dropdown
const isActiveAdmin = computed(() => {
    const routes = [
        '/admin/users',
        '/admin/companies',
        '/admin/municipalities',
        '/admin/roles',
        '/admin/reports',
        '/admin/audits'
    ]
    return routes.some(r => isActive(r))
})
const adminOpen = ref(isActiveAdmin.value)
watch(isActiveAdmin, (val) => { if (val) adminOpen.value = true })

// Uploads group
const isActiveUploadsGroup = computed(() => {
    return isActive('/uploads') || isActive('/uploads/history')
})
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <div class="flex">
            <!-- Sidebar -->
            <aside class="hidden md:flex w-64 border-r border-slate-800 bg-animated-gradient">
                <div class="p-6 w-full">
                    <div class="flex items-center justify-center">
                        <img
                            :src="'/images/casey_logo.png'"
                            alt="Casey & Associates"
                            class="w-full max-h-14 object-contain drop-shadow"
                        />
                    </div>

                    <nav class="mt-6 space-y-1">
                        <!-- Dashboard -->
                        <Link
                            v-if="hasPermission('view dashboard')"
                            href="/dashboard"
                            :class="[
              'flex items-center gap-3 rounded-xl px-3 py-2 text-md font-medium transition',
              isActive('/dashboard')
                ? 'bg-emerald-600 text-white'
                : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
            ]"
                        >
                            Dashboard
                        </Link>

                        <!-- Uploads Group -->
                        <Link
                            v-if="hasPermission('view uploads')"
                            href="/uploads"
                            :class="[
              'flex items-center gap-3 rounded-xl px-3 py-2 text-md font-medium transition',
              page.url === '/uploads'
                ? 'bg-emerald-600 text-white'
                : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
            ]"
                        >
                            Uploads
                        </Link>

                        <Link
                            v-if="hasPermission('view uploads')"
                            href="/uploads/history"
                            :class="[
              'flex items-center gap-3 rounded-xl px-3 py-2 text-md font-medium transition',
              page.url === '/uploads/history'
                ? 'bg-emerald-600 text-white'
                : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
            ]"
                        >
                            Upload History
                        </Link>

                        <!-- Deadlines Dropdown -->
                        <div v-if="hasPermission('view deadlines')">
                            <button
                                @click="deadlinesOpen = !deadlinesOpen"
                                :class="[
                'flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-md font-medium transition',
                isActiveDeadlines.value
                  ? 'bg-emerald-600 text-white'
                  : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
              ]"
                            >
                                <span>Deadlines</span>
                                <span>
                <svg v-if="deadlinesOpen" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </span>
                            </button>

                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-screen"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 max-h-screen"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-if="deadlinesOpen" class="ml-6 mt-1 space-y-1 overflow-hidden">
                                    <Link
                                        href="/deadlines/municipalities"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/deadlines/municipalities')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Municipalities
                                    </Link>
                                    <Link
                                        href="/deadlines/companies"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/deadlines/companies')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Companies
                                    </Link>
                                </div>
                            </transition>
                        </div>

                        <!-- Notifications -->
                        <Link
                            v-if="hasPermission('view dashboard')"
                            href="/notifications"
                            :class="[
              'flex items-center gap-3 rounded-xl px-3 py-2 text-md font-medium transition',
              isActive('/notifications')
                ? 'bg-emerald-600 text-white'
                : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
            ]"
                        >
                            Notifications
                        </Link>

                        <!-- Admin Dropdown -->
                        <div v-if="hasPermission('manage users') || hasPermission('manage roles') || hasPermission('manage permissions') ">
                            <button
                                @click="adminOpen = !adminOpen"
                                :class="[
                'flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-md font-medium transition',
                isActiveAdmin.value
                  ? 'bg-emerald-600 text-white'
                  : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
              ]"
                            >
                                <span>Admin</span>
                                <span>
                <svg v-if="adminOpen" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </span>
                            </button>

                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-screen"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 max-h-screen"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-if="adminOpen" class="ml-6 mt-1 space-y-1 overflow-hidden">
                                    <Link
                                        v-if="hasPermission('manage users')"
                                        href="/admin/users"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/admin/users')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Users
                                    </Link>
                                    <Link
                                        v-if="hasPermission('view companies')"
                                        href="/admin/companies"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/admin/companies')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Companies
                                    </Link>
                                    <Link
                                        v-if="hasPermission('view municipalities')"
                                        href="/admin/municipalities"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/admin/municipalities')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Municipalities
                                    </Link>
                                    <Link
                                        v-if="hasPermission('manage roles') || hasPermission('manage permissions')"
                                        href="/admin/roles"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/admin/roles')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Roles/Permissions
                                    </Link>
                                    <Link
                                        v-if="hasPermission('view dashboard')"
                                        href="/admin/reports"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/admin/reports')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Reports
                                    </Link>
                                    <Link
                                        v-if="hasPermission('view dashboard')"
                                        href="/admin/audits"
                                        :class="[
                    'block rounded-lg px-3 py-1 text-sm transition',
                    isActive('/admin/audits')
                      ? 'bg-emerald-600 text-white'
                      : 'text-white hover:bg-emerald-50 hover:text-emerald-700'
                  ]"
                                    >
                                        Audits
                                    </Link>
                                </div>
                            </transition>
                        </div>
                    </nav>

                    <!-- Logout -->
                    <form method="post" action="/logout" class="mt-6">
                        <input type="hidden" name="_token" :value="csrf" />
                        <button
                            aria-label="Logout"
                            class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-red-300 hover:bg-red-500/20 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" />
                            </svg>
                            Logout
                        </button>
                    </form>

                    <!-- User card -->
                    <div class="mt-8 rounded-xl border border-white/10 bg-black/15 p-4">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-lg bg-emerald-600 text-white grid place-content-center font-semibold">
                                {{
                                    ($page.props.auth?.user?.name ?? 'User')
                                        .split(' ')
                                        .map(n => n[0])
                                        .slice(0, 2)
                                        .join('')
                                }}
                            </div>

                            <div class="min-w-0">
                                <p class="font-medium text-white/90 truncate">
                                    {{ $page.props.auth?.user?.name ?? 'Guest' }}
                                </p>
                                <p class="text-xs text-white/70 truncate">
                                    {{ $page.props.auth?.user?.roles?.join(', ') || 'No roles' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main -->
            <main class="flex-1">
                <header class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b">
                    <div class="w-full max-w-10/10 mx-auto px-6 py-3 flex items-center justify-between">
                        <div>
                            <h1 class="text-lg font-semibold leading-tight">Premium Submissions Platform</h1>
                            <p class="text-xs text-slate-500">Manage bulk deductions & third-party collections</p>
                        </div>

                        <div class="flex items-end gap-2">
                            <input placeholder="Search…" class="hidden md:block rounded-xl border px-3 py-2 text-sm" />
                            <Link
                                v-if="hasPermission('view submissions')"
                                href="/submissions"
                                class="inline-flex items-center justify-center rounded-xl px-3.5 py-2.5 text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700">
                                New Submission
                            </Link>
                        </div>
                    </div>
                </header>

                <div class="w-full max-w-10/10 mx-auto px-6 py-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
