<template>
  <div>
    <h1>Budget Circulars</h1>

    <!-- Search Bar and Top Bar Container -->
    <div class="search-and-top-bar">
      <!-- Search Bar -->
      <div class="search-bar-container">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search by document no, subject, or employee name..."
          class="search-bar"
        />
      </div>

      <!-- Results Counter and Pagination -->
      <div class="top-bar">
        <div class="results-counter">
          Showing {{ paginatedDocuments.length }} out of {{ filteredDocuments.length }} results
        </div>
        <div class="pagination">
          <button @click="prevPage" :disabled="currentPage === 1">❮</button>
          <button
            v-for="page in visiblePages"
            :key="page"
            :class="{ active: currentPage === page }"
            @click="setPage(page)"
            v-if="page !== '...'"
          >
            {{ page }}
          </button>
          <span v-else>...</span>
          <button @click="nextPage" :disabled="currentPage === totalPages">❯</button>
        </div>
      </div>
    </div>

    <!-- Document Table -->
    <table>
      <thead>
        <tr>
          <th>Document No</th>
          <th>Date Issued</th>
          <th>Subject</th>
          <th>Employee Names</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="document in paginatedDocuments" :key="document.id" @click="viewDocument(document.id)" class="clickable-row">
          <td>{{ document.document_no }}</td>
          <td>{{ document.date_issued }}</td>
          <td>{{ document.subject }}</td>
          <td>
            <ul>
              <li v-for="(name, index) in document.employee_names.slice(0, 5)" :key="index">
                {{ name }}
              </li>
              <li v-if="document.employee_names.length > 5">...</li>
            </ul>
          </td>

        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      documents: [],
      searchQuery: "", 
      currentPage: 1, 
      perPage: 5, 
    };
  },
  computed: {

    sortedDocuments() {
      return [...this.documents].sort((a, b) => new Date(b.date_issued) - new Date(a.date_issued));
    },
    filteredDocuments() {
      const query = this.searchQuery.toLowerCase();
      return this.sortedDocuments.filter(
        (document) =>
          document.document_no?.toLowerCase().includes(query) ||
          document.subject?.toLowerCase().includes(query) ||
          document.employee_names?.some((name) =>
            name.toLowerCase().includes(query)
          )
      );
    },
    paginatedDocuments() {
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      return this.filteredDocuments.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredDocuments.length / this.perPage);
    },
    visiblePages() {
      const total = this.totalPages;
      const current = this.currentPage;

      if (total <= 5) {
        return Array.from({ length: total }, (_, i) => i + 1);
      } else if (current <= 3) {
        return [1, 2, 3, 4, "...", total];
      } else if (current > total - 3) {
        return [1, "...", total - 3, total - 2, total - 1, total];
      } else {
        return [1, "...", current - 1, current, current + 1, "...", total];
      }
    },
  },
  mounted() {
    this.fetchDocuments(6);
  },
  methods: {
    async fetchDocuments(documentTypeId) {
      try {
        const response = await axios.get(`/api/admin/list/documents/${documentTypeId}`);
        this.documents = response.data;
      } catch (error) {
        console.error("Error fetching documents:", error);
      }
    },
    setPage(page) {
      if (page !== "...") {
        this.currentPage = page;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    viewDocument(documentId) {
      this.$router.push(`/admin-dashboard/documents/${documentId}`);
    }
  },
};
</script>


<style scoped>
table {
  margin-top: 15px;
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}

th {
  background-color: navy; 
  color: white;
}


.search-and-top-bar {
  display: flex;
  flex-direction: column;
  gap: 10px; 
}

.search-bar-container {
  align-self: flex-end; 
  position: relative;
  width: 300px;
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
}

.search-bar {
  width: 100%;
  padding: 8px 12px 8px 30px; 
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.results-counter {
  font-size: 14px;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 5px;
}

.pagination button {
  padding: 8px 12px;
  background-color: #f4f4f4;
  color: #333;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  min-width: 36px;
}

.pagination button.active {
  background-color: #007bff;
  color: white;
}

.pagination button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.pagination span {
  padding: 8px 12px;
  color: #888;
}

.clickable-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.clickable-row:hover {
  background-color: #f0f0f0;
}
</style>
