<template>
  <div class="sidebar p-3">
    <ul class="nav flex-column">
      <li class="nav-item">
        <router-link class="nav-link" to="/department-dashboard">
          <i class="bi bi-house-door-fill me-2"></i> Dashboard
        </router-link>
      </li>
      <li class="nav-item">
        <router-link class="nav-link" to="/department-dashboard/scan-document">
          <i class="bi bi-upload me-2"></i> Scan Document
        </router-link>
      </li>
      <li class="nav-item">
        <router-link class="nav-link" to="">
          <i class="bi bi-search me-2"></i> Search Document
        </router-link>
      </li>

      <li class="nav-item" @click.prevent="toggleDocumentsSubMenu">
        <a class="nav-link d-flex align-items-center">
          <i class="bi bi-folder2-open me-2"></i>
          <span>Documents</span>
          <i :class="['bi', isDocumentsSubMenuOpen ? 'bi-caret-down-fill' : 'bi-caret-left-fill', 'ms-auto']"></i>
        </a>

        <transition name="slide-fade">
          <ul v-show="isDocumentsSubMenuOpen" class="nav flex-column ms-3 submenu">
            <li class="nav-item">
              <router-link class="nav-link" to="/department-dashboard/department/documents/travel-order">
                <i class="bi bi-file-earmark-text me-2"></i> Travel Order
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/department-dashboard/department/documents/office-order">
                <i class="bi bi-file-earmark-text me-2"></i> Office Order
              </router-link>
            </li>
            
            <li class="nav-item">
              <router-link class="nav-link" to="/department-dashboard/department/documents/Special-order">
                <i class="bi bi-file-earmark-text me-2"></i> Special Order
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/department-dashboard/department/documents/others">
                <i class="bi bi-file-earmark-text me-2"></i> All
              </router-link>
            </li>
          </ul>
        </transition>
      </li>

      <li class="nav-item" @click.prevent="toggleSettingsSubMenu">
        <a class="nav-link d-flex align-items-center">
          <i class="bi bi-gear-fill me-2"></i>
          <span>System Settings</span>
          <i :class="['bi', isSettingsSubMenuOpen ? 'bi-caret-down-fill' : 'bi-caret-left-fill', 'ms-auto']"></i>
        </a>
        <transition name="slide-fade">
          <ul v-show="isSettingsSubMenuOpen" class="nav flex-column ms-3 submenu">
            <li class="nav-item">
              <router-link class="nav-link" to="/department-dashboard/departmentaccount">
                Settings
              </router-link>
            </li>
          </ul>
        </transition>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link" @click.prevent="confirmLogout">
          <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DepartmentSidebar',
  data() {
    return {
      isDocumentsSubMenuOpen: false,
      isSettingsSubMenuOpen: false,
    };
  },
  methods: {
    toggleDocumentsSubMenu() {
      this.isDocumentsSubMenuOpen = !this.isDocumentsSubMenuOpen;
    },
    toggleSettingsSubMenu() {
      this.isSettingsSubMenuOpen = !this.isSettingsSubMenuOpen;
    },
    async confirmLogout() {
    const confirmed = confirm('Are you sure you want to logout?');
    if (confirmed) {
      try {
        await axios.post('/api/logout', {}, { withCredentials: true });

        localStorage.removeItem('token');
        sessionStorage.removeItem('token');
        
        this.$router.push('/');
      } catch (error) {
        console.error('Logout failed:', error.response ? error.response.data : error.message);
        alert('Logout failed: ' + (error.response ? error.response.data.message : 'Server error'));
      }
    }
  },

  },
  mounted() {
    const base_url = import.meta.env.VITE_APP_URL;
    axios.defaults.baseURL = base_url; 
  }
};

</script>

<style scoped>
.sidebar {
  width: 250px;
}
.nav-link {
  display: block;
  padding: 10px 15px;
  text-decoration: none;
  color: inherit;
  transition: background-color 0.3s;
 
}

.submenu .nav-link .bi {
  font-size: 1rem;
}
.nav-link:hover {
  background-color: #f0f0f0;
}
.nav-link .bi {
  font-size: 1.5rem;
}
.submenu {
  overflow: hidden;
}
.slide-fade-enter-active, .slide-fade-leave-active {
  transition: all 0.3s ease;
}
.slide-fade-enter, .slide-fade-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>
