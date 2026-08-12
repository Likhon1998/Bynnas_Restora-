/** Static demo data for the Bynnas Restora admin dashboard. */

export const GOLD = '#c47a25';

export const navSections = [
    {
        title: null,
        items: [{ label: 'Dashboard', key: 'dashboard', icon: 'layout' }],
    },
    {
        title: 'Operations',
        items: [
            { label: 'POS', key: 'pos', icon: 'monitor' },
            { label: 'Orders', key: 'orders', icon: 'bag', badge: 24 },
            { label: 'Reservations', key: 'reservations', icon: 'calendar', badge: 18 },
            { label: 'Table Management', key: 'tables', icon: 'grid' },
            { label: 'Customers', key: 'customers', icon: 'users' },
            { label: 'CRM & Loyalty', key: 'crm', icon: 'heart' },
        ],
    },
    {
        title: 'Inventory',
        items: [
            { label: 'Inventory', key: 'inventory', icon: 'box' },
            { label: 'Recipes (BOM)', key: 'recipes', icon: 'book' },
            { label: 'Suppliers', key: 'suppliers', icon: 'truck' },
            { label: 'Purchase Orders', key: 'po', icon: 'file' },
            { label: 'Stock Transfers', key: 'transfers', icon: 'shuffle' },
            { label: 'Wastage & Variance', key: 'wastage', icon: 'alert' },
        ],
    },
    {
        title: 'Finance',
        items: [
            { label: 'Accounting', key: 'accounting', icon: 'wallet' },
            { label: 'Reports', key: 'reports', icon: 'chart' },
            { label: 'Expenses', key: 'expenses', icon: 'receipt' },
            { label: 'Tax Management', key: 'tax', icon: 'percent' },
        ],
    },
    {
        title: 'Staff & Settings',
        items: [
            { label: 'Staff Management', key: 'staff', icon: 'badge' },
            { label: 'Roles & Permissions', key: 'roles', icon: 'shield' },
            { label: 'Settings', key: 'settings', icon: 'settings' },
        ],
    },
];

export const kpis = [
    {
        title: 'Total Revenue',
        value: '৳ 185,420.50',
        change: '+14.6%',
        tone: 'orange',
        spark: [42, 48, 45, 52, 58, 55, 62, 70],
    },
    {
        title: 'Total Orders',
        value: '438',
        change: '+12.3%',
        tone: 'purple',
        spark: [30, 34, 32, 40, 38, 44, 48, 52],
    },
    {
        title: 'Reservations',
        value: '62',
        change: '+8.9%',
        tone: 'blue',
        spark: [18, 20, 22, 19, 24, 28, 26, 30],
    },
    {
        title: 'New Customers',
        value: '128',
        change: '+15.8%',
        tone: 'green',
        spark: [12, 16, 14, 20, 22, 18, 26, 28],
    },
    {
        title: 'Average Order Value',
        value: '৳ 423.45',
        change: '+9.4%',
        tone: 'amber',
        spark: [36, 38, 40, 39, 42, 44, 43, 46],
    },
];

export const tableLegend = [
    { label: 'Seated', count: 12, color: '#3b82f6' },
    { label: 'Ordered', count: 8, color: '#a855f7' },
    { label: 'Preparing', count: 6, color: '#f59e0b' },
    { label: 'Ready', count: 4, color: '#22c55e' },
    { label: 'Waiting', count: 2, color: '#ef4444' },
    { label: 'Available', count: 16, color: '#64748b' },
];

export const floorTables = [
    { id: 1, status: 'seated' },
    { id: 2, status: 'ordered' },
    { id: 3, status: 'preparing' },
    { id: 4, status: 'available' },
    { id: 5, status: 'ready' },
    { id: 6, status: 'seated' },
    { id: 7, status: 'waiting' },
    { id: 8, status: 'available' },
    { id: 9, status: 'ordered' },
    { id: 10, status: 'seated' },
    { id: 11, status: 'available' },
    { id: 12, status: 'preparing' },
    { id: 13, status: 'ready' },
    { id: 14, status: 'available' },
    { id: 15, status: 'seated' },
    { id: 16, status: 'ordered' },
];

export const tableStatusColor = {
    seated: '#3b82f6',
    ordered: '#a855f7',
    preparing: '#f59e0b',
    ready: '#22c55e',
    waiting: '#ef4444',
    available: '#334155',
};

export const liveOrders = [
    { id: '#ORD-1024', source: 'Dine-in', ago: '4m', status: 'Preparing', tone: 'amber' },
    { id: '#ORD-1025', source: 'Delivery', ago: '7m', status: 'Ready', tone: 'green' },
    { id: '#ORD-1026', source: 'Takeaway', ago: '9m', status: 'Preparing', tone: 'amber' },
    { id: '#ORD-1027', source: 'Dine-in', ago: '12m', status: 'On the Way', tone: 'blue' },
    { id: '#ORD-1028', source: 'QR Order', ago: '15m', status: 'Ready', tone: 'green' },
    { id: '#ORD-1029', source: 'Delivery', ago: '18m', status: 'Preparing', tone: 'amber' },
];

export const revenueWeek = [
    { day: 'Mon', value: 14200 },
    { day: 'Tue', value: 15850 },
    { day: 'Wed', value: 17100 },
    { day: 'Thu', value: 16440 },
    { day: 'Fri', value: 19820 },
    { day: 'Sat', value: 24850 },
    { day: 'Sun', value: 22160 },
];

export const inventoryAlerts = [
    { name: 'Mozzarella', left: '5.2 kg', pct: 22, tone: 'red' },
    { name: 'Chicken Breast', left: '8.0 kg', pct: 34, tone: 'amber' },
    { name: 'Olive Oil', left: '2.1 L', pct: 18, tone: 'red' },
    { name: 'Basmati Rice', left: '12 kg', pct: 41, tone: 'amber' },
    { name: 'Fresh Cream', left: '3.4 L', pct: 27, tone: 'red' },
];

export const purchaseOrders = [
    { id: 'PO-2041', supplier: 'Fresh Farm Co.', date: 'May 19', status: 'Sent', tone: 'blue' },
    { id: 'PO-2040', supplier: 'Ocean Catch Ltd.', date: 'May 18', status: 'Received', tone: 'green' },
    { id: 'PO-2039', supplier: 'Dairy Valley', date: 'May 17', status: 'Partially Rec.', tone: 'amber' },
    { id: 'PO-2038', supplier: 'Spice Route', date: 'May 16', status: 'Draft', tone: 'slate' },
];

export const financialSnapshot = [
    { label: 'Total Sales', value: '৳ 185,420.50' },
    { label: 'Cost of Goods Sold', value: '৳ 72,580.20' },
    { label: 'Gross Profit', value: '৳ 112,840.30', meta: '60.8%' },
    { label: 'Total Expenses', value: '৳ 28,450.00' },
    { label: 'Net Profit', value: '৳ 84,390.30', meta: '45.5%', highlight: true },
];

export const topSelling = [
    {
        name: 'Grilled Salmon',
        sold: 86,
        revenue: '৳ 64,500',
        image: 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=120&q=80',
    },
    {
        name: 'Truffle Pasta',
        sold: 74,
        revenue: '৳ 51,800',
        image: 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=120&q=80',
    },
    {
        name: 'Beef Steak',
        sold: 61,
        revenue: '৳ 73,200',
        image: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=120&q=80',
    },
    {
        name: 'Caesar Salad',
        sold: 55,
        revenue: '৳ 24,750',
        image: 'https://images.unsplash.com/photo-1546793665-c74683f339c1?auto=format&fit=crop&w=120&q=80',
    },
    {
        name: 'Chocolate Lava',
        sold: 49,
        revenue: '৳ 19,600',
        image: 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=120&q=80',
    },
];

export const upcomingReservations = [
    { time: '11:00 AM', name: 'Sarah Ahmed', guests: 4, status: 'Confirmed', tone: 'green' },
    { time: '01:00 PM', name: 'James Carter', guests: 2, status: 'Pending', tone: 'amber' },
    { time: '02:30 PM', name: 'Nadia Khan', guests: 6, status: 'Confirmed', tone: 'green' },
    { time: '07:00 PM', name: 'Michael Chen', guests: 3, status: 'Confirmed', tone: 'green' },
    { time: '08:15 PM', name: 'Emily Brooks', guests: 5, status: 'Pending', tone: 'amber' },
];

export const staffPerformance = [
    { label: 'Present', value: '28/32', pct: 87, change: null },
    { label: 'Sales / Staff', value: '৳ 6,622', pct: 74, change: '+12%' },
    { label: 'Orders / Staff', value: '15.6', pct: 68, change: '+8%' },
    { label: 'Labor Cost %', value: '21.6%', pct: 42, change: '-3%' },
];

export const crmStats = [
    { label: 'Total Customers', value: '2,568' },
    { label: 'Members', value: '1,248' },
    { label: 'Points Redeemed', value: '12,540' },
];

export const recentActivities = [
    { text: 'New order #ORD-1029 received', time: '2 min ago', tone: 'orange' },
    { text: 'Table 06 marked as Ready', time: '5 min ago', tone: 'green' },
    { text: 'Reservation confirmed for Nadia Khan', time: '12 min ago', tone: 'blue' },
    { text: 'Stock update: Olive Oil low', time: '18 min ago', tone: 'red' },
    { text: 'PO-2041 sent to Fresh Farm Co.', time: '34 min ago', tone: 'purple' },
    { text: 'Payment captured for #ORD-1022', time: '41 min ago', tone: 'green' },
];
