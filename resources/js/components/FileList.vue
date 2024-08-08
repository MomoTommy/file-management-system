<template>
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
      <h2 class="text-2xl font-semibold mb-6 text-white">Uploaded Files</h2>

      <!-- Search and Sort Controls -->
      <div class="flex mb-4 space-x-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search files..."
          class="flex-grow px-4 py-2 bg-gray-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <select
          v-model="sortBy"
          class="px-4 py-2 bg-gray-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="name">Name</option>
          <option value="size">Size</option>
          <option value="created_at">Date</option>
        </select>
      </div>

      <!-- File List -->
      <ul v-if="filteredFiles.length" class="space-y-4">
        <li v-for="file in filteredFiles" :key="file.id"
            @click="selectFile(file)"
            class="flex items-center justify-between p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition duration-300 ease-in-out cursor-pointer"
            :class="{ 'border-2 border-blue-500': selectedFile && selectedFile.id === file.id }">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 flex items-center justify-center bg-gray-600 rounded-md overflow-hidden">
              <img v-if="isImage(file.type)" :src="getFileUrl(file.path)" @click.stop="previewImage(file)" class="w-full h-full object-cover cursor-pointer" :alt="file.name">
              <svg v-else-if="file.type.includes('pdf')" class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
              <svg v-else-if="file.type.includes('video')" class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
              <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-200">{{ file.name }}</p>
              <p class="text-sm text-gray-400">{{ formatFileSize(file.size) }}</p>
            </div>
          </div>
          <div class="flex space-x-2">
            <a :href="getFileUrl(file.path)" download class="px-3 py-1 text-sm text-white bg-blue-500 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">
              Download
            </a>
            <button @click.stop="deleteFile(file.id)" class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600 transition duration-300 ease-in-out">
              Delete
            </button>
          </div>
        </li>
      </ul>
      <p v-else class="text-gray-300 text-center">No files found.</p>

      <!-- File Details Panel -->
      <div v-if="selectedFile" class="mt-6 p-4 bg-gray-700 rounded-lg">
        <h3 class="text-xl font-semibold mb-2 text-white">File Details</h3>
        <p><span class="text-gray-400">Name:</span> {{ selectedFile.name }}</p>
        <p><span class="text-gray-400">Size:</span> {{ formatFileSize(selectedFile.size) }}</p>
        <p><span class="text-gray-400">Type:</span> {{ selectedFile.type }}</p>
        <p><span class="text-gray-400">Uploaded:</span> {{ formatDate(selectedFile.created_at) }}</p>
      </div>

      <!-- Image Preview Modal -->
      <div v-if="previewedImage" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closePreview">
        <div class="max-w-4xl max-h-full p-4 bg-gray-800 rounded-lg border border-gray-700">
          <img :src="getFileUrl(previewedImage.path)" :alt="previewedImage.name" class="max-w-full max-h-[80vh] object-contain">
          <p class="mt-2 text-center text-gray-300">{{ previewedImage.name }}</p>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { ref, computed, onMounted } from 'vue'
  import axios from 'axios'

  export default {
    name: 'FileList',
    setup() {
      const files = ref([])
      const previewedImage = ref(null)
      const searchQuery = ref('')
      const sortBy = ref('name')
      const selectedFile = ref(null)

      const fetchFiles = async () => {
        try {
          const response = await axios.get('/api/files')
          files.value = response.data
        } catch (error) {
          console.error('Error fetching files:', error)
        }
      }

      const deleteFile = async (id) => {
        try {
          await axios.delete(`/api/files/${id}`)
          await fetchFiles()
          if (selectedFile.value && selectedFile.value.id === id) {
            selectedFile.value = null
          }
        } catch (error) {
          console.error('Error deleting file:', error)
        }
      }

      const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes'
        const k = 1024
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
        const i = Math.floor(Math.log(bytes) / Math.log(k))
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
      }

      const isImage = (type) => {
        return type.startsWith('image/')
      }

      const previewImage = (file) => {
        if (isImage(file.type)) {
          previewedImage.value = file
        }
      }

      const closePreview = () => {
        previewedImage.value = null
      }

      const getFileUrl = (path) => {
        return `/storage/${path}`
      }

      const selectFile = (file) => {
        selectedFile.value = file
      }

      const formatDate = (dateString) => {
        return new Date(dateString).toLocaleString()
      }

      const filteredFiles = computed(() => {
        let result = files.value
        if (searchQuery.value) {
          result = result.filter(file =>
            file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
          )
        }
        return result.sort((a, b) => {
          if (sortBy.value === 'size') {
            return a.size - b.size
          } else if (sortBy.value === 'created_at') {
            return new Date(b.created_at) - new Date(a.created_at)
          } else {
            return a.name.localeCompare(b.name)
          }
        })
      })

      onMounted(fetchFiles)

      return {
        files,
        deleteFile,
        fetchFiles,
        formatFileSize,
        isImage,
        previewImage,
        previewedImage,
        closePreview,
        getFileUrl,
        searchQuery,
        sortBy,
        filteredFiles,
        selectFile,
        selectedFile,
        formatDate
      }
    }
  }
  </script>
