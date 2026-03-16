import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import Dashboard from './pages/Dashboard';
import CategoriasIndex from './pages/Categorias/Index';
import CategoriasCreate from './pages/Categorias/Create';
import CategoriasEdit from './pages/Categorias/Edit';
import CategoriasShow from './pages/Categorias/Show';
import ProductosIndex from './pages/Productos/Index';
import ProductosCreate from './pages/Productos/Create';
import ProductosEdit from './pages/Productos/Edit';
import ProductosShow from './pages/Productos/Show';
import Login from './pages/Auth/Login';
import Register from './pages/Auth/Register';
import ProfileEdit from './pages/Profile/Edit';

const pages = {
  'Dashboard': Dashboard,
  'Categorias/Index': CategoriasIndex,
  'Categorias/Create': CategoriasCreate,
  'Categorias/Edit': CategoriasEdit,
  'Categorias/Show': CategoriasShow,
  'Productos/Index': ProductosIndex,
  'Productos/Create': ProductosCreate,
  'Productos/Edit': ProductosEdit,
  'Productos/Show': ProductosShow,
  'Auth/Login': Login,
  'Auth/Register': Register,
  'Profile/Edit': ProfileEdit,
};

function App() {
  const [currentPage, setCurrentPage] = React.useState(() => {
    return window.pageName || 'Dashboard';
  });
  const [pageProps, setPageProps] = React.useState(() => {
    return window.pageProps || {};
  });

  React.useEffect(() => {
    const handlePopState = () => {
      const path = window.location.pathname;
      const routeName = getRouteName(path);
      setCurrentPage(routeName);
      setPageProps(window.pageProps || {});
    };

    window.addEventListener('popstate', handlePopState);
    return () => window.removeEventListener('popstate', handlePopState);
  }, []);

  const navigate = (path) => {
    window.history.pushState({}, '', path);
    const routeName = getRouteName(path);
    setCurrentPage(routeName);
    setPageProps(window.pageProps || {});
  };

  const getRouteName = (path) => {
    if (path === '/' || path === '/dashboard') return 'Dashboard';
    if (path === '/productos') return 'Categorias/Index';
    if (path === '/categorias') return 'Categorias/Index';
    if (path.startsWith('/categorias/create')) return 'Categorias/Create';
    if (path.match(/^\/categorias\/\d+\/edit$/)) return 'Categorias/Edit';
    if (path.match(/^\/categorias\/\d+$/)) return 'Categorias/Show';
    if (path === '/productos') return 'Productos/Index';
    if (path.startsWith('/productos/create')) return 'Productos/Create';
    if (path.match(/^\/productos\/\d+\/edit$/)) return 'Productos/Edit';
    if (path.match(/^\/productos\/\d+$/)) return 'Productos/Show';
    if (path === '/login') return 'Auth/Login';
    if (path === '/register') return 'Auth/Register';
    if (path === '/profile') return 'Profile/Edit';
    return 'Dashboard';
  };

  const PageComponent = pages[currentPage];

  if (!PageComponent) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-50 to-pink-50">
        <div className="text-center">
          <h1 className="text-4xl font-bold text-purple-600 mb-4">404</h1>
          <p className="text-gray-600">Página no encontrada</p>
        </div>
      </div>
    );
  }

  return <PageComponent {...pageProps} />;
}

const container = document.getElementById('app');
if (container) {
  const root = createRoot(container);
  root.render(<App />);
}
