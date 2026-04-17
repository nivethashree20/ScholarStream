import React, { useState } from 'react';
import { useSearchParams, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Library, ArrowLeft, Loader2 } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

const Login = () => {
    const [searchParams] = useSearchParams();
    const role = searchParams.get('role') || 'student';
    const navigate = useNavigate();
    const { login } = useAuth();

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const roleDisplayName = role === 'admin' ? 'Administrator' : 'Student';

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setIsSubmitting(true);

        const result = await login(email, password, role);
        if (result.success) {
            navigate(role === 'admin' ? '/admin' : '/student');
        } else {
            setError(result.message);
            setIsSubmitting(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center p-6 bg-[#09090b]">
            <motion.div 
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="glass-card w-full max-w-[480px] p-12 text-center"
            >
                <div className="w-20 h-20 bg-[#a855f7] rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-[0_0_40px_rgba(168,85,247,0.3)]">
                    <Library size={48} className="text-black" />
                </div>
                
                <div className="text-[22px] font-bold text-[#a855f7] mb-2 tracking-tight">ScholarStream</div>
                <h2 className="text-[52px] font-bold mb-12 tracking-tighter leading-none">{roleDisplayName} Login</h2>

                <AnimatePresence>
                    {error && (
                        <motion.div 
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl py-3 px-4 mb-8 text-sm font-bold"
                        >
                            {error}
                        </motion.div>
                    )}
                </AnimatePresence>

                <form onSubmit={handleSubmit} className="text-left space-y-8">
                    <div className="space-y-3">
                        <label className="text-[17px] font-bold text-[#a1a1aa] ml-1">Email Address</label>
                        <input 
                            type="email" 
                            className="glass-input h-16 text-lg bg-[#27272a]/30 border-[#3f3f46]/50" 
                            placeholder="Enter your email" 
                            required
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>
                    <div className="space-y-3">
                        <label className="text-[17px] font-bold text-[#a1a1aa] ml-1">Password</label>
                        <input 
                            type="password" 
                            className="glass-input h-16 text-lg bg-[#27272a]/30 border-[#3f3f46]/50" 
                            placeholder="••••••••" 
                            required
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>
                    
                    <button 
                        type="submit" 
                        disabled={isSubmitting}
                        className="btn-premium w-full h-20 text-[20px] font-black rounded-3xl mt-6 shadow-[0_15px_30px_rgba(168,85,247,0.3)] hover:scale-[1.02] active:scale-100 transition-all flex items-center justify-center gap-3"
                    >
                        {isSubmitting ? <Loader2 className="animate-spin" /> : 'Sign In'}
                    </button>
                </form>

                <div className="mt-12 pt-8 border-t border-white/5 space-y-8">
                    {role === 'student' && (
                        <p className="text-[#a1a1aa] font-medium text-[16px]">
                            Don't have an account? <Link to="/register" className="text-white font-black hover:underline ml-1">Sign Up</Link>
                        </p>
                    )}
                    
                    <Link to="/" className="flex items-center justify-center gap-2 text-[#71717a] hover:text-white transition-colors text-[16px] font-medium opacity-80 group">
                        <ArrowLeft size={18} className="group-hover:-translate-x-1 transition-transform" />
                        Back to Portal Selection
                    </Link>
                </div>
            </motion.div>
        </div>
    );
};

export default Login;
