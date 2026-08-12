import { createRoot } from 'react-dom/client';
import AdminApp from './admin/AdminApp';
import '../css/app.css';

const el = document.getElementById('admin-root');

if (el) {
    const userName = el.dataset.userName || 'Admin';
    const userEmail = el.dataset.userEmail || '';

    createRoot(el).render(<AdminApp userName={userName} userEmail={userEmail} />);
}
