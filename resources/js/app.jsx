import '../css/app.css';
import './bootstrap';

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import MainApp from './MainApp';

const rootEl = document.getElementById('app');

if (rootEl) {
    createRoot(rootEl).render(
        <StrictMode>
            <MainApp />
        </StrictMode>,
    );
}
