import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  // Check session on load
  useEffect(() => {
    checkSession();
  }, []);

  const checkSession = async () => {
    try {
      const res = await axios.get('/api/auth/me.php');
      if (res.data.success) {
        setUser(res.data.data);
      }
    } catch (err) {
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password, role) => {
    try {
      const res = await axios.post('/api/auth/login.php', { email, password, role });
      if (res.data.success) {
        setUser(res.data.data);
        return { success: true };
      }
      return { success: false, message: res.data.message };
    } catch (err) {
      return { success: false, message: err.response?.data?.message || "Login failed" };
    }
  };

  const logout = async () => {
    try {
      await axios.get('/api/auth/logout.php'); 
      setUser(null);
    } catch (err) {
      console.error("Logout failed", err);
    }
  };

  // Logout API endpoint helper (we'll create this next)
  return (
    <AuthContext.Provider value={{ user, loading, login, logout, checkSession }}>
      {!loading && children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
