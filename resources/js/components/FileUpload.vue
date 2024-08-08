<template>
    <div class="mb-8 p-6 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
      <div class="flex items-center justify-center w-full">
        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-500 border-dashed rounded-lg cursor-pointer bg-gray-700 hover:bg-gray-600 transition duration-300 ease-in-out">
          <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="mb-2 text-sm text-gray-300"><span class="font-semibold">Click to upload</span> or drag and drop</p>
            <p class="text-xs text-gray-400">PNG, JPG, GIF up to 10MB</p>
          </div>
          <input id="dropzone-file" type="file" class="hidden" @change="handleFileUpload" />
        </label>
      </div>
      <div v-if="file" class="mt-4">
        <p class="text-sm text-gray-300">Selected file: {{ file.name }}</p>
        <button
          @click="uploadFile"
          class="mt-2 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 ease-in-out"
        >
          Upload File
        </button>
      </div>
      <p v-if="error" class="mt-2 text-sm text-red-400">{{ error }}</p>
    </div>
  </template>

  <script>
  import { ref } from 'vue'
  import axios from 'axios'

  export default {
    name: 'FileUpload',
    emits: ['file-uploaded'],
    setup(props, { emit }) {
      const file = ref(null)
      const error = ref(null)

      const handleFileUpload = (event) => {
        file.value = event.target.files[0]
        error.value = null
      }

      const uploadFile = async () => {
        if (!file.value) return

        const formData = new FormData()
        formData.append('file', file.value)

        try {
          await axios.post('/api/files', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
          emit('file-uploaded')
          file.value = null
          error.value = null
        } catch (e) {
          error.value = e.response?.data?.error || 'An error occurred while uploading the file'
          console.error('Error uploading file:', e.response?.data || e)
        }
      }

      return {
        file,
        handleFileUpload,
        uploadFile,
        error
      }
    }
  }
  </script>
