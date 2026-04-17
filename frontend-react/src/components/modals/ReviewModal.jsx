import React, { useState } from 'react';
import { 
    X, 
    CheckCircle, 
    XCircle, 
    RotateCcw, 
    Loader2, 
    MessageSquare,
    FileText,
    User
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import axios from 'axios';

const ReviewModal = ({ paper, isOpen, onClose, onSuccess }) => {
    const [status, setStatus] = useState('');
    const [comments, setComments] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showSuccess, setShowSuccess] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async () => {
        if (!status) {
            setError('Please select a final status.');
            return;
        }

        setIsSubmitting(true);
        setError('');

        try {
            const res = await axios.post('/api/admin/review_paper.php', {
                paper_id: paper.id,
                status,
                comments
            });

            if (res.data.success) {
                setShowSuccess(true);
                setTimeout(() => {
                    onSuccess();
                    onClose();
                    setShowSuccess(false);
                }, 1500);
            } else {
                setError(res.data.message);
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to update paper status');
        } finally {
            setIsSubmitting(false);
        }
    };

    if (!isOpen) return null;

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
                    className="relative w-full max-w-2xl bg-[#18181b] border border-white/10 rounded-3xl shadow-2xl overflow-hidden"
                >
                    <AnimatePresence mode="wait">
                        {showSuccess ? (
                            <motion.div 
                                key="success"
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -10 }}
                                className="p-20 flex flex-col items-center justify-center text-center space-y-6"
                            >
                                <div className="w-24 h-24 bg-emerald-500 rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(16,185,129,0.4)]">
                                    <CheckCircle size={48} className="text-white" />
                                </div>
                                <div className="space-y-2">
                                    <h2 className="text-3xl font-bold tracking-tight">Decision Recorded</h2>
                                    <p className="text-secondary">The research paper status has been updated successfully.</p>
                                </div>
                            </motion.div>
                        ) : (
                            <motion.div key="form" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
                                {/* Header */}
                                <div className="p-8 border-b border-white/5 flex justify-between items-start">
                                    <div className="space-y-1">
                                        <h2 className="text-2xl font-bold tracking-tight">Review Submission</h2>
                                        <p className="text-secondary text-sm">Evaluate the research and provide a formal decision.</p>
                                    </div>
                                    <button onClick={onClose} className="p-2 hover:bg-white/5 rounded-xl transition-colors">
                                        <X size={24} className="text-secondary" />
                                    </button>
                                </div>

                                <div className="p-8 space-y-8 max-h-[70vh] overflow-y-auto">
                                    {/* Paper Details */}
                                    <div className="grid grid-cols-2 gap-6">
                                        <div className="space-y-2">
                                            <label className="text-[11px] font-black uppercase tracking-widest text-secondary pl-1">Research Title</label>
                                            <div className="p-4 bg-white/[0.02] border border-white/5 rounded-2xl flex items-start gap-3">
                                                <FileText size={18} className="text-primary mt-0.5" />
                                                <span className="font-bold text-sm leading-tight">{paper.title}</span>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[11px] font-black uppercase tracking-widest text-secondary pl-1">Submitted By</label>
                                            <div className="p-4 bg-white/[0.02] border border-white/5 rounded-2xl flex items-start gap-3">
                                                <User size={18} className="text-primary mt-0.5" />
                                                <span className="font-bold text-sm leading-tight">{paper.student_name}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Abstract Snippet */}
                                    <div className="space-y-2">
                                        <label className="text-[11px] font-black uppercase tracking-widest text-secondary pl-1">Abstract Snippet</label>
                                        <div className="p-6 bg-white/[0.02] border border-white/5 rounded-2xl">
                                            <p className="text-sm text-secondary leading-relaxed line-clamp-4 italic">
                                                "{paper.abstract || 'No abstract preview available.'}"
                                            </p>
                                        </div>
                                    </div>

                                    {/* Status Selection */}
                                    <div className="space-y-4">
                                        <label className="text-[11px] font-black uppercase tracking-widest text-secondary pl-1">Final Decision</label>
                                        <div className="grid grid-cols-3 gap-4">
                                            <button 
                                                onClick={() => setStatus('Approved')}
                                                className={`flex flex-col items-center gap-3 p-6 rounded-2xl border transition-all
                                                    ${status === 'Approved' 
                                                        ? 'bg-emerald-500/10 border-emerald-500 text-emerald-500 shadow-lg shadow-emerald-500/10' 
                                                        : 'bg-white/[0.02] border-white/5 text-secondary hover:border-white/20'
                                                    }
                                                `}
                                            >
                                                <CheckCircle size={24} />
                                                <span className="text-xs font-bold uppercase tracking-widest">Approve</span>
                                            </button>
                                            <button 
                                                onClick={() => setStatus('Declined')}
                                                className={`flex flex-col items-center gap-3 p-6 rounded-2xl border transition-all
                                                    ${status === 'Declined' 
                                                        ? 'bg-red-500/10 border-red-500 text-red-500 shadow-lg shadow-red-500/10' 
                                                        : 'bg-white/[0.02] border-white/5 text-secondary hover:border-white/20'
                                                    }
                                                `}
                                            >
                                                <XCircle size={24} />
                                                <span className="text-xs font-bold uppercase tracking-widest">Decline</span>
                                            </button>
                                            <button 
                                                onClick={() => setStatus('Revision Required')}
                                                className={`flex flex-col items-center gap-3 p-6 rounded-2xl border transition-all
                                                    ${status === 'Revision Required' 
                                                        ? 'bg-blue-500/10 border-blue-500 text-blue-500 shadow-lg shadow-blue-500/10' 
                                                        : 'bg-white/[0.02] border-white/5 text-secondary hover:border-white/20'
                                                    }
                                                `}
                                            >
                                                <RotateCcw size={24} />
                                                <span className="text-xs font-bold uppercase tracking-widest">Revision</span>
                                            </button>
                                        </div>
                                    </div>

                                    {/* Comments */}
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-2 pl-1">
                                            <MessageSquare size={16} className="text-primary" />
                                            <label className="text-[11px] font-black uppercase tracking-widest text-secondary">Reviewer Comments</label>
                                        </div>
                                        <textarea 
                                            className="glass-input min-h-[120px] text-sm leading-relaxed"
                                            placeholder="Provide detailed feedback or reasons for the decision..."
                                            value={comments}
                                            onChange={(e) => setComments(e.target.value)}
                                        />
                                    </div>

                                    {error && (
                                        <p className="text-red-500 text-xs font-bold bg-red-500/10 p-4 rounded-xl border border-red-500/20">
                                            {error}
                                        </p>
                                    )}
                                </div>

                                {/* Footer */}
                                <div className="p-8 border-t border-white/5 flex gap-4">
                                    <button 
                                        onClick={onClose}
                                        className="btn-outline flex-grow font-black text-xs uppercase tracking-widest py-4"
                                    >
                                        Cancel
                                    </button>
                                    <button 
                                        onClick={handleSubmit}
                                        disabled={isSubmitting}
                                        className="btn-premium flex-grow font-black text-xs uppercase tracking-widest py-4 shadow-lg shadow-primary/20"
                                    >
                                        {isSubmitting ? (
                                            <div className="flex items-center justify-center gap-2">
                                                <Loader2 className="animate-spin" />
                                                <span>Processing...</span>
                                            </div>
                                        ) : (
                                            'Submit Decision'
                                        )}
                                    </button>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </motion.div>
            </div>
        </AnimatePresence>
    );
};

export default ReviewModal;
