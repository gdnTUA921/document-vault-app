import { useEffect, useState } from "react";
import api from "../services/api";
import "../assets/SharedFilesPage.css";

export default function SharedFilesPage() {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  // search state
  const [query, setQuery] = useState("");

  const fetchShared = async () => {
    try {
      setLoading(true);
      setErr("");
      const res = await api.get("/shares/incoming");
      const data = res.data?.data ?? res.data ?? [];
      setRows(data);
    } catch (e) {
      setErr(e?.response?.data?.message || "Failed to load shared files.");
    } finally {
      setLoading(false);
    }
  };

  const searchShared = async (e) => {
    if (e) e.preventDefault();
    if (!query.trim()) {
      fetchShared();
      return;
    }
    try {
      setLoading(true);
      setErr("");
      const res = await api.get(`/search?q=${encodeURIComponent(query)}`);
      const data = res.data?.data ?? res.data ?? [];
      setRows(data);
    } catch (e) {
      setErr(e?.response?.data?.message || "Search failed.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchShared();
  }, []);

  const handleDownload = async (fileId, suggestedName) => {
    try {
      const res = await api.get(`/files/${fileId}/download`, {
        responseType: "blob",
      });

      let filename = suggestedName || `file-${fileId}`;
      if (res.headers["x-file-name"]) {
        filename = decodeURIComponent(res.headers["x-file-name"]);
      } else {
        const disp = res.headers["content-disposition"];
        if (disp && disp.indexOf("filename=") !== -1) {
          filename = disp.split("filename=")[1].trim().replace(/(^"|"$)/g, "");
        }
      }

      const blob = new Blob([res.data], {
        type: res.headers["content-type"] || "application/octet-stream",
      });

      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    } catch (e) {
      const msg =
        e?.response?.data?.message ||
        (e?.response?.status === 403
          ? "You do not have permission to download this file."
          : "Download failed.");
      alert(msg);
    }
  };

  return (
    <div className="sf-wrap">
      <h2 className="sf-title">🤝 Shared with me</h2>

      {/* Search bar */}
      <form onSubmit={searchShared} className="sf-search">
        <input
          type="text"
          placeholder="Search shared files…"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          className="sf-input"
        />
        <button type="submit" className="sf-btn">Search</button>
        {query && (
          <button
            type="button"
            className="sf-btn danger"
            onClick={() => {
              setQuery("");
              fetchShared();
            }}
          >
            Clear
          </button>
        )}
      </form>

      {err && <div className="sf-alert">{err}</div>}

      {loading ? (
        <div className="sf-muted">Loading…</div>
      ) : rows.length === 0 ? (
        <div className="sf-empty">
          No one has shared a file with you yet.
        </div>
      ) : (
        <div className="sf-table">
          <div className="sf-thead">
            <div>Title</div>
            <div>From</div>
            <div>Type / Size</div>
            <div></div>
          </div>

          {rows.map((r) => {
            const f = r.file || r; // fallback if search returns plain File
            const owner = f.user || {};
            const displayName =
              f.title || f.original_name || `file-${f.id || r.file_id}`;
            const sizeKb = Math.max(1, Math.round((f.size_bytes || 0) / 1024));
            return (
              <div className="sf-row" key={`${r.id || r.file_id}-share`}>
                <div className="sf-cell sf-titlecol" title={displayName}>
                  {displayName}
                </div>
                <div className="sf-cell">
                  {owner.first_name} {owner.last_name}
                  {owner.email ? <span className="sf-dim"> • {owner.email}</span> : null}
                </div>
                <div className="sf-cell sf-dim">
                  {f.mime_type || "—"} • {sizeKb} KB
                </div>
                <div className="sf-cell sf-actions">
                  <button
                    className="sf-btn"
                    onClick={() => handleDownload(f.id, displayName)}
                  >
                    Download
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
