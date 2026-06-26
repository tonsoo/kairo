```js
import React, { useState } from 'react';
import {
LayoutDashboard,
History,
CalendarDays,
User,
Play,
Square,
FastForward,
LogOut,
Info,
Clock,
ChevronsUpDown,
Settings
} from 'lucide-react';
import {
PieChart,
Pie,
Cell,
BarChart,
Bar,
XAxis,
YAxis,
CartesianGrid,
Tooltip,
Legend,
ResponsiveContainer
} from 'recharts';

// Mock data for Recharts
const semesterData = [
{ name: 'Jan/26', Trabalhadas: 160, Extras: 0, Faltando: 0 },
{ name: 'Fev/26', Trabalhadas: 165, Extras: 5, Faltando: 0 },
{ name: 'Mar/26', Trabalhadas: 170, Extras: 10, Faltando: 0 },
{ name: 'Abr/26', Trabalhadas: 155, Extras: 0, Faltando: 5 },
{ name: 'Mai/26', Trabalhadas: 40, Extras: 0, Faltando: 120 },
{ name: 'Jun/26', Trabalhadas: 0, Extras: 0, Faltando: 0 },
];

const dailyMonthData = Array.from({ length: 30 }, (_, i) => ({
day: `${i + 1}`,
Trabalhadas: Math.floor(Math.random() * 2) + 7, // 7 to 8 hours
Extras: Math.random() > 0.8 ? 1 : 0,
Faltando: Math.random() > 0.9 ? 1 : 0,
}));

const PIE_COLORS_HOURS = ['#0d9488', '#334155']; // Teal and Slate
const PIE_COLORS_TODAY = ['#0d9488', '#334155', '#eab308']; // Teal, Slate, Yellow

const App = () => {
const [view, setView] = useState('dashboard'); // 'dashboard' | 'history' | 'schedules'
const [workState, setWorkState] = useState('start'); // 'start' | 'end' | 'continue'
const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);

// State for the Weekly Schedule Configuration
const [schedules, setSchedules] = useState({
'Segunda': { type: 'range', start: '08:00', end: '17:00', total: '' },
'Terça': { type: 'range', start: '08:00', end: '17:00', total: '' },
'Quarta': { type: 'range', start: '08:00', end: '17:00', total: '' },
'Quinta': { type: 'total_time', start: '', end: '', total: '08h 00m' },
'Sexta': { type: 'total_time', start: '', end: '', total: '08h 00m' },
'Sábado': { type: 'off', start: '', end: '', total: '' },
'Domingo': { type: 'off', start: '', end: '', total: '' },
});

const handleScheduleChange = (day, field, value) => {
setSchedules(prev => ({
...prev,
[day]: { ...prev[day], [field]: value }
}));
};

const SidebarItem = ({ icon: Icon, label, id, active }) => (
<button
onClick={() => setView(id)}
className={`w-full flex items-center gap-3 px-4 py-3 mb-1 rounded-lg transition-all text-sm font-medium ${
        active 
          ? 'bg-teal-500/10 text-teal-400 border border-teal-500/20' 
          : 'text-slate-400 hover:bg-[#2a2b2d] hover:text-slate-200 border border-transparent'
      }`}
>
<Icon size={18} />
<span className="flex-1 text-left">{label}</span>
</button>
);

const CustomTooltip = ({ active, payload, label }) => {
if (active && payload && payload.length) {
return (
<div className="bg-[#18191a] border border-[#3a3b3c] p-3 rounded shadow-lg text-xs">
<p className="text-slate-200 font-semibold mb-2">{label}</p>
{payload.map((entry, index) => (
<p key={index} style={{ color: entry.color }} className="mb-1">
{entry.name}: {entry.value}h
</p>
))}
</div>
);
}
return null;
};

return (
<div className="min-h-screen bg-[#1e1f20] text-slate-300 flex font-sans">

      {/* LEFT SIDEBAR */}
      <aside className="w-72 bg-[#18191a] border-r border-[#2e2f30] flex flex-col flex-shrink-0 z-20">
        
        {/* Top Spacing */}
        <div className="pt-8"></div>

        {/* Navigation */}
        <div className="flex-1 overflow-y-auto px-4 py-2">
          <div className="space-y-1 mb-8">
            <div className="px-2 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
              Geral
            </div>
            <SidebarItem icon={LayoutDashboard} label="Dashboard" id="dashboard" active={view === 'dashboard'} />
            <SidebarItem icon={History} label="Histórico" id="history" active={view === 'history'} />
          </div>

          <div className="space-y-1">
            <div className="px-2 mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
              Configurações
            </div>
            <SidebarItem icon={CalendarDays} label="Escala Semanal" id="schedules" active={view === 'schedules'} />
          </div>
        </div>

        {/* Bottom Actions & User Profile */}
        <div className="px-4 pb-4 flex flex-col gap-4">
          
          {/* Shift State Action Button */}
          <button 
            onClick={() => setWorkState(workState === 'start' ? 'end' : workState === 'end' ? 'continue' : 'start')}
            className={`w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-md transition-colors font-medium text-sm border ${
              workState === 'start' ? 'bg-teal-500/10 text-teal-400 border-teal-500/20 hover:bg-teal-500/20' : 
              workState === 'end' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20 hover:bg-rose-500/20' : 
              'bg-indigo-500/10 text-indigo-400 border-indigo-500/20 hover:bg-indigo-500/20'
            }`}
          >
            {workState === 'start' && <Play size={16} fill="currentColor" />}
            {workState === 'end' && <Square size={16} fill="currentColor" />}
            {workState === 'continue' && <FastForward size={16} fill="currentColor" />}
            
            <span>
              {workState === 'start' && 'Iniciar Turno'}
              {workState === 'end' && 'Encerrar Turno'}
              {workState === 'continue' && 'Continuar Hoje'}
            </span>
          </button>

          {/* User Menu Layout */}
          <div className="relative">
            {/* Popover Menu */}
            {isUserMenuOpen && (
              <div className="absolute bottom-full left-0 mb-2 w-full bg-[#1e1f20] border border-[#2e2f30] rounded-xl shadow-2xl overflow-hidden flex flex-col z-50">
                <div className="p-4 border-b border-[#2e2f30] flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-[#2a2b2d] flex items-center justify-center text-lg font-medium text-slate-200">A</div>
                  <div className="flex flex-col overflow-hidden">
                    <span className="text-sm font-medium text-slate-200 truncate">Alysson</span>
                    <span className="text-[11px] text-slate-400 truncate" title="contato@alysson-thoaldo.com">contato@alysson-thoald...</span>
                  </div>
                </div>
                <div className="p-1.5">
                  <button className="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:text-white hover:bg-[#2a2b2d] rounded-lg transition-colors">
                    <Settings size={16} className="text-slate-400" /> Settings
                  </button>
                  <button className="w-full flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:text-white hover:bg-[#2a2b2d] rounded-lg transition-colors">
                    <LogOut size={16} className="text-slate-400" /> Log out
                  </button>
                </div>
              </div>
            )}

            {/* Menu Trigger Button */}
            <button 
              onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
              className="w-full flex items-center justify-between p-2 bg-[#242526] hover:bg-[#2a2b2d] rounded-xl transition-colors border border-[#2e2f30]"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-lg bg-[#3a3b3c] flex items-center justify-center text-sm font-medium text-slate-200">A</div>
                <span className="text-sm font-medium text-slate-200">Alysson</span>
              </div>
              <ChevronsUpDown size={16} className="text-slate-500 mr-1" />
            </button>
          </div>
        </div>
      </aside>

      {/* MAIN CONTENT AREA */}
      <main className="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[#1e1f20]">
        
        <div className="p-8 max-w-[1600px] mx-auto w-full">
          
          {view === 'dashboard' && (
            <div className="space-y-6">
              
              <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {/* Left Column: Donut Charts */}
                <div className="lg:col-span-3 space-y-6">
                  {/* Banco de Horas Donut */}
                  <div className="bg-[#242526] border border-[#2e2f30] rounded-xl p-5 shadow-sm">
                    <div className="flex justify-between items-start mb-4">
                      <h3 className="text-slate-200 font-medium">Banco de horas: <span className="text-teal-400 font-bold">01:13</span></h3>
                      <Info size={16} className="text-amber-500 cursor-pointer" />
                    </div>
                    <div className="h-48 w-full">
                      <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                          <Pie
                            data={[
                              { name: 'Positivo', value: 75 },
                              { name: 'Restante', value: 25 },
                            ]}
                            innerRadius={50}
                            outerRadius={70}
                            paddingAngle={5}
                            dataKey="value"
                            stroke="none"
                          >
                            {PIE_COLORS_HOURS.map((color, index) => (
                              <Cell key={`cell-${index}`} fill={color} />
                            ))}
                          </Pie>
                        </PieChart>
                      </ResponsiveContainer>
                    </div>
                    <div className="text-center">
                      <button className="text-teal-500 text-sm hover:text-teal-400 transition-colors">Detalhar</button>
                    </div>
                  </div>

                  {/* Hoje Donut */}
                  <div className="bg-[#242526] border border-[#2e2f30] rounded-xl p-5 shadow-sm">
                    <h3 className="text-slate-200 font-medium mb-4">Hoje | <span className="text-white font-bold">04:44</span></h3>
                    <div className="h-48 w-full relative">
                      <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                          <Pie
                            data={[
                              { name: 'Trabalhado', value: 284 },
                              { name: 'Restante', value: 196 },
                            ]}
                            innerRadius={50}
                            outerRadius={70}
                            paddingAngle={2}
                            dataKey="value"
                            stroke="none"
                          >
                            {PIE_COLORS_TODAY.map((color, index) => (
                              <Cell key={`cell-${index}`} fill={color} />
                            ))}
                          </Pie>
                        </PieChart>
                      </ResponsiveContainer>
                    </div>
                    <div className="flex justify-center gap-4 text-xs text-slate-400 mt-2">
                      <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-teal-500"></span> Online</div>
                      <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-amber-500"></span> Pausa</div>
                      <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-slate-600"></span> Faltando</div>
                    </div>
                  </div>
                </div>

                {/* Center/Right Column: Semester Bar Chart */}
                <div className="lg:col-span-9 bg-[#242526] border border-[#2e2f30] rounded-xl p-6 shadow-sm flex flex-col">
                  <div className="flex items-center gap-3 mb-6">
                    <div className="flex gap-1">
                      <button className="w-6 h-6 rounded-full bg-[#18191a] border border-[#3a3b3c] flex items-center justify-center text-slate-400 hover:text-white">&larr;</button>
                      <button className="w-6 h-6 rounded-full bg-[#18191a] border border-[#3a3b3c] flex items-center justify-center text-slate-400 hover:text-white">&rarr;</button>
                    </div>
                    <h2 className="text-lg font-medium text-slate-200">Resumo do semestre</h2>
                  </div>
                  
                  <div className="flex-1 w-full min-h-[300px]">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={semesterData} margin={{ top: 20, right: 30, left: -20, bottom: 5 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#334155" vertical={false} />
                        <XAxis dataKey="name" stroke="#64748b" tick={{ fill: '#64748b', fontSize: 12 }} axisLine={false} tickLine={false} />
                        <YAxis stroke="#64748b" tick={{ fill: '#64748b', fontSize: 12 }} axisLine={false} tickLine={false} tickFormatter={(val) => `${val}H`} />
                        <Tooltip content={<CustomTooltip />} />
                        <Legend wrapperStyle={{ fontSize: '12px', color: '#cbd5e1' }} />
                        <Bar dataKey="Trabalhadas" stackId="a" fill="#0d9488" />
                        <Bar dataKey="Extras" stackId="a" fill="#be123c" />
                        <Bar dataKey="Faltando" stackId="a" fill="#475569" />
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </div>
              </div>

              {/* Bottom Row: Monthly Daily Breakdown */}
              <div className="bg-[#242526] border border-[#2e2f30] rounded-xl p-6 shadow-sm">
                <div className="flex items-center gap-3 mb-6">
                  <div className="flex gap-1">
                    <button className="w-6 h-6 rounded-full bg-[#18191a] border border-[#3a3b3c] flex items-center justify-center text-slate-400 hover:text-white">&larr;</button>
                    <button className="w-6 h-6 rounded-full bg-[#18191a] border border-[#3a3b3c] flex items-center justify-center text-slate-400 hover:text-white">&rarr;</button>
                  </div>
                  <h2 className="text-lg font-medium text-slate-200">2026 | Junho | Banco de horas: 00:00</h2>
                  
                  <div className="ml-auto flex items-center gap-4 text-sm text-slate-400">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="radio" name="viewType" className="accent-teal-500" defaultChecked /> Resumo
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="radio" name="viewType" className="accent-teal-500" /> Jornada
                    </label>
                  </div>
                </div>

                <div className="h-64 w-full">
                  <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={dailyMonthData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                      <CartesianGrid strokeDasharray="3 3" stroke="#334155" vertical={false} />
                      <XAxis dataKey="day" stroke="#64748b" tick={{ fill: '#64748b', fontSize: 11 }} axisLine={false} tickLine={false} />
                      <YAxis stroke="#64748b" tick={{ fill: '#64748b', fontSize: 11 }} axisLine={false} tickLine={false} tickFormatter={(val) => `${val}H`} />
                      <Tooltip content={<CustomTooltip />} />
                      <Bar dataKey="Trabalhadas" stackId="a" fill="#0d9488" />
                      <Bar dataKey="Extras" stackId="a" fill="#be123c" />
                      <Bar dataKey="Faltando" stackId="a" fill="#475569" />
                    </BarChart>
                  </ResponsiveContainer>
                </div>
              </div>
            </div>
          )}

          {view === 'history' && (
             <div className="max-w-[1000px]">
               <h2 className="text-2xl font-semibold text-white mb-6">Histórico de Turnos</h2>
               <div className="bg-[#242526] border border-[#2e2f30] rounded-xl overflow-hidden shadow-sm">
                 <table className="w-full text-sm text-left">
                   <thead className="text-xs text-slate-400 uppercase bg-[#18191a] border-b border-[#2e2f30]">
                     <tr>
                       <th className="px-6 py-4 font-medium">Data</th>
                       <th className="px-6 py-4 font-medium">Entrada</th>
                       <th className="px-6 py-4 font-medium">Saída</th>
                       <th className="px-6 py-4 font-medium">Duração</th>
                       <th className="px-6 py-4 font-medium text-right">Ações</th>
                     </tr>
                   </thead>
                   <tbody className="divide-y divide-[#2e2f30]">
                     {[
                       { date: '25 Jun 2026', start: '08:00', end: '17:30', duration: '08h 30m' },
                       { date: '24 Jun 2026', start: '08:15', end: '17:00', duration: '07h 45m' },
                       { date: '23 Jun 2026', start: '07:50', end: '17:00', duration: '08h 10m' },
                       { date: '22 Jun 2026', start: '08:00', end: '12:00', duration: '04h 00m' },
                     ].map((shift, i) => (
                       <tr key={i} className="hover:bg-[#2a2b2d] transition-colors">
                         <td className="px-6 py-4 text-slate-200">{shift.date}</td>
                         <td className="px-6 py-4 text-teal-400">{shift.start}</td>
                         <td className="px-6 py-4 text-rose-400">{shift.end}</td>
                         <td className="px-6 py-4 text-slate-300 font-medium">{shift.duration}</td>
                         <td className="px-6 py-4 text-right">
                           <button className="text-indigo-400 hover:text-indigo-300 text-xs font-medium uppercase tracking-wider">Editar</button>
                         </td>
                       </tr>
                     ))}
                   </tbody>
                 </table>
               </div>
             </div>
          )}

          {view === 'schedules' && (
             <div className="max-w-[1000px]">
               <div className="flex justify-between items-center mb-6">
                 <div>
                   <h2 className="text-2xl font-semibold text-white">Escala Semanal</h2>
                   <p className="text-sm text-slate-400 mt-1">Configure sua rotina padrão. Apenas uma escala é ativa por vez.</p>
                 </div>
                 <button className="bg-teal-600 hover:bg-teal-500 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-teal-900/20">
                   Salvar Alterações
                 </button>
               </div>
               
               <div className="bg-[#242526] border border-[#2e2f30] rounded-xl p-6 shadow-sm">
                 <div className="grid grid-cols-[140px_1fr_1.5fr] gap-6 mb-4 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:grid">
                   <div>Dia da Semana</div>
                   <div>Tipo de Escala</div>
                   <div>Configuração</div>
                 </div>
                 
                 <div className="space-y-3">
                   {Object.entries(schedules).map(([day, config]) => (
                     <div key={day} className="grid grid-cols-1 md:grid-cols-[140px_1fr_1.5fr] gap-6 items-center bg-[#1e1f20] p-4 rounded-lg border border-[#2e2f30] hover:border-[#3a3b3c] transition-colors">
                       <div className="font-medium text-slate-200">{day}</div>
                       
                       <div>
                         <select 
                           value={config.type}
                           onChange={(e) => handleScheduleChange(day, 'type', e.target.value)}
                           className="bg-[#141515] border border-[#3a3b3c] text-slate-200 text-sm rounded-md px-3 py-2 outline-none focus:border-teal-500 w-full appearance-none"
                         >
                           <option value="range">Intervalo Fixo</option>
                           <option value="total_time">Horas Flexíveis</option>
                           <option value="off">Folga</option>
                         </select>
                       </div>

                       <div className="flex items-center gap-3">
                         {config.type === 'range' && (
                           <>
                             <input 
                               type="time" 
                               value={config.start} 
                               onChange={(e) => handleScheduleChange(day, 'start', e.target.value)}
                               className="bg-[#141515] border border-[#3a3b3c] text-slate-200 text-sm rounded-md px-3 py-2 outline-none focus:border-teal-500" 
                             />
                             <span className="text-slate-500 text-sm">até</span>
                             <input 
                               type="time" 
                               value={config.end} 
                               onChange={(e) => handleScheduleChange(day, 'end', e.target.value)}
                               className="bg-[#141515] border border-[#3a3b3c] text-slate-200 text-sm rounded-md px-3 py-2 outline-none focus:border-teal-500" 
                             />
                           </>
                         )}
                         {config.type === 'total_time' && (
                           <div className="relative">
                             <input 
                               type="text" 
                               value={config.total} 
                               onChange={(e) => handleScheduleChange(day, 'total', e.target.value)}
                               placeholder="Ex: 08h 00m" 
                               className="bg-[#141515] border border-[#3a3b3c] text-slate-200 text-sm rounded-md px-3 py-2 outline-none focus:border-teal-500 w-40" 
                             />
                             <span className="absolute right-3 top-2.5 text-xs text-slate-500">Total</span>
                           </div>
                         )}
                         {config.type === 'off' && (
                           <span className="text-slate-500 text-sm italic flex items-center gap-2">
                             <span className="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                             Sem expediente
                           </span>
                         )}
                       </div>
                     </div>
                   ))}
                 </div>
               </div>
             </div>
          )}

        </div>
      </main>
    </div>
);
};

export default App;
```
