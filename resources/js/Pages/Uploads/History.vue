<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'

// Props from backend
const props = defineProps({
  filters: Object,
  uploads: Object,
  statusOptions: Array
})

// Form state
const form = ref({
  status: props.filters.status || '',
  search: props.filters.search || ''
})

// Preview modal state (Index.vue)
const previewModal = ref({
  isOpen: false,
  title: '',
  content: '',
  fileType: '',
  fileName: '',
  fileUrl: ''
})

// Handle file preview (Index.vue)
const previewFile = async (fileUrl, fileName, fileType) => {
  previewModal.value.isOpen = true
  previewModal.value.title = fileName
  previewModal.value.fileName = fileName
  previewModal.value.fileType = fileType
  previewModal.value.fileUrl = fileUrl
  
  try {
    if (fileType === 'email') {
      const response = await fetch(fileUrl)
      const content = await response.text()
      previewModal.value.content = content
    } else if (fileType === 'pdf') {
      previewModal.value.content = fileUrl
    } else if (fileType === 'image') {
      previewModal.value.content = fileUrl
    } else if (fileType === 'text' || fileType === 'office') {
      const response = await fetch(fileUrl)
      const content = await response.text()
      previewModal.value.content = content || 'Preview not available. Please download the file.'
    } else {
      previewModal.value.content = 'Preview not available for this file type. Please download the file.'
    }
  } catch (error) {
    console.error('Error loading file preview:', error)
    previewModal.value.content = 'Unable to load preview. Please download the file.'
  }
}

// Close preview modal (Index.vue)
const closePreview = () => {
  previewModal.value.isOpen = false
  previewModal.value.title = ''
  previewModal.value.content = ''
  previewModal.value.fileType = ''
  previewModal.value.fileName = ''
  previewModal.value.fileUrl = ''
}

// Get file type from extension (Index.vue)
const getFileType = (fileName) => {
  if (!fileName) return 'unknown'
  
  const extension = fileName.split('.').pop().toLowerCase()
  if (['eml', 'msg'].includes(extension)) return 'email'
  if (['pdf'].includes(extension)) return 'pdf'
  if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) return 'image'
  if (['txt', 'csv', 'json', 'xml', 'html', 'htm'].includes(extension)) return 'text'
  if (['xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx'].includes(extension)) return 'office'
  return 'unknown'
}

// Filter submissions
const filter = () => {
  router.get('/uploads/history', form.value, {
    preserveState: true,
    replace: true
  })
}

// Reset filters
const resetFilters = () => {
  form.value = {
    status: '',
    search: ''
  }
  filter()
}

// Format date for display
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

// Export to Excel
const exportToExcel = () => {
  const params = new URLSearchParams(form.value)
  window.location.href = `/uploads/export?${params.toString()}`
}
</script>

<template>
  <AppLayout>
    <h2 class="text-2xl font-bold">Uploads History</h2>

    <!-- Preview Modal (Index.vue) -->
    <div v-if="previewModal.isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h3 class="text-lg font-semibold">{{ previewModal.title }}</h3>
          <button @click="closePreview" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="p-6 overflow-auto max-h-[70vh]">
          <template v-if="previewModal.fileType === 'email'">
            <div class="bg-gray-100 p-4 rounded-lg">
              <pre class="text-xs whitespace-pre-wrap">{{ previewModal.content }}</pre>
            </div>
          </template>
          
          <template v-else-if="previewModal.fileType === 'pdf'">
            <embed 
              :src="previewModal.fileUrl" 
              type="application/pdf" 
              class="w-full h-96"
              :title="previewModal.fileName"
            />
          </template>
          
          <template v-else-if="previewModal.fileType === 'image'">
            <img 
              :src="previewModal.fileUrl" 
              :alt="previewModal.fileName"
              class="max-w-full max-h-96 mx-auto"
            />
          </template>
          
          <template v-else-if="previewModal.fileType === 'text'">
            <div class="bg-gray-100 p-4 rounded-lg">
              <pre class="text-xs whitespace-pre-wrap">{{ previewModal.content }}</pre>
            </div>
          </template>
          
          <template v-else>
            <div class="text-center py-8">
              <p class="text-gray-500 mb-4">Preview not available for this file type</p>
              <a 
                :href="previewModal.fileUrl" 
                download
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              >
                Download File
              </a>
            </div>
          </template>
        </div>
        
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
          <a 
            :href="previewModal.fileUrl" 
            download
            class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 mr-2"
          >
            Download
          </a>
          <button 
            @click="closePreview"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 rounded-xl border bg-white p-6 shadow">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Status</label>
          <select v-model="form.status" @change="filter" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-700">Search</label>
          <input 
            type="text" 
            v-model="form.search" 
            @keyup.enter="filter"
            placeholder="Search reference, company, or municipality..."
            class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"
          />
        </div>
        
        <div class="flex items-end space-x-2">
          <button 
            @click="filter" 
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
          <button 
            @click="exportToExcel" 
            class="bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition flex items-center"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Excel
          </button>
        </div>
      </div>
    </div>

    <!-- Uploads Table -->
    <div class="mt-6 overflow-hidden rounded-xl border bg-white shadow">
      <div class="px-4 py-3 border-b bg-slate-50 font-medium flex justify-between items-center">
        <span>All Uploads ({{ uploads.total }})</span>
        <Link 
          href="/uploads" 
          class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
          View Recent Uploads →
        </Link>
      </div>
      
      <div v-if="!uploads.data.length" class="p-6 text-center text-slate-600">
        No uploads found matching your criteria.
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left">
              <th class="px-4 py-3">Reference</th>
              <th class="px-4 py-3">Municipality</th>
              <th class="px-4 py-3">Company</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Email Files</th>
              <th class="px-4 py-3">System Import</th>
              <th class="px-4 py-3">Workings</th>
              <th class="px-4 py-3">Extracted Dates</th>
              <th class="px-4 py-3">Import Date</th>
              <th class="px-4 py-3">Submitted</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="upload in uploads.data" :key="upload.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3 font-mono">{{ upload.reference }}</td>
              <td class="px-4 py-3">{{ upload.municipality?.name }}</td>
              <td class="px-4 py-3">{{ upload.company?.name }}</td>
              <td class="px-4 py-3">
                <span :class="{
                  'bg-yellow-100 text-yellow-800': upload.status === 'Pending',
                  'bg-blue-100 text-blue-800': upload.status === 'Processing',
                  'bg-green-100 text-green-800': upload.status === 'Completed',
                  'bg-red-100 text-red-800': upload.status === 'Rejected'
                }" class="px-2 py-1 rounded-full text-xs">
                  {{ upload.status }}
                </span>
              </td>
              
              <td class="px-4 py-3">
                <div v-if="upload.original_file_names && upload.original_file_names.length">
                  <a 
                    v-for="(name, index) in upload.original_file_names" 
                    :key="index" 
                    href="#" 
                    @click.prevent="previewFile(upload.original_file_urls && upload.original_file_urls[index], name, getFileType(name))"
                    class="text-blue-600 hover:text-blue-800 underline block text-xs mb-1 cursor-pointer"
                    :title="`Preview ${name}`"
                  >
                    {{ name }}
                  </a>
                </div>
                <span v-else class="text-gray-500">-</span>
              </td>
              
              <td class="px-4 py-3">
                <template v-if="upload.systems_import_file_name">
                  <a 
                    href="#" 
                    @click.prevent="previewFile(upload.systems_import_file_url, upload.systems_import_file_name, getFileType(upload.systems_import_file_name))"
                    class="text-blue-600 hover:text-blue-800 underline text-xs cursor-pointer"
                    :title="`Preview ${upload.systems_import_file_name}`"
                  >
                    {{ upload.systems_import_file_name }}
                  </a>
                </template>
                <span v-else class="text-gray-500">-</span>
              </td>
              
              <td class="px-4 py-3">
                <template v-if="upload.workings_file_name">
                  <a 
                    href="#" 
                    @click.prevent="previewFile(upload.workings_file_url, upload.workings_file_name, getFileType(upload.workings_file_name))"
                    class="text-blue-600 hover:text-blue-800 underline text-xs cursor-pointer"
                    :title="`Preview ${upload.workings_file_name}`"
                  >
                    {{ upload.workings_file_name }}
                  </a>
                </template>
                <span v-else class="text-gray-500">-</span>
              </td>
              
              <td class="px-4 py-3">
                <div v-if="upload.extracted_dates && upload.extracted_dates.length">
                  <div v-for="(date, index) in upload.extracted_dates" :key="index" class="text-xs mb-1">
                    {{ date ? formatDate(date) : 'Not extracted' }}
                    <span v-if="upload.original_file_names && upload.original_file_names[index]" class="text-gray-400 text-xs ml-1">
                      ({{ upload.original_file_names[index] }})
                    </span>
                  </div>
                </div>
                <span v-else class="text-gray-500">-</span>
              </td>
              
              <td class="px-4 py-3 text-xs">{{ formatDate(upload.system_import_date) }}</td>
              <td class="px-4 py-3 text-xs">{{ formatDate(upload.submitted_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="px-4 py-3 border-t bg-slate-50 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ uploads.from }} to {{ uploads.to }} of {{ uploads.total }} results
        </div>
        <div class="flex space-x-2">
          <Link 
            v-if="uploads.prev_page_url"
            :href="uploads.prev_page_url"
            class="px-3 py-1 rounded border bg-white text-sm text-gray-700 hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link 
            v-if="uploads.next_page_url"
            :href="uploads.next_page_url"
            class="px-3 py-1 rounded border bg-white text-sm text-gray-700 hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
pre {
  font-family: 'Courier New', monospace;
  line-height: 1.4;
}
</style>