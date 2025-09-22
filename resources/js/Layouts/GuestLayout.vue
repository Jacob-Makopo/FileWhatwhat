<script setup></script>
<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-6">
        <img :src="'/images/casey_logo.png'" alt="Casey & Associates" class="mx-auto h-12 w-auto" />
        <h1 class="mt-3 text-lg font-semibold">Casey & Associates</h1>
        <p class="text-xs text-slate-500">Premium Submissions Platform</p>
      </div>

      <div class="rounded-2xl bg-white shadow-sm border p-6">
        <slot />
      </div>

      <p class="text-center text-xs text-slate-400 mt-6">
        © {{ new Date().getFullYear() }} Casey & Associates. All rights reserved.
      </p>
    </div>
  </div>
</template>