<template>
  <div>
    <h1>Backup and Restore</h1>
    <div class="backup-restore-container">
      <div class="backup-section">
        <h3>Create Backup</h3>
        <button @click="createBackup" :disabled="isBackingUp">
          <i class="fa fa-database"></i> Create Backup
        </button>
      </div>

      <div class="restore-section">
        <h3>Restore Backup</h3>
        <input type="file" @change="handleFileUpload" />
        <button @click="restoreBackup" :disabled="isRestoring || !backupFile">
          <i class="fa fa-refresh"></i> Restore Backup
        </button>
      </div>
    </div>

    <div>
      <h3>Backup Files</h3>
      <table class="backup-table">
        <thead>
          <tr>
            <th>Backup File Name</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="backup in backups" :key="backup">
            <td>{{ backup }}</td>
            <td>
              <button @click="downloadBackup(backup)">
                <i class="fa fa-download"></i> Download
              </button>
              <button @click="deleteBackup(backup)">
                <i class="fa fa-trash"></i> Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="message" :class="messageClass">
      {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isBackingUp: false,
      isRestoring: false,
      backupFile: null,
      message: '',
      messageClass: '',
      backups: [] 
    };
  },
  created() {
    this.getBackups();
  },
  methods: {
    createBackup() {
      this.isBackingUp = true;
      this.message = 'Creating backup...';
      this.messageClass = 'alert-info';

      axios.post('/api/admin/backup/create')
        .then(response => {
          this.message = response.data.message;
          this.messageClass = 'alert-success';
          this.getBackups(); 
        })
        .catch(error => {
          this.message = error.response.data.message;
          this.messageClass = 'alert-danger';
        })
        .finally(() => {
          this.isBackingUp = false;
        });
    },

    handleFileUpload(event) {
      this.backupFile = event.target.files[0];
    },

    restoreBackup() {
      if (!this.backupFile) {
        this.message = 'Please select a backup file.';
        this.messageClass = 'alert-warning';
        return;
      }

      this.isRestoring = true;
      const formData = new FormData();
      formData.append('backup_file', this.backupFile);

      this.message = 'Restoring backup...';
      this.messageClass = 'alert-info';

      axios.post('/api/admin/backup/restore', formData)
        .then(response => {
          this.message = response.data.message;
          this.messageClass = 'alert-success';
        })
        .catch(error => {
          this.message = error.response.data.message;
          this.messageClass = 'alert-danger';
        })
        .finally(() => {
          this.isRestoring = false;
        });
    },

    getBackups() {
      axios.get('/api/admin/backup/list')
        .then(response => {
          this.backups = response.data.backups;
        })
        .catch(error => {
          this.message = 'Failed to load backups.';
          this.messageClass = 'alert-danger';
        });
    },

    downloadBackup(backup) {
      axios.get(`/api/admin/backup/download/${backup}`)
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', backup); 
          document.body.appendChild(link);
          link.click();
        })
        .catch(error => {
          this.message = 'Failed to download backup.';
          this.messageClass = 'alert-danger';
        });
    },

    deleteBackup(backup) {
      if (confirm(`Are you sure you want to delete the backup: ${backup}?`)) {
        axios.delete(`/api/admin/backup/delete/${backup}`)
          .then(response => {
            this.message = response.data.message;
            this.messageClass = 'alert-success';
            this.getBackups(); 
          })
          .catch(error => {
            this.message = 'Failed to delete backup.';
            this.messageClass = 'alert-danger';
          });
      }
    }
  }
};
</script>

<style scoped>
.alert-info {
  color: blue;
}
.alert-success {
  color: green;
}
.alert-danger {
  color: red;
}
.alert-warning {
  color: orange;
}

button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.backup-restore-container {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  margin-top: 20px;
}

.backup-section,
.restore-section {
  flex: 1;
}

/* Table Styles */
.backup-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.backup-table th,
.backup-table td {
  padding: 10px;
  text-align: left;
  border: 1px solid #ddd;
}

.backup-table th {
  background-color: navy;
  color: white;
}

.backup-table button {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
  margin-right: 10px; 
}

.backup-table button:hover {
  background-color: #45a049;
}

/* Icons */
button i {
  margin-right: 5px;
}
</style>
