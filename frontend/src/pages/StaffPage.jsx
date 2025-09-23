import { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import api from "../services/api.js";
import "../assets/AdminPage.css"; // reuse same CSS styles

export default function StaffPanelPage() {
  const location = useLocation();
  const params = new URLSearchParams(location.search);
  const defaultTab = params.get("tab") || "users";

  const [activeTab, setActiveTab] = useState(defaultTab);

  // State
  const [users, setUsers] = useState([]);
  const [files, setFiles] = useState([]);
  const [staffProfile, setStaffProfile] = useState(null);
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  // Search state
  const [userQuery, setUserQuery] = useState("");
  const [fileQuery, setFileQuery] = useState("");

  // --- Fetch helpers ---
  const fetchUsers = async (search = "") => {
    try {
      const res = await api.get(
        `/staff/users?search=${encodeURIComponent(search)}`
      );
      setUsers(res.data.data || res.data || []);
    } catch (e) {
      console.error("Failed to fetch department users", e);
    }
  };

  const fetchFiles = async (search = "") => {
    try {
      if (search.trim()) {
        const res = await api.get(`/search?q=${encodeURIComponent(search)}`);
        setFiles(res.data.data || res.data || []);
      } else {
        const res = await api.get("/staff/files");
        setFiles(res.data.data || res.data || []);
      }
    } catch (e) {
      console.error("Failed to fetch department files", e);
      setFiles([]);
    }
  };

  const fetchProfile = async () => {
    try {
      const res = await api.get("/me");
      setStaffProfile(res.data);
    } catch (e) {
      console.error("Failed to fetch staff profile", e);
    }
  };

  useEffect(() => {
    if (activeTab === "users") fetchUsers();
    if (activeTab === "files") fetchFiles();
    if (activeTab === "settings") fetchProfile();
  }, [activeTab]);

  // --- Actions ---
  const updatePassword = async (e) => {
    e.preventDefault();
    if (password !== confirmPassword) {
      alert("Passwords do not match!");
      return;
    }
    try {
      await api.put(`/staff/users/${staffProfile.id}/password`, { password });
      alert("Password updated!");
      setPassword("");
      setConfirmPassword("");
    } catch (e) {
      alert("Password update failed");
    }
  };

  const handleDownload = async (fileId, fileTitle) => {
    try {
      const res = await api.get(`/files/${fileId}/download`, {
        responseType: "blob",
      });

      let filename = fileTitle || `file-${fileId}`;
      if (res.headers["x-file-name"]) {
        filename = decodeURIComponent(res.headers["x-file-name"]);
      } else {
        const disposition = res.headers["content-disposition"];
        if (disposition && disposition.indexOf("filename=") !== -1) {
          filename = disposition
            .split("filename=")[1]
            .trim()
            .replace(/(^"|"$)/g, "");
        }
      }

      const blob = new Blob([res.data], {
        type: res.headers["content-type"] || "application/octet-stream",
      });

      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", filename);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (e) {
      alert("Download failed");
    }
  };

  // --- Render ---
  return (
    <div className="admin-wrap">
      <h2 className="admin-title">🛠️ Staff Panel</h2>

      {/* Tabs */}
      <div className="admin-tabs">
        <button
          onClick={() => setActiveTab("users")}
          className={activeTab === "users" ? "active" : ""}
        >
          Users
        </button>
        <button
          onClick={() => setActiveTab("files")}
          className={activeTab === "files" ? "active" : ""}
        >
          Files
        </button>
        <button
          onClick={() => setActiveTab("settings")}
          className={activeTab === "settings" ? "active" : ""}
        >
          My Account
        </button>
      </div>

      <div className="admin-content">
        {/* Users */}
        {activeTab === "users" && (
          <div>
            <h3>👥 Department Users</h3>

            {/* Search form */}
            <form
              onSubmit={(e) => {
                e.preventDefault();
                fetchUsers(userQuery);
              }}
              className="admin-form"
              style={{ marginBottom: "12px" }}
            >
              <input
                type="text"
                placeholder="Search users…"
                value={userQuery}
                onChange={(e) => setUserQuery(e.target.value)}
                className="mf-input"
              />
              <button type="submit" className="mf-btn ghost">Search</button>
              {userQuery && (
                <button
                  type="button"
                  className="mf-btn danger"
                  onClick={() => {
                    setUserQuery("");
                    fetchUsers();
                  }}
                >
                  Clear
                </button>
              )}
            </form>

            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Department</th>
                </tr>
              </thead>
              <tbody>
                {users.map((u) => (
                  <tr key={u.id}>
                    <td>{u.id}</td>
                    <td>
                      {u.first_name} {u.last_name}
                    </td>
                    <td>{u.email}</td>
                    <td>{u.role}</td>
                    <td>{u.department?.name || "—"}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Files */}
        {activeTab === "files" && (
          <div>
            <h3>📂 Department Files</h3>

            {/* Search form */}
            <form
              onSubmit={(e) => {
                e.preventDefault();
                fetchFiles(fileQuery);
              }}
              className="admin-form"
              style={{ marginBottom: "12px" }}
            >
              <input
                type="text"
                placeholder="Search files…"
                value={fileQuery}
                onChange={(e) => setFileQuery(e.target.value)}
                className="mf-input"
              />
              <button type="submit" className="mf-btn ghost">Search</button>
              {fileQuery && (
                <button
                  type="button"
                  className="mf-btn danger"
                  onClick={() => {
                    setFileQuery("");
                    fetchFiles();
                  }}
                >
                  Clear
                </button>
              )}
            </form>

            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Owner</th>
                  <th>Size</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {Array.isArray(files) && files.length > 0 ? (
                  files.map((f) => (
                    <tr key={f.id}>
                      <td>{f.id}</td>
                      <td>{f.title}</td>
                      <td>
                        {f.user
                          ? `${f.user.first_name} ${f.user.last_name}`
                          : f.user_id}
                      </td>
                      <td>
                        {Math.max(
                          1,
                          Math.round((f.size_bytes || 0) / 1024)
                        )}{" "}
                        KB
                      </td>
                      <td>
                        <button
                          className="mf-btn ghost"
                          onClick={() => handleDownload(f.id, f.title)}
                        >
                          Download
                        </button>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="5">No files found</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}

        {/* Settings */}
        {activeTab === "settings" && (
          <div>
            <h3>👤 My Account</h3>
            {staffProfile ? (
              <div className="profile-box">
                <p>
                  <b>ID:</b> {staffProfile.id}
                </p>
                <p>
                  <b>Name:</b> {staffProfile.first_name}{" "}
                  {staffProfile.last_name}
                </p>
                <p>
                  <b>Email:</b> {staffProfile.email}
                </p>
                <p>
                  <b>Role:</b> {staffProfile.role}
                </p>
                <p>
                  <b>Department:</b> {staffProfile.department?.name || "—"}
                </p>
                <br />
                <h4>Change Password</h4>
                <form onSubmit={updatePassword} className="admin-form">
                  <input
                    type="password"
                    placeholder="New password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                  />
                  <input
                    type="password"
                    placeholder="Confirm new password"
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    required
                  />
                  <button type="submit">Update Password</button>
                </form>
              </div>
            ) : (
              <p>Loading profile...</p>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
