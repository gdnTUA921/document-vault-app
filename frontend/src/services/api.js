// src/services/api.js
import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://",
  headers: { Accept: "application/json" },
});

// Attach token from localStorage before each request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Only auto-redirect on 401 for protected endpoints (NOT /login)
api.interceptors.response.use(
  (res) => res,
  (err) => {
    const { response, config } = err;

    if (response?.status === 401) {
      const isLogin = config?.url?.endsWith("/login");
      const hasToken = Boolean(localStorage.getItem("token"));
      if (!isLogin && hasToken) {
        localStorage.removeItem("token");
        window.location.href = "/"; // redirect to login for protected routes
        // Note: we still reject to allow local catch blocks to run if needed
      }
    }
    return Promise.reject(err);
  }
);

export default api;
