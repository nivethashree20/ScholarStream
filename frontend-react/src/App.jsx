import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import Welcome from './pages/Welcome';
import Login from './pages/Login';
import Register from './pages/Register';
import StudentDashboard from './pages/student/Dashboard';
import SubmitPaper from './pages/student/SubmitPaper';
import AdminDashboard from './pages/admin/Dashboard';
import AdminSubmissions from './pages/admin/Submissions';

// Protected Route Component
const ProtectedRoute = ({ children, allowedRole = null }) => {
  const { user, loading } = useAuth();
  
  if (loading) return null; // Or a loading spinner
  
  if (!user) return <Navigate to="/" replace />;
  
  if (allowedRole && user.role !== allowedRole) {
    return <Navigate to={user.role === 'admin' ? '/admin' : '/student'} replace />;
  }
  
  return children;
};

const ComingSoon = ({ title }) => (
  <div className="min-h-screen flex items-center justify-center">
    <div className="glass-card p-20 text-center">
      <h2 className="text-4xl font-bold mb-4">{title}</h2>
      <p className="text-secondary">This module is currently being migrated.</p>
    </div>
  </div>
);

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* Public Routes */}
          <Route path="/" element={<Welcome />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          
          {/* Protected Student Routes */}
          <Route 
            path="/student/*" 
            element={
              <ProtectedRoute allowedRole="student">
                <Routes>
                  <Route index element={<StudentDashboard />} />
                  <Route path="submit" element={<SubmitPaper />} />
                </Routes>
              </ProtectedRoute>
            } 
          />
          
          {/* Protected Admin Routes */}
          <Route 
            path="/admin/*" 
            element={
              <ProtectedRoute allowedRole="admin">
                <Routes>
                  <Route index element={<AdminDashboard />} />
                  <Route path="submissions" element={<AdminSubmissions />} />
                </Routes>
              </ProtectedRoute>
            } 
          />
          
          {/* Fallback */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;
