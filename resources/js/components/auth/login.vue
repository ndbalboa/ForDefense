<template>
  <div class="login-container">
    <div class="login-box">
      <div class="text-center mb-4">
        <h2 class="mt-3">Leyte Normal University</h2>
        <h4>Optimized Categorization of Records Management System</h4>
      </div>
      <form @submit.prevent="login">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
          </div>
          <input
            type="text"
            class="form-control"
            placeholder="Username"
            v-model="username"
            required
            :disabled="retryAfter > 0"
          />
        </div>
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
          </div>
          <input
            type="password"
            class="form-control"
            placeholder="Password"
            v-model="password"
            required
            :disabled="retryAfter > 0"
          />
        </div>
        <div class="text-center">
          <button
            type="submit"
            class="btn btn-primary btn-block mt-3"
            :disabled="retryAfter > 0"
          >
            Log In
          </button>
        </div>
        <div v-if="retryAfter > 0" class="alert alert-warning mt-3">
          Too many login attempts. Please wait {{ retryAfter }} seconds before trying again.
        </div>
        <div v-if="errorMessage" class="alert alert-danger mt-3">
          {{ errorMessage }}
        </div>
      </form>

      <div v-if="isTwoFactorRequired" class="two-factor-container">
        <h3>Enter Two-Factor Authentication Code</h3>
        <form @submit.prevent="verify2fa">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
            </div>
            <input
              type="text"
              class="form-control"
              placeholder="Authentication Code"
              v-model="twoFactorCode"
              required
            />
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-primary btn-block mt-3">
              Verify Code
            </button>
          </div>
          <div v-if="errorMessage" class="alert alert-danger mt-3">
            {{ errorMessage }}
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

axios.defaults.withCredentials = true;

export default {
  name: "login",
  data() {
    return {
      username: "",
      password: "",
      errorMessage: "",
      isTwoFactorRequired: false, 
      twoFactorCode: "",
      twoFactorToken: "",
      retryAfter: 0, 
      timer: null, 
    };
  },
  methods: {
    async login() {
      try {
        await axios.get("/sanctum/csrf-cookie");
        const response = await axios.post("/api/login", {
          username: this.username,
          password: this.password,
        });

        const { access_token, role, two_factor_required, two_factor_token } =
          response.data;
        if (two_factor_required) {
          this.twoFactorToken = two_factor_token;
          this.isTwoFactorRequired = true;
        } else {
          this.handleSuccessfulLogin(role, access_token);
        }
      } catch (error) {
        this.handleError(error);
      }
    },

    async verify2fa() {
      try {
        const response = await axios.post("/api/verify-2fa", {
          two_factor_token: this.twoFactorToken,
          two_factor_code: this.twoFactorCode,
        });

        const { access_token, role } = response.data;

        this.handleSuccessfulLogin(role, access_token);
      } catch (error) {
        this.handleError(error);
      }
    },

    handleSuccessfulLogin(role, token) {
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify({ role }));

      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

      if (role === "admin") {
        this.$router.push("/admin-dashboard");
      } else if (role === "secretary") {
        this.$router.push("/secretary-dashboard");
      } else if (role === "department") {
        this.$router.push("/department-dashboard");
      } else if (role === "user") {
        this.$router.push("/user-dashboard");
      }
    },

    handleError(error) {
      if (error.response && error.response.status === 429) {
        this.retryAfter = parseInt(
          error.response.headers["retry-after"] || 30
        );
        this.startRetryTimer();
      } else {
        this.errorMessage = error.response
          ? error.response.data.message
          : "An error occurred. Please try again.";
      }
    },

    startRetryTimer() {
      clearInterval(this.timer);
      this.timer = setInterval(() => {
        if (this.retryAfter > 0) {
          this.retryAfter--;
        } else {
          clearInterval(this.timer);
        }
      }, 1000);
    },
  },
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-image: url("/public/lnubacklogo.jpg");
  background-size: cover;
  background-position: center;
}

.login-box {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  width: 100%;
  opacity: 90%;
}

.btn {
  width: 100%;
}
</style>
