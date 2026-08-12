import AdminShell from './layout/AdminShell';
import DashboardPage from './pages/DashboardPage';

export default function AdminApp({ userName, userEmail }) {
    return (
        <AdminShell userName={userName} userEmail={userEmail}>
            {({ firstName }) => <DashboardPage firstName={firstName} />}
        </AdminShell>
    );
}
