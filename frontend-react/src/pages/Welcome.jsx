import React from 'react';
import { Link } from 'react-router-dom';
import { Library, GraduationCap, UserCog, ArrowRight } from 'lucide-react';
import { motion } from 'framer-motion';

const Welcome = () => {
    return (
        <div className="min-h-screen flex items-center justify-center p-6 bg-[#09090b]">
            <motion.div 
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, ease: "easeOut" }}
                className="glass-card w-full max-w-[560px] p-12 text-center"
            >
                <div className="flex flex-col items-center mb-10">
                    <div className="w-16 h-16 bg-[#a855f7] rounded-xl flex items-center justify-center text-white mb-6 shadow-[0_0_30px_rgba(168,85,247,0.3)]">
                        <Library size={36} />
                    </div>
                    <div className="text-2xl font-bold text-[#a855f7] tracking-tight">ScholarStream</div>
                </div>

                <h1 className="text-[52px] font-bold mb-4 tracking-tighter leading-[1.1]">
                    Welcome to <br /> ScholarStream
                </h1>
                <p className="text-[#a1a1aa] text-lg mb-12 max-w-[400px] mx-auto leading-relaxed">
                    The platform for academic research submission and review. Please select your role to continue.
                </p>

                <div className="flex flex-col gap-4">
                    <Link to="/login?role=student" className="flex items-center p-6 rounded-2xl bg-[#18181b] border border-[#27272a] hover:bg-[#27272a] hover:border-[#a855f7]/50 transition-all group">
                        <div className="w-14 h-14 bg-[#27272a] rounded-xl flex items-center justify-center mr-6 text-[#a1a1aa] group-hover:text-[#a855f7] transition-colors">
                            <GraduationCap size={28} />
                        </div>
                        <div className="flex-grow text-left">
                            <h3 className="text-xl font-bold mb-0.5">Student Portal</h3>
                            <p className="text-[#71717a] text-sm">Submit and track your research papers.</p>
                        </div>
                        <ArrowRight size={20} className="text-[#3f3f46] group-hover:text-[#a855f7] group-hover:translate-x-1 transition-all" />
                    </Link>

                    <Link to="/login?role=admin" className="flex items-center p-6 rounded-2xl bg-[#18181b] border border-[#27272a] hover:bg-[#27272a] hover:border-[#a855f7]/50 transition-all group">
                        <div className="w-14 h-14 bg-[#27272a] rounded-xl flex items-center justify-center mr-6 text-[#a1a1aa] group-hover:text-[#a855f7] transition-colors">
                            <UserCog size={28} />
                        </div>
                        <div className="flex-grow text-left">
                            <h3 className="text-xl font-bold mb-0.5">Admin Portal</h3>
                            <p className="text-[#71717a] text-sm">Review and manage submissions.</p>
                        </div>
                        <ArrowRight size={20} className="text-[#3f3f46] group-hover:text-[#a855f7] group-hover:translate-x-1 transition-all" />
                    </Link>
                </div>
            </motion.div>
        </div>
    );
};

export default Welcome;
