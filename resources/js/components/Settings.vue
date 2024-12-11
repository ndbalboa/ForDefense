<template>
  <div class="backup">
    <h1>Backup & Restore</h1>
    <p class="settings-header">Manage your database backups and restores efficiently.</p>

    <button @click="createBackup" class="action-button create-backup">
      <span class="icon">💾</span> Create Backup
    </button>

    <h2>Available Backups</h2>
    <ul class="backup-list">
      <li v-for="backup in backups" :key="backup" class="backup-item">
        <span>{{ backup }}</span>
        <div class="button-group">
          <button @click="restoreBackup(backup)" class="action-button restore-backup">
            <span class="icon">🔄</span> Restore
          </button>
          <a :href="`/api/admin/download/${backup}`" class="action-button download-backup" download>
            <span class="icon">⬇️</span> Download
          </a>
        </div>
      </li>
    </ul>
    <!-- Restore button always visible -->
    <button @click="restoreBackup(null)" class="action-button restore-backup" v-if="!backups.length">
      <span class="icon">🔄</span> Restore Default Data
    </button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      backups: [],
    };
  },
  methods: {
    fetchBackups() {
      axios.get('/api/admin/backups').then((response) => {
        this.backups = response.data;
      });
    },
    createBackup() {
      axios.get('/api/admin/backup', { responseType: 'blob' }).then((response) => {
        // Automatically download the file after creation
        const blob = new Blob([response.data], { type: 'application/octet-stream' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'backup.zip'; // Customize the file name if needed
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        alert('Backup created and downloaded successfully!');
        this.fetchBackups();
      }).catch(() => {
        alert('Failed to create backup.');
      });
    },
    restoreBackup(backup) {
      if (backup === null) {
        alert('Restoring default data.');
        // Implement the logic to restore default data here, if needed.
      } else {
        axios
          .post('/api/admin/restore', { filename: backup })
          .then(() => alert('Backup restored successfully!'))
          .catch(() => alert('Failed to restore backup.'));
      }
    },
  },
  mounted() {
    this.fetchBackups();
  },
};
</script>

<style>
.backup {
  max-width: 600px;
  margin: 20px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}

.settings-header {
  text-align: center;
  margin-bottom: 20px;
  color: #555;
  font-size: 16px;
}

h2 {
  margin-top: 20px;
  color: #555;
}

.action-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  color: #fff;
  background-color: #28a745;
  width: 100%;
  margin-bottom: 10px;
  transition: background-color 0.3s;
}

.action-button.create-backup {
  background-color: #007bff;
}

.action-button.restore-backup {
  background-color: #dc3545;
}

.action-button.download-backup {
  background-color: #17a2b8;
}

.action-button:hover {
  opacity: 0.8;
}

.icon {
  margin-right: 8px;
}

.backup-list {
  list-style: none;
  padding: 0;
}

.backup-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding: 10px;
  border-bottom: 1px solid #ddd;
  background-color: #fff;
  border-radius: 4px;
}

.backup-item span {
  font-size: 14px;
  color: #333;
}

.button-group {
  display: flex;
  gap: 10px;
}

@media (max-width: 600px) {
  .backup {
    padding: 15px;
  }

  .action-button {
    width: 100%;
    font-size: 12px;
  }
}
</style>
