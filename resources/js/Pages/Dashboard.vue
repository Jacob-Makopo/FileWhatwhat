<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
import StatCard from '../Components/StatCard.vue'

const props = defineProps({
  stats: { 
    type: Object, 
    default: () => ({ 
      totalSubmissions: 0, 
      activeCompanies: 0, 
      municipalities: 0, 
      totalValue: 0 
    }) 
  },
  recent: { 
    type: Array,  
    default: () => [] 
  }
})

// Safely format numbers with fallback for undefined values
const formatNumber = (value) => {
  const num = Number(value) || 0
  return num.toLocaleString()
}

const money = (n) => {
  const amount = Number(n) || 0
  return new Intl.NumberFormat('en-ZA', { 
    style: 'currency', 
    currency: 'ZAR', 
    maximumFractionDigits: 0 
  }).format(amount)
}
</script>

<template>
  <AppLayout>
    <!-- Light look + fill remaining viewport height under sticky header -->
    <section class="bg-white text-slate-900 w-full min-h-[calc(100vh-96px)]">
      <div class="mx-auto w-full max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-5 md:py-6">

        <!-- Heading -->
        <div>
          <h2 class="text-2xl md:text-3xl font-bold">Dashboard</h2>
          <p class="mt-1 text-sm text-slate-600">
            Overview of premium submissions across companies and municipalities.
          </p>
        </div>

        <!-- STAT CARDS (Green / Gray / Black) -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard
            title="Total Submissions"
            :value="formatNumber(props.stats.totalSubmissions)"
            hint="+12.5% from last month"
            variant="emerald"
            :pulse="true"
          >
            <template #icon>
              <!-- doc icon -->
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 6h8M8 10h8M8 14h5M5 4h10l4 4v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
              </svg>
            </template>
          </StatCard>

          <StatCard
            title="Active Companies"
            :value="formatNumber(props.stats.activeCompanies)"
            hint="-2.4% from last month"
            variant="neutral"
          >
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 21h18M9 8h6M8 21V8a2 2 0 012-2h4a2 2 0 012 2v13"/>
              </svg>
            </template>
          </StatCard>

          <StatCard
            title="Municipalities"
            :value="formatNumber(props.stats.municipalities)"
            hint="no change"
            variant="black"
          >
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 11c1.657 0 3-1.79 3-4s-1.343-4-3-4-3 1.79-3 4 1.343 4 3 4zm0 0c-4.418 0-8 2.015-8 4.5V20h16v-4.5c0-2.485-3.582-4.5-8-4.5z"/>
              </svg>
            </template>
          </StatCard>

          <StatCard
            title="Total Value"
            :value="money(props.stats.totalValue)"
            hint="+8.2% from last month"
            variant="emerald"
          >
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-3.866 0-7 1.79-7 4s3.134 4 7 4 7-1.79 7-4-3.134-4-7-4zm0-5v5m0 8v5"/>
              </svg>
            </template>
          </StatCard>
        </div>

        <!-- Quick Search -->
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm p-4 sm:p-6">
          <div class="flex flex-col md:flex-row md:items-center gap-3">
            <input
              class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm
                     placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600"
              placeholder="Search organizations or references…"
            />
            <a
              href="/submissions"
              class="inline-flex items-center justify-center rounded-xl px-3.5 py-2.5 text-sm font-medium
                     bg-emerald-600 text-white hover:bg-emerald-700 transition"
            >
              Search
            </a>
          </div>
        </div>

        <!-- Recent Submissions -->
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div class="px-4 sm:px-6 py-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="font-semibold">Recent Submissions</h3>
            <a href="/submissions" class="text-sm text-emerald-700 hover:underline">View all</a>
          </div>

          <div v-if="!props.recent.length" class="px-4 sm:px-6 pb-6 text-sm text-slate-500">
            No recent submissions yet.
          </div>

          <table v-else class="min-w-full text-sm">
            <thead class="bg-slate-50">
              <tr class="text-left text-slate-600">
                <th class="px-4 sm:px-6 py-3">Reference</th>
                <th class="px-4 sm:px-6 py-3">Company</th>
                <th class="px-4 sm:px-6 py-3">Municipality</th>
                <th class="px-4 sm:px-6 py-3">Amount</th>
                <th class="px-4 sm:px-6 py-3">Status</th>
                <th class="px-4 sm:px-6 py-3">Submitted</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in props.recent" :key="s.id"
                class="border-t border-slate-200 hover:bg-slate-50 transition"
              >
                <td class="px-4 sm:px-6 py-3 font-medium text-slate-900">{{ s.reference || 'N/A' }}</td>
                <td class="px-4 sm:px-6 py-3 text-slate-700">{{ s.company || 'N/A' }}</td>
                <td class="px-4 sm:px-6 py-3 text-slate-700">{{ s.municipality || 'N/A' }}</td>
                <td class="px-4 sm:px-6 py-3 text-slate-900">{{ money(s.amount) }}</td>
                <td class="px-4 sm:px-6 py-3">
                  <span
                    class="px-2 py-0.5 rounded-full text-[11px] border"
                    :class="{
                      'bg-yellow-50 text-yellow-800 border-yellow-200': s.status==='pending',
                      'bg-emerald-50 text-emerald-700 border-emerald-200': s.status==='approved' || s.status==='reconciled',
                      'bg-rose-50 text-rose-700 border-rose-200': s.status==='rejected'
                    }"
                  >
                    {{ s.status || 'unknown' }}
                  </span>
                </td>
                <td class="px-4 sm:px-6 py-3 text-slate-700">{{ s.submitted_at || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Spacer to prevent any bottom gap -->
        <div class="h-10"></div>
      </div>
    </section>
  </AppLayout>
</template>