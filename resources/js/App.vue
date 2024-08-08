<template>
    <div class="min-h-screen bg-gray-900 text-gray-100 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl font-extrabold text-center mb-10 text-white">
            File Manager
        </h1>
        <FileUpload @file-uploaded="refreshFiles" />
        <FileList ref="fileList" />
      </div>
    </div>
  </template>

  <script>
  import { ref } from 'vue'
  import FileList from './components/FileList.vue'
  import FileUpload from './components/FileUpload.vue'

  export default {
    name: 'App',
    components: {
      FileList,
      FileUpload
    },
    setup() {
      const fileList = ref(null)

      const refreshFiles = () => {
        fileList.value.fetchFiles()
      }

      return {
        fileList,
        refreshFiles
      }
    }
  }
  </script>
