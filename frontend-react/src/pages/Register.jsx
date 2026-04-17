import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Library, ArrowLeft, Loader2, CheckCircle2 } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

const Register = () => {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        organization: '',
        password: '',
        confirm_password: ''
    });

    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        if (formData.password !== formData.confirm_password) {
            setError("Passwords do not match.");
            return;
        }

        setIsSubmitting(true);
        try {
            const res = await axios.post('/api/auth/register.php', formData);
            if (res.data.success) {
                setSuccess(true);
                setTimeout(() => navigate('/login?role=student'), 3000);
            } else {
                setError(res.data.message);
                setIsSubmitting(false);
            }
        } catch (err) {
            setError(err.response?.data?.message || "Registration failed");
            setIsSubmitting(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center p-6 bg-bg-dark">
            <motion.div 
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="register-card glass-card w-full max-w-[500px] p-10 text-center"
            >
                <div className="logo-box w-14 h-14 bg-accent rounded-2xl flex items-center justify-center mx-auto mb-6 text-black">
                    <Library size={32} />
                </div>
                <div className="logo-text text-xl font-bold mb-2 tracking-tight text-accent">ScholarStream</div>
                <h2 className="text-4xl font-bold mb-10 tracking-tighter">Student Registration</h2>

                <AnimatePresence mode='wait'>
                    {success ? (
                        <motion.div 
                            key="success"
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            className="space-y-6 py-10"
                        >
                            <div className="w-20 h-20 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/20">
                                <CheckCircle2 size={40} />
                            </div>
                            <h3 className="text-2xl font-bold">Account Created!</h3>
                            <p className="text-secondary">Registration successful. We are redirecting you to the login portal...</p>
                        </motion.div>
                    ) : (
                        <motion.div key="form" exit={{ opacity: 0, scale: 0.9 }}>
                            {error && (
                                <div className="bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl py-3 px-4 mb-6 text-sm">
                                    {error}
                                </div>
                            )}

                            <form onSubmit={handleSubmit} className="text-left space-y-5">
                                <div className="space-y-2">
                                    <label className="text-sm font-semibold text-secondary ml-1">Full Name</label>
                                    <input 
                                        type="text" 
                                        className="glass-input w-full" 
                                        placeholder="John Doe" 
                                        required
                                        value={formData.name}
                                        onChange={(e) => setFormData({...formData, name: e.target.value})}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <label className="text-sm font-semibold text-secondary ml-1">Email Address</label>
                                    <input 
                                        type="email" 
                                        className="glass-input w-full" 
                                        placeholder="john@university.edu" 
                                        required
                                        value={formData.email}
                                        onChange={(e) => setFormData({...formData, email: e.target.value})}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <label className="text-sm font-semibold text-secondary ml-1">Organization / University</label>
                                    <input 
                                        type="text" 
                                        className="glass-input w-full" 
                                        placeholder="University Name" 
                                        required
                                        value={formData.organization}
                                        onChange={(e) => setFormData({...formData, organization: e.target.value})}
                                    />
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold text-secondary ml-1">Password</label>
                                        <input 
                                            type="password" 
                                            className="glass-input w-full" 
                                            placeholder="••••••••" 
                                            required
                                            value={formData.password}
                                            onChange={(e) => setFormData({...formData, password: e.target.value})}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-sm font-semibold text-secondary ml-1">Confirm</label>
                                        <input 
                                            type="password" 
                                            className="glass-input w-full" 
                                            placeholder="••••••••" 
                                            required
                                            value={formData.confirm_password}
                                            onChange={(e) => setFormData({...formData, confirm_password: e.target.value})}
                                        />
                                    </div>
                                </div>
                                <button 
                                    type="submit" 
                                    disabled={isSubmitting}
                                    className="btn-premium w-full mt-4 h-12 text-lg font-bold bg-accent text-black"
                                >
                                    {isSubmitting ? <Loader2 className="animate-spin" /> : 'Create Account'}
                                </button>
                            </form>

                            <div className="mt-8 pt-6 border-t border-white/5">
                                <span className="text-secondary text-sm">Already have an account?</span>
                                <Link to="/login?role=student" className="text-white font-bold text-sm ml-2 hover:underline">Sign In</Link>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>

                <Link to="/" className="flex items-center justify-center gap-2 mt-6 text-secondary hover:text-white transition-colors text-sm opacity-60">
                    <ArrowLeft size={16} />
                    Back to Portal Selection
                </Link>
            </motion.div>
        </div>
    );
};

export default Register;
