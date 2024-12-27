<template>
  <h2>{{ document && document.document_type ? document.document_type.document_type : 'Travel Order' }}</h2>

  <!-- Action Buttons Section -->
  <div v-if="document" class="action-buttons">
    <div class="buttons-container">
      <button @click="viewFile" v-if="document.file_path" class="btn-primary">
        <i class="fas fa-eye"></i> View File
      </button>
      <button v-if="!isEditing" @click="editDocument" class="btn-secondary">
        <i class="fas fa-edit"></i> Edit Document
      </button>
      <button v-if="isEditing" @click="saveDocument" class="btn-primary">
        <i class="fas fa-save"></i> Save Changes
      </button>
      <button @click="deleteDocument" class="btn-danger">
        <i class="fas fa-trash"></i> Delete Document
      </button>
    </div>
  </div>

  <!-- Document Details and Preview Section -->
  <div class="document-container">
    <!-- Document Details Section -->
    <div class="document-details">
      <div class="details-grid">
        <label for="doc-no"><strong>Document No:</strong></label>
        <input id="doc-no" v-model="document.document_no" :disabled="!isEditing" />

        <label for="series-no"><strong>Series Year:</strong></label>
        <input id="series-no" v-model="document.series_no" :disabled="!isEditing" />

        <label for="date-issued"><strong>Date Issued:</strong></label>
        <input id="date-issued" v-model="document.date_issued" :disabled="!isEditing" />

        <label for="inclusive-date"><strong>Inclusive Date:</strong></label>
        <input id="inclusive-date" v-model="document.inclusive_date" :disabled="!isEditing" />

        <label for="subject"><strong>Subject:</strong></label>
        <textarea 
          id="subject" 
          v-model="document.subject" 
          @input="resizeTextarea($event)" 
          :disabled="!isEditing"
          class="resizable-textarea"
        ></textarea>

        <label for="description"><strong>Description:</strong></label>
        <textarea 
          id="description" 
          v-model="document.description" 
          @input="resizeTextarea($event)" 
          :disabled="!isEditing"
          class="resizable-textarea"
        ></textarea>

        <label for="venue"><strong>Venue:</strong></label>
        <input id="venue" v-model="document.venue" :disabled="!isEditing" />

        <label for="series-no"><strong>Destination:</strong></label>
        <input id="destination" v-model="document.destination" :disabled="!isEditing" />

        <label for="sender"><strong>From:</strong></label>
        <input id="sender" v-model="document.sender" :disabled="!isEditing" />

        <label for="employee-names"><strong>Employee Names:</strong></label>
        <textarea 
          id="employee-names" 
          v-model="employeeNames" 
          @input="resizeTextarea($event)" 
          :disabled="!isEditing"
          class="resizable-textarea"
        ></textarea>
      </div>
    </div>

    <!-- Document Preview Section -->
    <div class="document-preview">
      <h3>Document Preview</h3>
      <div class="preview-container">
        <div v-if="isImageFile">
          <img :src="getFileUrl(document.file_path)" alt="Document Image" width="100%" />
        </div>
        <div v-else>
          <iframe 
            :src="getFileUrl(document.file_path)"
            width="100%"
            height="500px"
            frameborder="0"
            title="Document Preview"
          ></iframe>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      document: null,
      error: null,
      isEditing: false,
      base_url: import.meta.env.VITE_APP_URL, 
    };
  },
  computed: {
    employeeNames() {
      return this.document && this.document.employee_names
        ? this.document.employee_names.join('\n')
        : '';
    },
    isImageFile() {
      return this.document && (this.document.file_path.endsWith('.jpg') || this.document.file_path.endsWith('.jpeg') || this.document.file_path.endsWith('.png'));
    },
  },
  methods: {
    async fetchDocumentDetails() {
      const documentId = this.$route.params.id;

      if (!documentId) {
        this.error = 'Invalid document ID.';
        return;
      }

      try {
        const response = await axios.get(`/api/admin/documents/${documentId}`);
        this.document = response.data;
        this.$nextTick(() => {
          this.autoResizeTextareas();
        });
      } catch (error) {
        console.error('Error fetching document details:', error);
        if (error.response && error.response.status === 404) {
          this.error = 'Document not found.';
        } else {
          this.error = 'Failed to load document details. Please try again later.';
        }
      }
    },
    editDocument() {
      this.isEditing = true;
    },
    async saveDocument() {
      try {
        const employeeNamesArray = this.employeeNames.split('\n').map(name => name.trim());
        this.document.employee_names = employeeNamesArray;

        await axios.put(`/api/admin/documents/update/${this.document.id}`, this.document);
        alert('Document updated successfully.');
        this.isEditing = false;
      } catch (error) {
        console.error('Error saving document:', error);
        alert('Failed to save document. Please try again later.');
      }
    },
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      const month = (d.getMonth() + 1).toString().padStart(2, '0');
      const day = d.getDate().toString().padStart(2, '0');
      const year = d.getFullYear();
      return `${month}-${day}-${year}`;
    },
    getFileUrl(filePath) {
      if (!filePath) {
        console.error('File path is undefined');
        return '';
      }
      return `${this.base_url}${filePath}`;
    },
    viewFile() {
      const fileUrl = this.getFileUrl(this.document.file_path);
      if (fileUrl) {
        window.open(fileUrl, '_blank');
      } else {
        console.error('Cannot view file: URL is undefined');
        alert('File is not available.');
      }
    },
    async deleteDocument() {
      if (confirm('Are you sure you want to delete this document?')) {
        try {
          await axios.delete(`/api/admin/delete/documents/${this.document.id}`);
          alert('Document deleted successfully.');
          this.$router.push('/documents');
        } catch (error) {
          console.error('Error deleting document:', error);
          alert('Failed to delete document. Please try again later.');
        }
      } else {
        alert('Document deletion canceled.');
      }
    },
    autoResizeTextareas() {
      this.$nextTick(() => {
        const textareas = this.$refs.textareas || [];
        textareas.forEach(textarea => {
          textarea.style.height = 'auto';
          textarea.style.height = `${textarea.scrollHeight}px`;
        });
      });
    },
    resizeTextarea(event) {
      const textarea = event.target;
      textarea.style.height = 'auto';
      textarea.style.height = `${textarea.scrollHeight}px`;
    },
  },
  mounted() {
    axios.defaults.baseURL = this.base_url;
    this.fetchDocumentDetails();
  },
  watch: {
    document() {
      this.$nextTick(() => {
        this.autoResizeTextareas();
      });
    },
  },
};
</script>

<style scoped>
.document-container {
  display: flex;
  gap: 20px;
  justify-content: space-between;
  margin: 20px auto;
}

.document-details,
.document-preview {
  flex: 1;
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: left;
  font-family: 'Arial', sans-serif;
  color: #333;
  margin-bottom: 20px;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 10px;
}

.details-grid label {
  font-weight: bold;
}

.details-grid input,
.details-grid textarea {
  font-size: 14px;
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
  background-color: #f9f9f9;
  resize: none;
}

.details-grid textarea {
  height: auto;
  min-height: 40px;
  overflow: hidden;
  resize: vertical;
}

#description {
  height: 200px;
}

.btn-primary,
.btn-danger,
.btn-secondary {
  display: inline-block;
  padding: 10px 20px;
  font-size: 14px;
  color: white;
  text-align: center;
  border-radius: 5px;
  cursor: pointer;
}

.btn-primary {
  background-color: #007bff;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-danger {
  background-color: #dc3545;
}

.btn-danger:hover {
  background-color: #c82333;
}

.btn-secondary {
  background-color: #6c757d;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.error-message {
  color: red;
  text-align: center;
  margin-top: 20px;
}

.inclusive-date {
  grid-column: span 2;
  display: flex;
  align-items: center;
  gap: 120px;
}

.preview-container {
  overflow: hidden;
}

.resizable-textarea {
  min-height: 50px;
}

.buttons-container {
  display: flex;
  justify-content: flex-end;
  gap: 20px;  
}
</style>
