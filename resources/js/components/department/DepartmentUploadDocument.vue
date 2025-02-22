<template>
  <div class="upload-document">
    <h1>Upload Document</h1>
    <div class="form-container">
      <form @submit.prevent="submitDocument">
        <!-- Document Type -->
        <div class="form-group">
          <label for="documentType">Document Type</label>
          <select v-model="form.documentTypeId" id="documentType" class="form-control" required>
            <option value="" disabled>Select Document Type</option>
            <option v-for="type in documentTypes" :key="type.id" :value="type.id">
              {{ type.document_type }}
            </option>
          </select>
        </div>

        <!-- File Upload -->
        <div class="form-group">
          <label for="file">File</label>
          <input
            type="file"
            id="file"
            class="form-control"
            @change="onFileChange"
            required
          />
        </div>

        <!-- Additional Fields -->
        <div v-if="form.documentTypeId">
          <div class="form-group">
            <label for="documentNo">Document Number</label>
            <input
              type="text"
              id="documentNo"
              class="form-control"
              v-model="form.documentNo"
            />
          </div>
          <div class="form-group">
            <label for="seriesNo">Series Number</label>
            <input
              type="text"
              id="seriesNo"
              class="form-control"
              v-model="form.seriesNo"
            />
          </div>
          <div class="form-group">
            <label for="dateIssued">Date Issued</label>
            <input
              type="date"
              id="dateIssued"
              class="form-control"
              v-model="form.dateIssued"
            />
          </div>
          <div class="form-group">
            <label for="fromDate">From Date</label>
            <input
              type="date"
              id="fromDate"
              class="form-control"
              v-model="form.fromDate"
            />
          </div>
          <div class="form-group">
            <label for="toDate">To Date</label>
            <input
              type="date"
              id="toDate"
              class="form-control"
              v-model="form.toDate"
            />
          </div>

          <!-- Conditional Venue and Destination -->
          <div v-if="isTravelOrder">
            <div class="form-group">
              <label for="venue">Venue</label>
              <input
                type="text"
                id="venue"
                class="form-control"
                v-model="form.venue"
              />
            </div>
            <div class="form-group">
              <label for="destination">Destination</label>
              <input
                type="text"
                id="destination"
                class="form-control"
                v-model="form.destination"
              />
            </div>
          </div>

          <!-- Employee Names -->
          <div class="form-group">
            <label>Employee Names:</label>
            <transition-group name="fade">
              <div
                v-for="(employee, index) in form.employeeNames"
                :key="index"
                class="name-group"
              >
                <input
                  type="text"
                  v-model="form.employeeNames[index]"
                  class="form-control"
                  placeholder="Enter employee name"
                />
                <button
                  type="button"
                  class="btn-remove"
                  @click="removeEmployee(index)"
                >
                  <i class="fas fa-minus-circle"></i>
                </button>
              </div>
            </transition-group>
            <button
              type="button"
              class="btn-add"
              @click="addEmployee"
            >
              <i class="fas fa-plus-circle"></i>
            </button>
          </div>

          <!-- Student Names -->
          <div class="form-group">
            <label>Student Names:</label>
            <transition-group name="fade">
              <div
                v-for="(student, index) in form.studentNames"
                :key="index"
                class="name-group"
              >
                <input
                  type="text"
                  v-model="form.studentNames[index]"
                  class="form-control"
                  placeholder="Enter student name"
                />
                <button
                  type="button"
                  class="btn-remove"
                  @click="removeStudent(index)"
                >
                  <i class="fas fa-minus-circle"></i>
                </button>
              </div>
            </transition-group>
            <button
              type="button"
              class="btn-add"
              @click="addStudent"
            >
              <i class="fas fa-plus-circle"></i>
            </button>
          </div>

          <!-- Subject and Description -->
          <div class="form-group">
            <label for="subject">Subject</label>
            <input
              type="text"
              id="subject"
              class="form-control"
              v-model="form.subject"
            />
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea
              id="description"
              class="form-control"
              v-model="form.description"
            ></textarea>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
      </form>
    </div>
  </div>
</template>

<script>
// Importing Font Awesome for icons
import axios from "axios";

export default {
  data() {
    return {
      documentTypes: [],
      form: {
        documentTypeId: "",
        documentNo: "",
        seriesNo: "",
        dateIssued: "",
        fromDate: "",
        toDate: "",
        venue: "",
        destination: "",
        subject: "",
        description: "",
        employeeNames: [""],
        studentNames: [""],
        file: null,
      },
    };
  },
  computed: {
    isTravelOrder() {
      const selectedType = this.documentTypes.find(
        (type) => type.id === this.form.documentTypeId
      );
      return selectedType?.document_type === "Travel Order";
    },
  },
  methods: {
    async fetchDocumentTypes() {
      try {
        const response = await axios.get("/api/admin/upload/document-types");
        this.documentTypes = response.data;
      } catch (error) {
        console.error("Error fetching document types:", error);
      }
    },
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        const validTypes = [
          "application/pdf",
          "application/msword",
          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
          "image/jpeg",
          "image/png",
        ];
        if (!validTypes.includes(file.type)) {
          alert(
            "Invalid file type. Please upload a PDF, Word document, or image."
          );
          return;
        }
        if (file.size > 10 * 1024 * 1024) {
          alert("File size exceeds 10 MB. Please upload a smaller file.");
          return;
        }
        this.form.file = file;
      }
    },
    addEmployee() {
      this.form.employeeNames.push("");
    },
    removeEmployee(index) {
      this.form.employeeNames.splice(index, 1);
    },
    addStudent() {
      this.form.studentNames.push("");
    },
    removeStudent(index) {
      this.form.studentNames.splice(index, 1);
    },
    async submitDocument() {
      const formData = new FormData();
      Object.entries(this.form).forEach(([key, value]) => {
        if (Array.isArray(value)) {
          value.filter((v) => v.trim() !== "").forEach((v) =>
            formData.append(`${key}[]`, v)
          );
        } else {
          formData.append(key, value);
        }
      });

      try {
        const response = await axios.post(
          "/api/admin/upload/documents",
          formData
        );
        alert("Document uploaded successfully!");
        this.resetForm();
      } catch (error) {
        alert("Error uploading document.");
        console.error(error);
      }
    },
    resetForm() {
      this.form = {
        documentTypeId: "",
        documentNo: "",
        seriesNo: "",
        dateIssued: "",
        fromDate: "",
        toDate: "",
        venue: "",
        destination: "",
        subject: "",
        description: "",
        employeeNames: [""],
        studentNames: [""],
        file: null,
      };
    },
  },
  created() {
    this.fetchDocumentTypes();
  },
};
</script>
<style scoped>
.upload-document {
  max-width: 800px;
  margin: 50px auto;
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 10px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  animation: fadeIn 1s ease;
}

.form-container {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1rem;
}

.name-group {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.name-group input {
  flex: 1;
  margin-right: 10px;
}

.btn-add,
.btn-remove {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  color: #007bff;
  vertical-align: middle;
}

.btn-add {
  margin-top: 10px;
  display: block;
  text-align: center;
}

.btn-remove {
  margin-left: 10px;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
