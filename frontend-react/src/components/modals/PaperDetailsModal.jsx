import React from 'react';
import { 
    X, 
    FileText, 
    Calendar, 
    Tag, 
    MessageCircle, 
    Download,
    CheckCircle,
    XCircle,
    Clock,
    AlertCircle
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

const PaperDetailsModal = ({ paper, isOpen, onClose }) => {
    if (!paper || !isOpen) return null;

    const getStatusInfo = (status) => {
        switch (status) {
            case 'Approved': return { icon: CheckCircle, color: '#10b981', label: 'Approved' };
            case 'Declined': return { icon: XCircle, color: '#ef4444', label: 'Declined' };
            case 'Pending': return { icon: Clock, color: '#f59e0b', label: 'Pending Review' };
            case 'Revision Required': return { icon: AlertCircle, color: '#3b82f6', label: 'Revision Needed' };
            default: return { icon: Clock, color: '#71717a', label: 'Unknown' };
        }
    };

    const statusInfo = getStatusInfo(paper.status);

    return (
        <AnimatePresence>
            <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                <motion.div 
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    onClick={onClose}
                    className="absolute inset-0 bg-black/80 backdrop-blur-sm"
                />
                
                <motion.div 
                    initial={{ opacity: 0, scale: 0.95, y: 20 }}
                    animate={{ opacity: 1, scale: 1, y: 0 }}
                    exit={{ opacity: 0, scale: 0.95, y: 20 }}
                    className="relative w-full max-w-3xl bg-[#18181b] border border-white/10 rounded-3xl shadow-2xl overflow-hidden"
                >
                    {/* Header */}
                    <div className="p-8 border-b border-white/5 flex justify-between items-start">
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-2xl flex items-center justify-center text-white" style={{ backgroundColor: statusInfo.color }}>
                                <statusInfo.icon size={24} />
                            </div>
                            <div>
                                <h2 className="text-2xl font-bold tracking-tight">Research Details</h2>
                                <p className="text-secondary text-sm">Status: <span style={{ color: statusInfo.color }} className="font-bold">{statusInfo.label}</span></p>
                            </div>
                        </div>
                        <button onClick={onClose} className="p-2 hover:bg-white/5 rounded-xl transition-colors">
                            <X size={24} className="text-secondary" />
                        </button>
                    </div>

                    <div className="p-8 space-y-8 max-h-[70vh] overflow-y-auto">
                        {/* Title & Stats */}
                        <div className="space-y-4">
                            <h3 className="text-3xl font-bold tracking-tighter leading-tight text-white/90">
                                {paper.title}
                            </h3>
                            <div className="flex flex-wrap gap-6 text-sm text-secondary">
                                <span className="flex items-center gap-2">
                                    <Tag size={16} />
                                    {paper.research_area}
                                </span>
                                <span className="flex items-center gap-2">
                                    <Calendar size={16} />
                                    Submitted {new Date(paper.submitted_at).toLocaleDateString()}
                                </span>
                            </div>
                        </div>

                        {/* Admin Feedback (Critical for user request) */}
                        {paper.admin_comments && (
                            <div className="p-6 bg-primary/5 border border-primary/20 rounded-2xl space-y-3">
                                <div className="flex items-center gap-2 text-primary font-bold">
                                    <MessageCircle size={18} />
                                    Reviewer Feedback
                                </div>
                                <p className="text-sm text-white/80 leading-relaxed italic">
                                    "{paper.admin_comments}"
                                </p>
                            </div>
                        )}

                        {/* Abstract */}
                        <div className="space-y-3">
                            <label className="text-[11px] font-black uppercase tracking-widest text-secondary pl-1">Abstract</label>
                            <div className="p-6 bg-white/[0.02] border border-white/5 rounded-2xl">
                                <p className="text-sm text-secondary leading-relaxed whitespace-pre-wrap">
                                    {paper.abstract}
                                </p>
                            </div>
                        </div>

                        {/* Meta Info */}
                        <div className="grid grid-cols-2 gap-6">
                            <div className="space-y-1">
                                <p className="text-[11px] font-black uppercase tracking-widest text-secondary">Department</p>
                                <p className="text-sm font-bold">{paper.department}</p>
                            </div>
                            <div className="space-y-1">
                                <p className="text-[11px] font-black uppercase tracking-widest text-secondary">Research Guide</p>
                                <p className="text-sm font-bold">{paper.guide_name}</p>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="p-8 border-t border-white/5 flex gap-4">
                        <button 
                            className="btn-premium flex-grow font-black text-xs uppercase tracking-widest py-4 shadow-lg shadow-primary/20 flex items-center justify-center gap-2"
                        >
                            <Download size={18} />
                            Download Research PDF
                        </button>
                        <button 
                            onClick={onClose}
                            className="btn-outline px-8 font-black text-xs uppercase tracking-widest py-4"
                        >
                            Close
                        </button>
                    </div>
                </motion.div>
            </div>
        </AnimatePresence>
    );
};

export default PaperDetailsModal;
