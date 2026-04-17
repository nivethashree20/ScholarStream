import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { 
    FileText, 
    CheckCircle, 
    Clock, 
    AlertCircle, 
    XCircle,
    Users,
    Activity,
    ChevronRight,
    Search
} from 'lucide-react';
import { Link } from 'react-router-dom';
import DashboardLayout from '../../components/layout/DashboardLayout';
import ReviewModal from '../../components/modals/ReviewModal';

const StatCard = ({ icon: Icon, label, value, color, description }) => (
    <div className="glass-card p-8 flex flex-col gap-4 relative overflow-hidden group">
        <div className="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-110 transition-transform">
            <Icon size={120} />
        </div>
        <div className="flex justify-between items-center text-secondary">
            <span className="font-bold text-xs uppercase tracking-widest">{label}</span>
            <Icon size={20} style={{ color }} />
        </div>
        <div>
            <div className="text-4xl font-bold tracking-tight mb-1">{value}</div>
            <p className="text-secondary text-xs">{description}</p>
        </div>
    </div>
);

const AdminDashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [selectedPaper, setSelectedPaper] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const fetchStats = async () => {
        try {
            // Added timestamp to prevent browser caching of stats
            const res = await axios.get(`/api/admin/dashboard_stats.php?t=${Date.now()}`);
            if (res.data.success) {
                setData(res.data.data);
            }
        } catch (err) {
            console.error("Failed to fetch admin dashboard", err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStats();
    }, []);

    const handleReviewClick = (paper) => {
        setSelectedPaper(paper);
        setIsModalOpen(true);
    };

    if (loading) return null;

    const getStatusStyles = (status) => {
        switch (status) {
            case 'Approved': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
            case 'Declined': return 'bg-red-500/10 text-red-500 border-red-500/20';
            case 'Pending': return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
            case 'Revision Required': return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
            default: return 'bg-gray-500/10 text-gray-500 border-gray-500/20';
        }
    };

    return (
        <DashboardLayout>
            <div className="max-w-7xl mx-auto">
                <header className="mb-12">
                    <div className="flex justify-between items-end">
                        <div>
                            <h1 className="text-5xl font-bold tracking-tighter text-white mb-2 leading-none">Admin Control</h1>
                            <p className="text-secondary text-xl">Monitor and review academic research submissions.</p>
                        </div>
                        <div className="flex gap-4">
                            <Link to="/admin/submissions" className="btn-premium">
                                <Search size={20} />
                                Search All Papers
                            </Link>
                        </div>
                    </div>
                </header>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
                    <StatCard icon={FileText} label="Total Papers" value={data?.stats.total || 0} color="#fff" description="System-wide submissions" />
                    <StatCard icon={CheckCircle} label="Approved" value={data?.stats.approved || 0} color="#10b981" description="Published works" />
                    <StatCard icon={Clock} label="Pending" value={data?.stats.pending || 0} color="#f59e0b" description="Awaiting review" />
                    <StatCard icon={AlertCircle} label="Revision" value={data?.stats.revision || 0} color="#3b82f6" description="Feedback provided" />
                    <StatCard icon={XCircle} label="Declined" value={data?.stats.declined || 0} color="#ef4444" description="Not accepted" />
                </div>

                <div className="grid grid-cols-1 gap-8">
                    <div className="glass-card p-10">
                        <div className="flex justify-between items-center mb-10">
                            <h2 className="text-2xl font-bold flex items-center gap-3">
                                <Activity size={24} className="text-primary" />
                                Recent Activity
                            </h2>
                            <Link to="/admin/submissions" className="text-secondary hover:text-white text-sm font-medium flex items-center gap-1 transition-colors">
                                View All Submissions <ChevronRight size={16} />
                            </Link>
                        </div>

                        <div className="space-y-6">
                            {data?.recent.map((paper) => (
                                <div key={paper.id} className="group p-6 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] hover:border-white/10 transition-all">
                                    <div className="flex justify-between items-start mb-4">
                                        <div className="flex gap-4">
                                            <div className="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                                <FileText size={24} />
                                            </div>
                                            <div>
                                                <h3 className="font-bold text-lg mb-1 group-hover:text-primary transition-colors">{paper.title}</h3>
                                                <div className="flex items-center gap-4 text-xs text-secondary">
                                                    <span className="flex items-center gap-1"><Users size={12} /> {paper.student_name}</span>
                                                    <span>•</span>
                                                    <span>{new Date(paper.submitted_at).toLocaleDateString()}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border ${getStatusStyles(paper.status)}`}>
                                            {paper.status}
                                        </span>
                                    </div>
                                    <div className="flex justify-end pt-2 border-t border-white/[0.03] mt-4">
                                        <button 
                                            onClick={() => handleReviewClick(paper)}
                                            className="btn-outline py-2 px-6 text-xs font-bold uppercase tracking-wider"
                                        >
                                            Execute Review
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {selectedPaper && (
                <ReviewModal 
                    paper={selectedPaper}
                    isOpen={isModalOpen}
                    onClose={() => setIsModalOpen(false)}
                    onSuccess={fetchStats}
                />
            )}
        </DashboardLayout>
    );
};

export default AdminDashboard;
