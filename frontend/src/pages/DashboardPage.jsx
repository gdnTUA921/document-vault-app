import { useEffect, useState, useMemo } from "react";
import { Link } from "react-router-dom";
import api from "../services/api";
import "../assets/DashboardPage.css";
import "../components/Header.jsx";

export default function DashboardPage() {
  const [data, setData] = useState(null);
  const [busy, setBusy] = useState(true);
  const [err, setErr] = useState("");

  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        setBusy(true);
        const res = await api.get("/dashboard");
        if (!mounted) return;
        setData(res.data);
        console.log(res.data);
      } catch (e) {
        console.error(e);
        setErr(
          e?.response?.data?.message ||
            e?.message ||
            "Failed to load dashboard."
        );
      } finally {
        if (mounted) setBusy(false);
      }
    })();
    return () => {
      mounted = false;
    };
  }, []);

  const fmt = useMemo(
    () =>
      new Intl.DateTimeFormat(undefined, {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      }),
    []
  );

  const getFilesLink = () => {
    if (!data || !data.user) return "/my-files";
    if (data.user.role === "admin") return "/admin?tab=files";
    if (data.user.role === "staff") return "/staff?tab=files";
    return "/my-files";
  };

  if (busy) {
    return (
      <div className="dash-wrap">
        <div className="dash-card">
          <div className="skeleton title" />
          <div className="skeleton row" />
          <div className="skeleton row" />
          <div className="skeleton row" />
        </div>
      </div>
    );
  }

  if (err) {
    return (
      <div className="dash-wrap">
        <div className="dash-card">
          <h1 className="dash-title">Dashboard</h1>
          <div className="error">{err}</div>
          <button className="btn" onClick={() => window.location.reload()}>
            Retry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="dash-page">
      <main className="dash-main">
        <section className="stats-grid">
          {data.user.role === "admin" ? (
            <Stat label="All Users" value={data.admin.users_total} />
          ) : null}
          {data.user.role === "admin" ? (
            <Stat label="All Files" value={data.admin.files_total} />
          ) : null}
          {data.user.role === "admin" ? (
            <Stat label="All Shares" value={data.admin.shares_total} />
          ) : null}
          {data.user.role === "staff" ? (
            <Stat
              label="All Users Within Department"
              value={data.staff.users_total}
            />
          ) : null}
          {data.user.role === "staff" ? (
            <Stat
              label="Department Files"
              value={data.staff.files_total}
            />
          ) : null}
          <Stat label="My Files" value={data.counts.my_files} />
          <Stat
            label="Files Shared With Me"
            value={data.counts.shared_with_me}
          />
        </section>

        <section className="panels-grid">
          <div className="panel">
            <div className="panel-head">
              <h2>Recent Files</h2>
              <Link to={getFilesLink()} className="link">
                View all
              </Link>
            </div>
            <div className="table-wrap">
              <table className="tbl">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                  </tr>
                </thead>
                <tbody>
                  {data.recent_files.length === 0 && (
                    <tr>
                      <td colSpan={5} className="muted">
                        No recent files.
                      </td>
                    </tr>
                  )}
                  {data.recent_files.map((f) => (
                    <tr key={f.id}>
                      <td title={f.original_name}>
                        <Link to={`/files?id=${f.id}`} className="link">
                          {f.title || f.original_name}
                        </Link>
                      </td>
                      <td>
                        {f.user
                          ? `${f.user.first_name ?? ""} ${
                              f.user.last_name ?? ""
                            }`.trim() || f.user.email
                          : "—"}
                      </td>
                      <td>{f.mime_type || "—"}</td>
                      <td>{formatBytes(f.size_bytes)}</td>
                      <td>
                        {f.created_at
                          ? fmt.format(new Date(f.created_at))
                          : "—"}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="panel">
            <div className="panel-head">
              <h2>Recent Activity</h2>
            </div>
            <ul className="activity">
              {data.recent_activity.length === 0 && (
                <li className="muted">No recent activity.</li>
              )}
              {data.recent_activity.map((a) => (
                <li key={a.id}>
                  <div className="act-line">
                    <strong>{a.action}</strong>
                    <span className="act-muted">
                      {a.created_at
                        ? fmt.format(new Date(a.created_at))
                        : "—"}
                    </span>
                  </div>
                  <div className="act-sub">
                    {a.user
                      ? `${a.user.first_name ?? ""} ${
                          a.user.last_name ?? ""
                        }`.trim() || a.user.email
                      : "Someone"}
                    {a.file
                      ? ` • ${a.file.title || a.file.original_name}`
                      : ""}
                    {a.ip_address ? ` • ${a.ip_address}` : ""}
                  </div>
                </li>
              ))}
            </ul>
          </div>
        </section>
      </main>
    </div>
  );
}

function Stat({ label, value }) {
  return (
    <div className="stat">
      <div className="stat-value">{Number(value).toLocaleString()}</div>
      <div className="stat-label">{label}</div>
    </div>
  );
}

function formatBytes(b) {
  if (!b && b !== 0) return "—";
  const u = ["B", "KB", "MB", "GB", "TB"];
  let i = 0;
  let n = Number(b);
  while (n >= 1024 && i < u.length - 1) {
    n /= 1024;
    i++;
  }
  return `${n.toFixed(n < 10 && i > 0 ? 1 : 0)} ${u[i]}`;
}
