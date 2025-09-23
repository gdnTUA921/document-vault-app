// LoginPage.jsx
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../services/api";
import "../assets/LoginPage.css";

export default function LoginPage() {
  const nav = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPw, setShowPw] = useState(false);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    if (loading) return;
    setError("");
    setLoading(true);

    try {
      const res = await api.post("/login", { email, password });
      localStorage.setItem("token", res.data.token);
      nav("/dashboard");
    } catch (err) {
      const status = err.response?.status;
      const data = err.response?.data;

      if (status === 422 && data?.errors) {
        // Collect first validation message
        const firstField = Object.keys(data.errors)[0];
        setError(data.errors[firstField]?.[0] || "Validation error.");
      } else if (status === 429) {
        setError("Too many attempts. Please wait a minute and try again.");
      } else if (status === 401) {
        setError(data?.message || "Invalid email or password.");
      } else if (!err.response) {
        setError("Network error. Please check your server or connection.");
      } else {
        setError(data?.message || "Something went wrong.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-wrapper">
      <form onSubmit={submit} className="login-card">
        <h1 className="login-title">Document Vault App</h1>

        {error && <p className="login-error">{error}</p>}

        <label className="login-label">
          <b>Email</b>
          <input
            className="login-input"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            autoComplete="username"
            required
          />
        </label>

        <label className="login-label">
          <b>Password</b>
          <div className="pw-wrapper">
            <input
              className="login-input"
              type={showPw ? "text" : "password"}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
              required
            />
            <button
              type="button"
              onClick={() => setShowPw((s) => !s)}
              className="pw-toggle"
            >
              {showPw ? "Hide" : "Show"}
            </button>
          </div>
        </label>

        <button className="login-button" type="submit" disabled={loading}>
          {loading ? "Signing in…" : "Sign in"}
        </button>
      </form>
    </div>
  );
}
