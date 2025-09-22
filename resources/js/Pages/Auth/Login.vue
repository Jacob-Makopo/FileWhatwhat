<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

// ✅ Replace email with employee_number
const form = useForm({ 
  employee_number: '', 
  password: '', 
  remember: false 
})

const submit = () => form.post('/login', { 
  onFinish: () => form.reset('password') 
})
</script>

<template>
  <GuestLayout>
    <Head title="Sign in" />

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Employee Number -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Employee Number</label>
        <input v-model="form.employee_number" 
               type="text" 
               required 
               autofocus
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm 
                      focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        <div v-if="form.errors.employee_number" 
             class="mt-1 text-xs text-red-600">
          {{ form.errors.employee_number }}
        </div>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Password</label>
        <input v-model="form.password" type="password" required
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm 
                      focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">
          {{ form.errors.password }}
        </div>
      </div>

      <!-- Remember Me -->
      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="form.remember" class="rounded border-slate-300" />
          Remember me
        </label>
        <!-- Uncomment if you want Forgot Password -->
        <!-- <a href="/forgot-password" class="text-sm text-emerald-700 hover:underline">Forgot?</a> -->
      </div>

      <!-- Submit -->
      <button type="submit"
              class="w-full inline-flex items-center justify-center rounded-xl px-3.5 py-2.5 text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50"
              :disabled="form.processing">
        <span v-if="form.processing">Signing in…</span>
        <span v-else>Sign in</span>
      </button>
    </form>
  </GuestLayout>
</template>
