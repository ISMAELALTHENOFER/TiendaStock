import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './app.jsx';

const root = document.getElementById('react-root');

if (root) {
    const props = JSON.parse(root.dataset.props || '{}');
    createRoot(root).render(<App props={props} />);
}
