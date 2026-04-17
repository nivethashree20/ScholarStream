import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { 
    FileUp, 
    ArrowLeft, 
    Loader2, 
    CheckCircle2, 
    Users,
    ChevronRight,
    Info
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { useAuth } from '../../context/AuthContext';
import DashboardLayout from '../../components/layout/DashboardLayout';

const SubmitPaper = () => {
    const navigate = useNavigate();
    const { user } = useAuth();
    
    const [formData, setFormData] = useState({
        academic_year: '',
        semester: '',
        department: '',
        research_area: '',
        title: '',
        guide_name: '',
        abstract: '',
        co_authors: []
    });

    const [file, setFile] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState('');
    const [coAuthorEmail, setCoAuthorEmail] = useState('');

    const departments = [
        "Computer Science Engineering", "Information Science & Engineering", 
        "Artificial Intelligence & Data Science", "Information Technology",
        "Electronics & Communication Engineering", "Electrical & Electronics Engineering",
        "Biotechnology", "Biomedical Engineering", "Civil Engineering", 
        "Mechanical Engineering", "Chemical Engineering", "Aerospace Engineering"
    ];

    const academicYears = ["2022-2026", "2023-2027", "2024-2028", "2025-2029"];
    
    const getSemesters = (year) => {
        const data = {
            '2022-2026': ['Semester 7', 'Semester 8'],
            '2023-2027': ['Semester 5', 'Semester 6'],
            '2024-2028': ['Semester 3', 'Semester 4'],
            '2025-2029': ['Semester 1', 'Semester 2']
        };
        return data[year] || [];
    };

    const handleAddCoAuthor = () => {
        if (coAuthorEmail && !formData.co_authors.includes(coAuthorEmail)) {
            setFormData({ ...formData, co_authors: [...formData.co_authors, coAuthorEmail] });
            setCoAuthorEmail('');
        }
    };

    const handleRemoveCoAuthor = (email) => {
        setFormData({ ...formData, co_authors: formData.co_authors.filter(e => e !== email) });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setIsSubmitting(true);

        const data = new FormData();
        Object.keys(formData).forEach(key => {
            if (key === 'co_authors') {
                data.append(key, JSON.stringify(formData[key]));
            } else {
                data.append(key, formData[key]);
            }
        });
        if (file) data.append('research_paper', file);

        try {
            const res = await axios.post('/api/student/submit_paper.php', data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            if (res.data.success) {
                navigate('/student');
            } else {
                setError(res.data.message);
                setIsSubmitting(false);
            }
        } catch (err) {
            setError(err.response?.data?.message || "Submission failed");
            setIsSubmitting(false);
        }
    };

    return (
        <DashboardLayout>
            <div className="max-w-4xl mx-auto pb-20">
                <button onClick={() => navigate(-1)} className="flex items-center gap-2 text-secondary hover:text-white transition-colors mb-8 group">
                    <ArrowLeft size={20} className="group-hover:-translate-x-1 transition-transform" />
                    Back to Dashboard
                </button>

                <header className="mb-12">
                    <h1 className="text-5xl font-bold tracking-tighter mb-4">Paper Registration</h1>
                    <p className="text-secondary text-xl">Fill out the details below to submit your research for review.</p>
                </header>

                <AnimatePresence>
                    {error && (
                        <motion.div 
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl mb-8 flex items-center gap-3"
                        >
                            <Info size={20} />
                            {error}
                        </motion.div>
                    )}
                </AnimatePresence>

                <form onSubmit={handleSubmit} className="space-y-8">
                    <div className="glass-card p-10 space-y-8">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div className="space-y-2">
                                <label className="text-sm font-semibold text-secondary ml-1">Academic Year</label>
                                <select 
                                    className="glass-input w-full" 
                                    required
                                    value={formData.academic_year}
                                    onChange={(e) => setFormData({...formData, academic_year: e.target.value, semester: ''})}
                                >
                                    <option value="">Select Year</option>
                                    {academicYears.map(year => <option key={year} value={year}>{year}</option>)}
                                </select>
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-semibold text-secondary ml-1">Semester</label>
                                <select 
                                    className="glass-input w-full" 
                                    required
                                    disabled={!formData.academic_year}
                                    value={formData.semester}
                                    onChange={(e) => setFormData({...formData, semester: e.target.value})}
                                >
                                    <option value="">Select Semester</option>
                                    {getSemesters(formData.academic_year).map(sem => <option key={sem} value={sem}>{sem}</option>)}
                                </select>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <label className="text-sm font-semibold text-secondary ml-1">Department</label>
                            <select 
                                className="glass-input w-full" 
                                required
                                value={formData.department}
                                onChange={(e) => setFormData({...formData, department: e.target.value})}
                            >
                                <option value="">Select Department</option>
                                {departments.map(dept => <option key={dept} value={dept}>{dept}</option>)}
                            </select>
                        </div>

                        <div className="space-y-2">
                            <label className="text-sm font-semibold text-secondary ml-1">Research Area</label>
                            <input 
                                type="text" 
                                className="glass-input w-full" 
                                placeholder="e.g. Machine Learning, Structural Biology"
                                required
                                value={formData.research_area}
                                onChange={(e) => setFormData({...formData, research_area: e.target.value})}
                            />
                        </div>

                        <div className="space-y-2">
                            <label className="text-sm font-semibold text-secondary ml-1">Paper Title</label>
                            <input 
                                type="text" 
                                className="glass-input w-full text-lg font-bold" 
                                placeholder="Enter full research title"
                                required
                                value={formData.title}
                                onChange={(e) => setFormData({...formData, title: e.target.value})}
                            />
                        </div>

                        <div className="space-y-2">
                            <label className="text-sm font-semibold text-secondary ml-1">Research Guide</label>
                            <input 
                                type="text" 
                                className="glass-input w-full" 
                                placeholder="Enter Guide Name"
                                required
                                value={formData.guide_name}
                                onChange={(e) => setFormData({...formData, guide_name: e.target.value})}
                            />
                        </div>
                    </div>

                    <div className="glass-card p-10 space-y-6">
                        <div className="flex items-center gap-2 mb-2">
                            <Users size={20} className="text-primary" />
                            <h3 className="font-bold text-lg">Co-authors</h3>
                        </div>
                        <div className="flex gap-4">
                            <input 
                                type="email" 
                                className="glass-input flex-grow" 
                                placeholder="Enter co-author's registered email"
                                value={coAuthorEmail}
                                onChange={(e) => setCoAuthorEmail(e.target.value)}
                            />
                            <button 
                                type="button" 
                                onClick={handleAddCoAuthor}
                                className="btn-outline"
                            >
                                Add Author
                            </button>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {formData.co_authors.map(email => (
                                <span key={email} className="bg-primary/10 border border-primary/20 text-primary-glow px-4 py-2 rounded-full text-sm flex items-center gap-3">
                                    {email}
                                    <button type="button" onClick={() => handleRemoveCoAuthor(email)} className="hover:text-white">×</button>
                                </span>
                            ))}
                        </div>
                    </div>

                    <div className="glass-card p-10 space-y-6">
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <label className="text-sm font-semibold text-secondary ml-1">Abstract</label>
                                <span className={`text-xs ${formData.abstract.split(/\s+/).filter(Boolean).length > 500 ? 'text-red-500' : 'text-secondary'}`}>
                                    {formData.abstract.split(/\s+/).filter(Boolean).length} / 500 words
                                </span>
                            </div>
                            <textarea 
                                className="glass-input w-full min-h-[200px]" 
                                placeholder="Provide a concise summary of your research..."
                                required
                                value={formData.abstract}
                                onChange={(e) => setFormData({...formData, abstract: e.target.value})}
                            />
                        </div>

                        <div className="space-y-4">
                            <label className="text-sm font-semibold text-secondary ml-1">Upload PDF File</label>
                            <div className={`
                                border-2 border-dashed rounded-2xl p-10 text-center transition-all
                                ${file ? 'border-primary bg-primary/5' : 'border-white/10 hover:border-white/20'}
                            `}>
                                <input 
                                    type="file" 
                                    accept=".pdf" 
                                    className="hidden" 
                                    id="fileUpload" 
                                    onChange={(e) => setFile(e.target.files[0])}
                                    required
                                />
                                <label htmlFor="fileUpload" className="cursor-pointer flex flex-col items-center">
                                    <div className="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mb-4 text-secondary">
                                        <FileUp size={32} />
                                    </div>
                                    {file ? (
                                        <div className="text-primary font-bold flex items-center gap-2">
                                            <CheckCircle2 size={18} />
                                            {file.name}
                                        </div>
                                    ) : (
                                        <>
                                            <p className="font-bold mb-1">Click to upload research paper</p>
                                            <p className="text-secondary text-sm">PDF format only (Max 10MB)</p>
                                        </>
                                    )}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end pt-4">
                        <button 
                            type="submit" 
                            disabled={isSubmitting}
                            className="btn-premium px-12 py-4 text-xl"
                        >
                            {isSubmitting ? (
                                <>
                                    <Loader2 className="animate-spin" />
                                    Submitting Paper...
                                </>
                            ) : (
                                <>
                                    Submit for Review
                                    <ChevronRight size={20} />
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </DashboardLayout>
    );
};

export default SubmitPaper;
