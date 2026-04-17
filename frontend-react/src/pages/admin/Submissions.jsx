import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { 
    Search, 
    Filter, 
    ChevronRight, 
    Download,
    Eye,
    MessageSquare,
    CheckCircle,
    XCircle,
    RotateCcw
} from 'lucide-react';
import DashboardLayout from '../../components/layout/DashboardLayout';
import ReviewModal from '../../components/modals/ReviewModal';

const Submissions = () => {
    const [submissions, setSubmissions] = useState([]);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [loading, setLoading] = useState(true);
    const [selectedPaper, setSelectedPaper] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const statuses = ['all', 'Pending', 'Approved', 'Declined', 'Revision Required'];

    const fetchSubmissions = async () => {
        try {
            setLoading(true);
            const res = await axios.get(`/api/admin/submissions.php?search=${search}&status=${statusFilter}`);
            if (res.data.success) {
                setSubmissions(res.data.data);
            }
        } catch (err) {
            console.error("Failed to fetch submissions", err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchSubmissions();
    }, [search, statusFilter]);

    const handleReviewClick = (paper) => {
        setSelectedPaper(paper);
        setIsModalOpen(true);
    };

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
                    <h1 className="text-4xl font-bold tracking-tighter text-white mb-2">Submission Management</h1>
                    <p className="text-secondary text-lg">Review and manage all student research paper submissions.</p>
                </header>

                <div className="glass-card p-8">
                    <div className="flex flex-col md:flex-row justify-between items-center gap-6 mb-10">
                        <div className="relative w-full max-w-xl">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-secondary" size={20} />
                            <input 
                                type="text" 
                                placeholder="Search by paper title or student name..." 
                                className="glass-input w-full pl-12 h-14 text-base"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                        <div className="flex items-center gap-4 w-full md:w-auto">
                            <Filter size={20} className="text-secondary" />
                            <div className="flex gap-2 p-1.5 bg-white/5 rounded-2xl border border-white/5">
                                {statuses.map((s) => (
                                    <button
                                        key={s}
                                        onClick={() => setStatusFilter(s)}
                                        className={`px-4 py-2 rounded-xl text-xs font-bold transition-all uppercase tracking-wider
                                            ${statusFilter === s 
                                                ? 'bg-primary text-white shadow-lg shadow-primary/20' 
                                                : 'text-secondary hover:text-white'
                                            }
                                        `}
                                    >
                                        {s}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="text-secondary text-xs font-black uppercase tracking-widest border-b border-white/5">
                                    <th className="pb-6">Student & Paper Details</th>
                                    <th className="pb-6">Submitted On</th>
                                    <th className="pb-6">Status</th>
                                    <th className="pb-6 text-right">Review Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {loading ? (
                                    <tr>
                                        <td colSpan="4" className="py-20 text-center">
                                            <div className="flex flex-col items-center gap-4">
                                                <div className="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                                                <span className="text-secondary font-bold uppercase tracking-widest text-xs">Synchronizing Archive...</span>
                                            </div>
                                        </td>
                                    </tr>
                                ) : submissions.length === 0 ? (
                                    <tr>
                                        <td colSpan="4" className="py-20 text-center text-secondary font-medium">
                                            No matches found in the registry.
                                        </td>
                                    </tr>
                                ) : (
                                    submissions.map((paper) => (
                                        <tr key={paper.id} className="group hover:bg-white/[0.02] transition-colors">
                                            <td className="py-8 pr-6">
                                                <div className="flex flex-col gap-1">
                                                    <span className="font-bold text-lg group-hover:text-primary transition-colors line-clamp-1">{paper.title}</span>
                                                    <span className="text-sm text-secondary flex items-center gap-2">
                                                        {paper.student_name} <span className="opacity-30">•</span> {paper.department}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="py-8 text-sm font-medium">
                                                {new Date(paper.submitted_at).toLocaleDateString(undefined, {
                                                    day: 'numeric',
                                                    month: 'long',
                                                    year: 'numeric'
                                                })}
                                            </td>
                                            <td className="py-8">
                                                <span className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border ${getStatusStyles(paper.status)}`}>
                                                    {paper.status}
                                                </span>
                                            </td>
                                            <td className="py-8 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <button className="w-10 h-10 rounded-xl border border-white/10 flex items-center justify-center text-secondary hover:bg-white/10 hover:text-white transition-all" title="View PDF">
                                                        <Eye size={18} />
                                                    </button>
                                                    <button 
                                                        onClick={() => handleReviewClick(paper)}
                                                        className="w-10 h-10 rounded-xl border border-white/10 flex items-center justify-center text-primary-glow hover:bg-primary/10 transition-all" 
                                                        title="Review Submission"
                                                    >
                                                        <MessageSquare size={18} />
                                                    </button>
                                                    <button 
                                                        onClick={() => handleReviewClick(paper)}
                                                        className="flex items-center gap-2 btn-premium py-2 px-5 text-xs font-black uppercase tracking-wider"
                                                    >
                                                        Review
                                                        <ChevronRight size={14} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {selectedPaper && (
                <ReviewModal 
                    paper={selectedPaper}
                    isOpen={isModalOpen}
                    onClose={() => setIsModalOpen(false)}
                    onSuccess={fetchSubmissions}
                />
            )}
        </DashboardLayout>
    );
};

export default Submissions;
