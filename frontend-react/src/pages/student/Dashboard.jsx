import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { 
    FileText, 
    CheckCircle, 
    Clock, 
    AlertCircle, 
    XCircle,
    Plus,
    Search,
    TrendingUp,
    FileSpreadsheet,
    Edit3
} from 'lucide-react';
import { 
    Chart as ChartJS, 
    CategoryScale, 
    LinearScale, 
    PointElement, 
    LineElement, 
    Title, 
    Tooltip, 
    Legend,
    Filler
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import DashboardLayout from '../../components/layout/DashboardLayout';
import PaperDetailsModal from '../../components/modals/PaperDetailsModal';

ChartJS.register(
    CategoryScale, 
    LinearScale, 
    PointElement, 
    LineElement, 
    Title, 
    Tooltip, 
    Legend,
    Filler
);

const StatCard = ({ icon: Icon, label, value, color, statusColor = 'text-white' }) => (
    <div className="bg-[#18181b] border border-white/5 p-6 rounded-xl flex flex-col gap-6 w-full min-w-[200px]">
        <div className="flex justify-between items-start">
            <span className="font-bold text-[13px] text-white/50 uppercase tracking-tighter">{label}</span>
            <Icon size={18} style={{ color }} />
        </div>
        <div className={`text-4xl font-bold tracking-tight ${statusColor}`}>{value}</div>
    </div>
);

const Dashboard = () => {
    const [data, setData] = useState(null);
    const [papers, setPapers] = useState([]);
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(true);
    const [selectedPaper, setSelectedPaper] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [statsRes, papersRes] = await Promise.all([
                    axios.get('/api/student/dashboard_stats.php'),
                    axios.get(`/api/student/papers.php?search=${search}`)
                ]);
                
                if (statsRes.data.success) setData(statsRes.data.data);
                if (papersRes.data.success) setPapers(papersRes.data.data);
            } catch (err) {
                console.error("Failed to fetch dashboard data", err);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [search]);

    if (loading) return null;

    const chartData = {
        labels: data?.chart.map(item => item.label),
        datasets: [{
            label: 'Submissions',
            data: data?.chart.map(item => item.count),
            borderColor: '#a855f7',
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(168, 85, 247, 0.4)');
                gradient.addColorStop(1, 'rgba(168, 85, 247, 0.0)');
                return gradient;
            },
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 6,
            pointBackgroundColor: '#a855f7',
            pointBorderColor: '#09090b',
            pointBorderWidth: 2,
        }]
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#18181b',
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
                padding: 12,
                titleFont: { size: 14, family: 'Outfit', weight: 'bold' },
                bodyFont: { size: 14, family: 'Outfit' },
            }
        },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
                ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 11 } }
            },
            x: { 
                grid: { display: false },
                ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 11 } }
            }
        }
    };

    return (
        <DashboardLayout>
            <div className="max-w-7xl mx-auto">
                <header className="mb-10">
                    <h1 className="text-4xl font-bold tracking-tighter text-[#a855f7] mb-2 leading-none">Research Paper Details</h1>
                    <p className="text-white/40 text-[15px] font-medium tracking-tight">View and manage your submitted research papers.</p>
                </header>

                <div className="flex flex-col gap-6 mb-10">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <StatCard icon={FileSpreadsheet} label="Total Research Papers" value={data?.stats.total} color="#ffffff" />
                        <StatCard icon={CheckCircle} label="Approved" value={data?.stats.approved} color="#22c55e" />
                        <StatCard icon={Clock} label="Pending" value={data?.stats.pending} color="#eab308" />
                        <StatCard icon={Edit3} label="Revisions Needed" value={data?.stats.revision} color="#f97316" statusColor="text-yellow-500" />
                    </div>
                    
                    <div className="max-w-[400px]">
                        <div className="bg-[#18181b] border border-red-500/10 p-6 rounded-xl flex flex-col gap-6 relative overflow-hidden group">
                           <div className="absolute top-0 right-0 w-12 h-12 bg-red-500/5 rounded-bl-3xl flex items-center justify-center border-l border-b border-red-500/10">
                                <XCircle size={16} className="text-red-500/50" />
                           </div>
                           <div className="flex flex-col gap-4">
                               <span className="font-bold text-[13px] text-red-500 lowercase opacity-60">Declined</span>
                               <div className="text-4xl font-bold tracking-tight text-red-500">{data?.stats.declined}</div>
                           </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-10">
                    <div className="bg-[#18181b] border border-white/5 p-8 rounded-xl shadow-2xl">
                        <div className="flex items-center gap-2 mb-8 pl-1">
                            <TrendingUp size={16} className="text-[#a855f7]" />
                            <h2 className="text-[14px] font-bold tracking-tight text-white/50 uppercase">Your Daily Progress (Last 7 Days)</h2>
                        </div>
                        <div className="h-[340px]">
                            <Line data={chartData} options={chartOptions} />
                        </div>
                    </div>

                    <div className="bg-[#18181b] border border-white/5 rounded-xl shadow-2xl overflow-hidden">
                        <div className="p-8 pb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div className="relative w-full max-w-sm">
                                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20" size={16} />
                                <input 
                                    type="text" 
                                    placeholder="Search by Title..." 
                                    className="bg-black/20 border border-white/5 w-full pl-11 h-12 text-[14px] text-white/80 rounded-lg focus:outline-none focus:border-[#a855f7]/50"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <button className="bg-[#a855f7] hover:bg-[#9333ea] text-black font-black text-[13px] px-6 h-12 rounded-lg flex items-center gap-2 transition-all active:scale-95 shadow-lg shadow-[#a855f7]/20 uppercase">
                                <Plus size={16} strokeWidth={3} />
                                Register New Paper
                            </button>
                        </div>

                        <div className="overflow-x-auto px-8 pb-10 mt-6">
                            <table className="w-full text-left">
                                <thead>
                                    <tr className="text-white/20 text-[11px] font-black uppercase tracking-widest border-b border-white/5">
                                        <th className="pb-6 pl-2">Research Title</th>
                                        <th className="pb-6">Research Area</th>
                                        <th className="pb-6">Status</th>
                                        <th className="pb-6 text-right pr-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-white/5">
                                    {papers.length === 0 ? (
                                        <tr>
                                            <td colSpan="4" className="py-16 text-center text-white/30 text-sm font-medium">
                                                No paper registers found in records.
                                            </td>
                                        </tr>
                                    ) : (
                                        papers.map((paper) => (
                                            <tr key={paper.id} className="group hover:bg-white/[0.01] transition-colors">
                                                <td className="py-7 pl-2 pr-4">
                                                    <span className="font-bold text-[14px] text-white/90 leading-tight group-hover:text-[#a855f7] transition-colors">{paper.title}</span>
                                                </td>
                                                <td className="py-7 pr-4 text-[13px] text-white/40 font-medium whitespace-nowrap">
                                                    {paper.research_area}
                                                </td>
                                                <td className="py-7">
                                                    <span className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border
                                                        ${paper.status === 'Approved' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 
                                                          paper.status === 'Declined' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 
                                                          paper.status === 'Pending' ? 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20' : 
                                                          'bg-blue-500/10 text-blue-500 border-blue-500/20'}
                                                    `}>
                                                        {paper.status}
                                                    </span>
                                                </td>
                                                <td className="py-7 text-right pr-2">
                                                    <button 
                                                        onClick={() => {
                                                            setSelectedPaper(paper);
                                                            setIsModalOpen(true);
                                                        }}
                                                        className="border border-white/10 hover:border-white/30 text-white/80 font-bold text-[12px] px-6 py-2.5 rounded-lg transition-all active:scale-95 bg-white/5"
                                                    >
                                                        View Details
                                                    </button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {selectedPaper && (
                <PaperDetailsModal 
                    paper={selectedPaper}
                    isOpen={isModalOpen}
                    onClose={() => setIsModalOpen(false)}
                />
            )}
        </DashboardLayout>
    );
};

export default Dashboard;
