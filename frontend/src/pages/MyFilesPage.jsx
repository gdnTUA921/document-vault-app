// MyFilesPage.jsx
import { useEffect, useState } from "react";
import api from "../services/api.js";
import "../assets/MyFilesPage.css";

export default function MyFilesPage() {
  const [files, setFiles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");
  const [file, setFile] = useState(null);
  const [title, setTitle] = useState("");

  // Search state
  const [query, setQuery] = useState("");

  // Share modal state
  const [shareOpen, setShareOpen] = useState(false);
  const [shareFile, setShareFile] = useState(null);
  const [recipientId, setRecipientId] = useState("");
  const [permission, setPermission] = useState("view");
  const [shareBusy, setShareBusy] = useState(false);

  // Shares state
  const [shares, setShares] = useState({});
  const [activeSharesFileId, setActiveSharesFileId] = useState(null);

  // Allowed types + size
  const allowedTypes = [
    "text/plain", // txt
    "application/pdf", // pdf
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document", // docx
    "image/png",
    "image/jpeg",
    "image/gif",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", // xlsx
    "application/vnd.openxmlformats-officedocument.presentationml.presentation", // pptx
  ];
  const maxSize = 10 * 1024 * 1024; // 10 MB

  // Fetch files (default list)
  const fetchFiles = async () => {
    try {
      setLoading(true);
      const res = await api.get("/files");
      setFiles(res.data.data || res.data || []);
      setErr("");
    } catch (e) {
      setErr(e?.response?.data?.message || "Failed to fetch files.");
    } finally {
      setLoading(false);
    }
  };

  // Search files
  const searchFiles = async (e) => {
    if (e) e.preventDefault();
    if (!query.trim()) {
      fetchFiles();
      return;
    }
    try {
      setLoading(true);
      const res = await api.get(`/search?q=${encodeURIComponent(query)}`);
      setFiles(res.data.data || res.data || []);
      setErr("");
    } catch (e) {
      setErr(e?.response?.data?.message || "Search failed.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFiles();
  }, []);

  // Upload file
  const handleUpload = async (e) => {
    e.preventDefault();
    if (!file) return;

    // ✅ Validate file
    if (!allowedTypes.includes(file.type)) {
      alert("Invalid file type. Allowed: txt, pdf, docx, png, jpeg, gif, xlsx, pptx");
      return;
    }
    if (file.size > maxSize) {
      alert("File is too large. Max size is 10 MB.");
      return;
    }

    const form = new FormData();
    form.append("title", title || file.name);
    form.append("file", file);

    try {
      await api.post("/files", form, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      setTitle("");
      setFile(null);
      await fetchFiles();
    } catch (e) {
      setErr(e?.response?.data?.message || "Upload failed.");
    }
  };

  // Download file
  const handleDownload = async (id, title) => {
    try {
      const res = await api.get(`/files/${id}/download`, {
        responseType: "blob",
      });

      const url = window.URL.createObjectURL(new Blob([res.data]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", title);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (e) {
      alert("Download failed: " + (e?.response?.data?.message || "Server error"));
    }
  };

  // Delete file
  const handleDelete = async (id, title) => {
    if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
    try {
      await api.delete(`/files/${id}`);
      await fetchFiles();
    } catch (e) {
      setErr(e?.response?.data?.message || "Delete failed.");
    }
  };

  // Share modal open
  const openShare = (f) => {
    setShareFile(f);
    setRecipientId("");
    setPermission("view");
    setShareOpen(true);
  };

  // Submit share
  const submitShare = async (e) => {
    e.preventDefault();
    if (!shareFile) return;
    setShareBusy(true);
    try {
      await api.post(`/files/${shareFile.id}/share`, {
        shared_with: Number(recipientId),
        permission,
      });
      setShareOpen(false);
    } catch (e) {
      setErr(e?.response?.data?.message || "Share failed.");
    } finally {
      setShareBusy(false);
    }
  };

  // Fetch shares for a file
  const fetchShares = async (fileId) => {
    try {
      const res = await api.get(`/files/${fileId}/share`);
      setShares((prev) => ({ ...prev, [fileId]: res.data || [] }));
    } catch (e) {
      console.error("Failed to fetch shares", e);
    }
  };

  // Delete a share
  const handleRemoveShare = async (fileId, shareId) => {
    if (!confirm("Remove this share?")) return;
    try {
      await api.delete(`/files/${fileId}/share/${shareId}`);
      await fetchShares(fileId);
    } catch (e) {
      alert("Failed to remove share: " + (e?.response?.data?.message || "Server error"));
    }
  };

  // Toggle view shares for a file
  const toggleSharesView = async (fileId) => {
    if (activeSharesFileId === fileId) {
      setActiveSharesFileId(null);
    } else {
      await fetchShares(fileId);
      setActiveSharesFileId(fileId);
    }
  };

  return (
    <div className="mf-wrap">
      <h2 className="mf-title">📂 My Files</h2>

      {/* Upload form */}
      <form onSubmit={handleUpload} className="mf-upload">
        <input
          type="text"
          placeholder="File title (optional)"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          className="mf-input"
        />
        <input
          type="file"
          onChange={(e) => setFile(e.target.files[0])}
          className="mf-input mf-file"
        />
        <button type="submit" className="mf-btn">
          Upload
        </button>
      </form>

      {/* Search form */}
      <form onSubmit={searchFiles} className="mf-upload">
        <input
          type="text"
          placeholder="Search files..."
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          className="mf-input"
        />
        <button type="submit" className="mf-btn ghost">
          Search
        </button>
        {query && (
          <button
            type="button"
            className="mf-btn danger"
            onClick={() => {
              setQuery("");
              fetchFiles();
            }}
          >
            Clear
          </button>
        )}
      </form>

      {err && <div className="mf-alert">{err}</div>}

      {loading ? (
        <div className="mf-muted">Loading files…</div>
      ) : files.length === 0 ? (
        <div className="mf-muted">No files found.</div>
      ) : (
        <div className="mf-grid">
          {files.map((f) => (
            <div className="mf-card mf-row" key={f.id}>
              <div className="mf-card-head">
                <div className="mf-file-title" title={f.title}>
                  {f.title}
                </div>
                <div className="mf-file-meta">
                  {f.mime_type || "—"} •{" "}
                  {Math.max(1, Math.round((f.size_bytes || 0) / 1024))} KB
                </div>
              </div>

              <div className="mf-actions">
                <button
                  className="mf-btn ghost"
                  onClick={() =>
                    handleDownload(f.id, f.original_name || f.title)
                  }
                >
                  Download
                </button>
                <button
                  className="mf-btn ghost"
                  onClick={() => openShare(f)}
                >
                  Share
                </button>
                <button
                  className="mf-btn danger"
                  onClick={() => handleDelete(f.id, f.title)}
                >
                  Delete
                </button>
                <button
                  className="mf-btn ghost"
                  onClick={() => toggleSharesView(f.id)}
                >
                  {activeSharesFileId === f.id
                    ? "Hide Shares"
                    : "View Shares"}
                </button>
              </div>

              {shares[f.id] && shares[f.id].length > 0 &&
                activeSharesFileId === f.id && (
                  <ul className="mf-shares">
                    {shares[f.id].map((s) => (
                      <li key={s.id} className="mf-share-item">
                        User #{s.shared_with}
                        <button
                          className="mf-btn danger small"
                          onClick={() =>
                            handleRemoveShare(f.id, s.id)
                          }
                        >
                          Remove
                        </button>
                      </li>
                    ))}
                  </ul>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Share Modal */}
      {shareOpen && (
        <div
          className="mf-modal-backdrop"
          onClick={() => setShareOpen(false)}
        >
          <div
            className="mf-modal"
            onClick={(e) => e.stopPropagation()}
          >
            <h3 className="mf-modal-title">
              Share “{shareFile?.title}”
            </h3>
            <form onSubmit={submitShare} className="mf-modal-form">
              <label className="mf-label">
                Recipient User ID
                <input
                  className="mf-input"
                  type="number"
                  min="1"
                  value={recipientId}
                  onChange={(e) => setRecipientId(e.target.value)}
                  required
                />
              </label>

              <div className="mf-modal-actions">
                <button
                  type="button"
                  className="mf-btn ghost"
                  onClick={() => setShareOpen(false)}
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="mf-btn"
                  disabled={shareBusy}
                >
                  {shareBusy ? "Sharing…" : "Share"}
                </button>
              </div>
            </form>
            <p className="mf-note">
              Tip: The recipient must have a valid RSA keypair generated in
              the backend (POST <code>/api/keys/generate</code>) to open
              shared file keys.
            </p>
          </div>
        </div>
      )}
    </div>
  );
}
