import { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import api from "../services/api.js";
import "../assets/AdminPage.css";

export default function AdminPanelPage() {
  const location = useLocation();
  const params = new URLSearchParams(location.search);
  const defaultTab = params.get("tab") || "users";

  const [activeTab, setActiveTab] = useState(defaultTab);

  // State
  const [users, setUsers] = useState([]);
  const [userMeta, setUserMeta] = useState({ current_page: 1, last_page: 1 });
  const [logs, setLogs] = useState([]);
  const [logMeta, setLogMeta] = useState({ current_page: 1, last_page: 1 });
  const [departments, setDepartments] = useState([]);
  const [files, setFiles] = useState([]);
  const [newUser, setNewUser] = useState({
    first_name: "",
    last_name: "",
    email: "",
    password: "",
    role: "user",
    department_id: "",
  });
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  // Search state
  const [userQuery, setUserQuery] = useState("");
  const [fileQuery, setFileQuery] = useState("");

  // Edit modal
  const [editOpen, setEditOpen] = useState(false);
  const [editUser, setEditUser] = useState(null);

  // Admin profile (settings tab)
  const [adminProfile, setAdminProfile] = useState(null);

  // --- Fetch helpers ---
  const fetchUsers = async (page = 1, search = "") => {
    try {
      const res = await api.get(
        `/admin/users?page=${page}&search=${encodeURIComponent(search)}`
      );
      setUsers(res.data.data || []);
      if (res.data.meta) {
        setUserMeta(res.data.meta);
      } else {
        setUserMeta({ current_page: page, last_page: 1 });
      }
    } catch (e) {
      console.error("Failed to fetch users", e);
    }
  };

  const fetchLogs = async (page = 1) => {
    try {
      const res = await api.get(`/admin/logs?page=${page}`);
      setLogs(res.data.data || []);
      if (res.data.meta) {
        setLogMeta(res.data.meta);
      } else {
        setLogMeta({ current_page: page, last_page: 1 });
      }
    } catch (e) {
      console.error("Failed to fetch logs", e);
    }
  };

  const fetchDepartments = async () => {
    try {
      const res = await api.get("/admin/departments");
      setDepartments(res.data || []);
    } catch (e) {
      console.error("Failed to fetch departments", e);
    }
  };

  const fetchFiles = async (search = "") => {
    try {
      if (search.trim()) {
        const res = await api.get(`/search?q=${encodeURIComponent(search)}`);
        setFiles(res.data.data || []);
      } else {
        const res = await api.get("/admin/files");
        setFiles(res.data.data || res.data || []);
      }
    } catch (e) {
      console.error("Failed to fetch files", e);
      setFiles([]);
    }
  };

  const fetchProfile = async () => {
    try {
      const res = await api.get("/me");
      setAdminProfile(res.data);
    } catch (e) {
      console.error("Failed to fetch admin profile", e);
    }
  };

  useEffect(() => {
    if (activeTab === "users") fetchUsers();
    if (activeTab === "logs") fetchLogs();
    if (activeTab === "register") fetchDepartments();
    if (activeTab === "files") fetchFiles();
    if (activeTab === "settings") fetchProfile();
  }, [activeTab]);

  // --- Actions ---
  const registerUser = async (e) => {
    e.preventDefault();
    try {
      await api.post("/admin/users", newUser);
      setNewUser({
        first_name: "",
        last_name: "",
        email: "",
        password: "",
        role: "user",
        department_id: "",
      });
      fetchUsers();
      alert("User registered!");
    } catch (e) {
      alert("Registration failed");
    }
  };

  const updatePassword = async (e) => {
    e.preventDefault();
    try {
      await api.put(`/admin/users/${adminProfile.id}/password`, { password });
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
          filename = disposition.split("filename=")[1].trim().replace(/(^"|"$)/g, "");
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

  const handleDeleteUser = async (id) => {
    if (!confirm("Delete this user?")) return;
    try {
      await api.delete(`/admin/users/${id}`);
      fetchUsers();
    } catch (e) {
      alert("Failed to delete user");
    }
  };

  const handleOpenEdit = (user) => {
    setEditUser({ ...user, department_id: user.department?.id || "" });
    setEditOpen(true);
    fetchDepartments();
  };

  const handleUpdateUser = async (e) => {
    e.preventDefault();
    try {
      await api.put(`/admin/users/${editUser.id}`, editUser);
      setEditOpen(false);
      setEditUser(null);
      fetchUsers();
      alert("User updated!");
    } catch (e) {
      alert("Failed to update user");
    }
  };

  // --- Render ---
  return (
    <div className="admin-wrap">
      <h2 className="admin-title">⚙️ Admin Panel</h2>

      {/* Tabs */}
      <div className="admin-tabs">
        <button onClick={() => setActiveTab("users")} className={activeTab === "users" ? "active" : ""}>Users</button>
        <button onClick={() => setActiveTab("logs")} className={activeTab === "logs" ? "active" : ""}>Logs</button>
        <button onClick={() => setActiveTab("files")} className={activeTab === "files" ? "active" : ""}>Files</button>
        <button onClick={() => setActiveTab("register")} className={activeTab === "register" ? "active" : ""}>Register User</button>
        <button onClick={() => setActiveTab("settings")} className={activeTab === "settings" ? "active" : ""}>Settings</button>
      </div>

      <div className="admin-content">
        {/* === Users Tab === */}
        {activeTab === "users" && (
          <div>
            <h3>👥 All Users</h3>

            {/* Search form */}
            <form
              onSubmit={(e) => {
                e.preventDefault();
                fetchUsers(1, userQuery);
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
                  <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Dept</th><th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.map((u) => (
                  <tr key={u.id}>
                    <td>{u.id}</td>
                    <td>{u.first_name} {u.last_name}</td>
                    <td>{u.email}</td>
                    <td>{u.role}</td>
                    <td>{u.department?.name || "—"}</td>
                    <td>
                      <button className="table-btn" onClick={() => handleOpenEdit(u)}>Update</button>
                      <button className="table-btn danger" onClick={() => handleDeleteUser(u.id)}>Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>

            {/* Pagination */}
            <div className="pagination">
              <button
                disabled={userMeta.current_page <= 1}
                onClick={() => fetchUsers(userMeta.current_page - 1, userQuery)}
              >
                Prev
              </button>
              {[...Array(userMeta.last_page)].map((_, idx) => {
                const pageNum = idx + 1;
                return (
                  <button
                    key={pageNum}
                    className={userMeta.current_page === pageNum ? "active" : ""}
                    onClick={() => fetchUsers(pageNum, userQuery)}
                  >
                    {pageNum}
                  </button>
                );
              })}
              <button
                disabled={userMeta.current_page >= userMeta.last_page}
                onClick={() => fetchUsers(userMeta.current_page + 1, userQuery)}
              >
                Next
              </button>
            </div>
          </div>
        )}

        {/* === Logs Tab === */}
        {activeTab === "logs" && (
          <div>
            <h3>📝 Audit Logs</h3>
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th><th>User</th><th>Action</th><th>File</th><th>IP</th><th>Time</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((l) => (
                  <tr key={l.id}>
                    <td>{l.id}</td>
                    <td>{l.user ? `${l.user.first_name} ${l.user.last_name}` : l.user_id}</td>
                    <td>{l.action}</td>
                    <td>{l.file?.title || "—"}</td>
                    <td>{l.ip_address}</td>
                    <td>{new Date(l.created_at).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>

            {/* Pagination */}
            <div className="pagination">
              <button disabled={logMeta.current_page <= 1} onClick={() => fetchLogs(logMeta.current_page - 1)}>Prev</button>
              {[...Array(logMeta.last_page)].map((_, idx) => {
                const pageNum = idx + 1;
                return (
                  <button key={pageNum} className={logMeta.current_page === pageNum ? "active" : ""} onClick={() => fetchLogs(pageNum)}>
                    {pageNum}
                  </button>
                );
              })}
              <button disabled={logMeta.current_page >= logMeta.last_page} onClick={() => fetchLogs(logMeta.current_page + 1)}>Next</button>
            </div>
          </div>
        )}

        {/* === Files Tab === */}
        {activeTab === "files" && (
          <div>
            <h3>📂 All Uploaded Files</h3>

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
                  <th>ID</th><th>Title</th><th>Owner</th><th>Size</th><th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {Array.isArray(files) && files.length > 0 ? (
                  files.map((f) => (
                    <tr key={f.id}>
                      <td>{f.id}</td>
                      <td>{f.title}</td>
                      <td>{f.user ? `${f.user.first_name} ${f.user.last_name}` : f.user_id}</td>
                      <td>{Math.max(1, Math.round((f.size_bytes || 0) / 1024))} KB</td>
                      <td>
                        <button className="mf-btn ghost" onClick={() => handleDownload(f.id, f.title)}>Download</button>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr><td colSpan="5">No files found</td></tr>
                )}
              </tbody>
            </table>
          </div>
        )}

        {/* === Register Tab === */}
        {activeTab === "register" && (
          <div>
            <h3>➕ Register User</h3>
            <form onSubmit={registerUser} className="admin-form">
              <input placeholder="First name" value={newUser.first_name} onChange={(e) => setNewUser({ ...newUser, first_name: e.target.value })} required />
              <input placeholder="Last name" value={newUser.last_name} onChange={(e) => setNewUser({ ...newUser, last_name: e.target.value })} required />
              <input type="email" placeholder="Email" value={newUser.email} onChange={(e) => setNewUser({ ...newUser, email: e.target.value })} required />
              <input type="password" placeholder="Password" value={newUser.password} onChange={(e) => setNewUser({ ...newUser, password: e.target.value })} required />
              <select value={newUser.role} onChange={(e) => setNewUser({ ...newUser, role: e.target.value })}>
                <option value="user">User</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
              </select>
              <select value={newUser.department_id} onChange={(e) => setNewUser({ ...newUser, department_id: e.target.value })}>
                <option value="">No Department</option>
                {departments.map((d) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
              <button type="submit">Register</button>
            </form>
          </div>
        )}

        {/* === Settings Tab === */}
        {activeTab === "settings" && (
          <div>
            <h3>👤 My Account</h3>
            {adminProfile ? (
              <div className="profile-box">
                <p><b>ID:</b> {adminProfile.id}</p>
                <p><b>Name:</b> {adminProfile.first_name} {adminProfile.last_name}</p>
                <p><b>Email:</b> {adminProfile.email}</p>
                <p><b>Role:</b> {adminProfile.role}</p>
                <p><b>Department:</b> {adminProfile.department?.name || "—"}</p>
                <br />
                <h4>Change Password</h4>
                <form
                  onSubmit={(e) => {
                    e.preventDefault();
                    if (password !== confirmPassword) {
                      alert("Passwords do not match!");
                      return;
                    }
                    updatePassword(e);
                  }}
                  className="admin-form"
                >
                  <input type="password" placeholder="New password" value={password} onChange={(e) => setPassword(e.target.value)} required />
                  <input type="password" placeholder="Confirm new password" value={confirmPassword} onChange={(e) => setConfirmPassword(e.target.value)} required />
                  <button type="submit">Update Password</button>
                </form>
              </div>
            ) : (
              <p>Loading profile...</p>
            )}
          </div>
        )}
      </div>

      {/* ===== Update User Modal ===== */}
      {editOpen && (
        <div className="modal-backdrop" onClick={() => setEditOpen(false)}>
          <div className="modal" onClick={(e) => e.stopPropagation()}>
            <h3 className="modal-title">Update User</h3>
            <form onSubmit={handleUpdateUser} className="modal-form">
              <input value={editUser.first_name} onChange={(e) => setEditUser({ ...editUser, first_name: e.target.value })} placeholder="First name" required />
              <input value={editUser.last_name} onChange={(e) => setEditUser({ ...editUser, last_name: e.target.value })} placeholder="Last name" required />
              <input type="email" value={editUser.email} onChange={(e) => setEditUser({ ...editUser, email: e.target.value })} placeholder="Email" required />
              <select value={editUser.role} onChange={(e) => setEditUser({ ...editUser, role: e.target.value })}>
                <option value="user">User</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
              </select>
              <select value={editUser.department_id} onChange={(e) => setEditUser({ ...editUser, department_id: e.target.value })}>
                <option value="">No Department</option>
                {departments.map((d) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
              <div className="modal-actions">
                <button type="button" className="table-btn" onClick={() => setEditOpen(false)}>Cancel</button>
                <button type="submit" className="table-btn primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
