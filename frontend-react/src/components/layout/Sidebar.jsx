import React from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { 
    LayoutDashboard, 
    FilePlus, 
    FileText, 
    LogOut, 
    Library,
    User,
    Settings
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { motion } from 'framer-motion';

const Sidebar = () => {
    const { logout, user } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        navigate('/');
    };

    const navItems = user?.role === 'admin' ? [
        { icon: LayoutDashboard, label: 'Dashboard', path: '/admin' },
        { icon: FileText, label: 'Submissions', path: '/admin/submissions' },
    ] : [
        { icon: LayoutDashboard, label: 'Research Paper Details', path: '/student' },
        { icon: FilePlus, label: 'Research paper registration', path: '/student/submit' },
    ];

    return (
        <motion.aside 
            initial={{ x: -20, opacity: 0 }}
            animate={{ x: 0, opacity: 1 }}
            className="w-[280px] h-screen sticky top-0 bg-[#16161b] border-r border-white/5 p-6 flex flex-col"
        >
            <div className="flex items-center gap-3 mb-10 pl-2">
                <div className="text-white text-lg font-bold flex items-center gap-2">
                    <Library size={22} className="text-[#a855f7]" />
                    ScholarStream
                </div>
            </div>

            <nav className="flex-grow space-y-2">
                {navItems.map((item) => (
                    <NavLink
                        key={item.path}
                        to={item.path}
                        end
                        className={({ isActive }) => `
                            flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-200 text-[15px] font-semibold
                            ${isActive 
                                ? 'bg-[#27272a] text-[#a855f7] shadow-lg' 
                                : 'text-[#71717a] hover:bg-white/5 hover:text-white'
                            }
                        `}
                    >
                        <item.icon size={20} />
                        <span>{item.label}</span>
                    </NavLink>
                ))}
            </nav>

            <div className="pt-6 mt-6 border-t border-white/5 space-y-4">
                <div className="flex items-center gap-3 px-2 py-3">
                    <div className="w-10 h-10 rounded-full bg-[#27272a] flex items-center justify-center text-[#71717a] font-bold text-xs">
                        {user?.name?.charAt(0).toUpperCase()}
                    </div>
                    <div className="overflow-hidden">
                        <p className="text-sm font-bold text-white truncate uppercase tracking-tight">{user?.name}</p>
                        <button 
                            onClick={handleLogout}
                            className="text-[13px] text-[#71717a] hover:text-white font-semibold flex items-center gap-1 transition-colors"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </motion.aside>
    );
};

export default Sidebar;
