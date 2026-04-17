import React from 'react';
import Sidebar from './Sidebar';
import { motion } from 'framer-motion';

const DashboardLayout = ({ children }) => {
    return (
        <div className="flex min-h-screen bg-bg-dark">
            <Sidebar />
            <motion.main 
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.4 }}
                className="flex-grow p-12 overflow-x-hidden"
            >
                {children}
            </motion.main>
        </div>
    );
};

export default DashboardLayout;
