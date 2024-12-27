<template>
  <div class="container">
    <h1 class="mt-3">Deactivated Employees</h1>

    <div class="d-flex justify-content-end mb-3">
      <div class="input-group" style="width: 300px;">
        <input
          type="text"
          v-model="searchTerm"
          placeholder="Search by name"
          class="form-control"
          @input="filterEmployees"
        />
        <div class="input-group-append">
          <span class="input-group-text">
            <i class="fa fa-search"></i>
          </span>
        </div>
      </div>
    </div>

    <nav aria-label="Page navigation" class="d-flex justify-content-end">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" @click.prevent="prevPage">
            <i class="fas fa-chevron-left"></i> Previous
          </a>
        </li>
        <li
          class="page-item"
          v-for="page in totalPages"
          :key="page"
          :class="{ active: currentPage === page }"
        >
          <a class="page-link" @click.prevent="fetchDeactivatedEmployees(page)">
            {{ page }}
          </a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" @click.prevent="nextPage">
            Next <i class="fas fa-chevron-right"></i>
          </a>
        </li>
      </ul>
    </nav>

    <table v-if="filteredEmployees.length > 0" class="table table-bordered">
      <thead class="thead-dark">
        <tr>
          <th>Last Name</th>
          <th>First Name</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in paginatedEmployees" :key="employee.id">
          <td>{{ employee.lastName }}</td>
          <td>{{ employee.firstName }}</td>
          <td>deactivated</td>
          <td>
            <button
              @click="activateUser(employee.id)"
              class="btn btn-success mr-2"
            >
              <i class="fas fa-user-check"></i> Activate
            </button>
            <button
              @click="deleteEmployee(employee.id)"
              class="btn btn-danger"
            >
              <i class="fas fa-trash"></i> Delete Permanently
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>No deactivated employees found.</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      employees: [],
      searchTerm: "",
      currentPage: 1,
      pageSize: 5,
    };
  },
  computed: {
    filteredEmployees() {
      if (this.searchTerm) {
        return this.employees.filter((employee) => {
          const fullName = `${employee.firstName} ${employee.lastName}`.toLowerCase();
          return fullName.includes(this.searchTerm.toLowerCase());
        });
      }
      return this.employees;
    },
    totalPages() {
      return Math.ceil(this.filteredEmployees.length / this.pageSize);
    },
    paginatedEmployees() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredEmployees.slice(start, start + this.pageSize);
    },
  },
  methods: {
    async fetchDeactivatedEmployees() {
      try {
        const response = await axios.get("/api/admin/employees/no-user-or-deleted");
        this.employees = response.data;
      } catch (error) {
        console.error("Error fetching deactivated employees:", error);
        alert("Failed to fetch deactivated employees.");
      }
    },
    async activateUser(employeeId) {
      try {
        await axios.post(`/api/admin/employees/${employeeId}/restore`);
        alert("Employee information restored successfully.");
        this.fetchDeactivatedEmployees();
      } catch (error) {
        console.error("Error activating user account or restoring employee info:", error);
        alert("Failed to restore employee information.");
      }
    },
    async deleteEmployee(employeeId) {
      const confirmDelete = confirm(
        "Are you sure you want to delete this employee permanently?"
      );
      if (confirmDelete) {
        try {
          await axios.delete(`/api/admin/employees/destroy/${employeeId}`);
          alert("Employee deleted permanently.");
          this.fetchDeactivatedEmployees(); 
        } catch (error) {
          console.error("Error deleting employee permanently:", error);
          alert("Failed to delete employee permanently.");
        }
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
    filterEmployees() {
      this.currentPage = 1;
    },
  },
  mounted() {
    this.fetchDeactivatedEmployees();
  },
};
</script>
<style scoped>
.table {
  width: 100%;
  margin-top: 20px;
}
.table th {
  background-color: navy;
  color: white;
}
.table td,
.table th {
  text-align: left;
  padding: 10px;
}

.btn {
  padding: 5px 10px;
  margin-right: 10px;
}
.btn:last-child {
  margin-right: 0; 
}
.btn i {
  margin-right: 5px; 
}
.pagination {
  margin-top: 20px;
  display: flex;
  align-items: center;
}
.pagination .page-item.active .page-link {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}
.pagination .page-link {
  color: #007bff;
  padding: 8px 12px;
  text-decoration: none;
}
.pagination .page-link:hover {
  background-color: #e9ecef;
}
.pagination .page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
  background-color: transparent;
  border-color: transparent;
}
.input-group {
  width: 300px;
}
</style>

