// src/components/Header.jsx
import { Link, useNavigate} from "react-router-dom";
import { useEffect, useState } from "react";
import "../assets/Header.css";
import api from "../services/api";

export default function Header() {
  const nav = useNavigate();
  const [user, setUser] = useState([]);

  useEffect(() => {
        const fetchUserDeets = async () => {
            const res = await api.get("/me");
            setUser(res.data);
        }
        fetchUserDeets();
  }, []);

  const logout = () => {
    localStorage.removeItem("token");
    nav("/"); // redirect back to login
  };

  return (
    <header className="app-header">
      <h1 className="logo">📂 Document Vault</h1>
      <nav className="nav-links">
        <Link to="/dashboard">Dashboard</Link>
        <Link to="/my-files">My Files</Link>
        <Link to="/shared">Shared Files</Link>
        {user.role === "admin" ? <Link to="/admin">Admin Panel</Link> : user.role === "staff" ? <Link to="/staff">Staff Panel</Link>: ""}
        <button onClick={logout} className="logout-btn">
          Logout
        </button>
      </nav>
    </header>
  );
}
