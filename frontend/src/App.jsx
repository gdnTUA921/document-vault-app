// src/App.jsx
import { Routes, Route, Navigate, Outlet } from "react-router-dom";
import LoginPage from "./pages/LoginPage.jsx";
import DashboardPage from "./pages/DashboardPage.jsx";
import MyFilesPage from "./pages/MyFilesPage.jsx";
import SharedFilesPage from "./pages/SharedFilesPage.jsx";
import AdminPage from "./pages/AdminPage.jsx";
import StaffPage from "./pages/StaffPage.jsx";
import ProtectedRoute from "./components/ProtectedRoute.jsx";
import Header from "./components/Header.jsx";

// Minimal footer (optional: move to src/components/Footer.jsx)
function Footer() {
  return (
    <footer style={{ padding: "25px", paddingBottom: "50px", textAlign: "center", color: "#64748b", backgroundColor: "#0f172a" }}>
      © {new Date().getFullYear()} Document Vault
    </footer>
  );
}

// Layout with header/footer for protected pages
function Layout() {
  return (
    <>
      <Header />
      <main style={{ padding: "0" }}>
        <Outlet />
      </main>
      <Footer />
    </>
  );
}

// Layout without header/footer (for login)
function BareLayout() {
  return <Outlet />;
}

export default function App() {
  return (
    <Routes>
      {/* Default → /login */}
      <Route path="/" element={<Navigate to="/login" replace />} />

      {/* Public (no header) */}
      <Route element={<BareLayout />}>
        <Route path="/login" element={<LoginPage />} />
      </Route>

      {/* Protected (with header/footer) */}
      <Route
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/my-files" element={<MyFilesPage />} />
        <Route path="/shared" element={<SharedFilesPage />} />
        <Route path="/admin" element={<AdminPage />} />
        <Route path="/staff" element={<StaffPage />} />

      </Route>

      {/* Fallback */}
      <Route path="*" element={<Navigate to="/login" replace />} />
    </Routes>
  );
}
