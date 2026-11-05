import { lazy, Suspense, useState } from 'react';
import { AppShell } from './layout/AppShell.jsx';
import { Toast } from './components/ui/Toast.jsx';
import { LoadingState } from './components/ui/LoadingState.jsx';

const Dashboard = lazy(() => import('./dashboard.jsx').then((module) => ({ default: module.Dashboard })));
const SalesHistory = lazy(() => import('./sales.jsx').then((module) => ({ default: module.SalesHistory })));
const SalesPos = lazy(() => import('./sales.jsx').then((module) => ({ default: module.SalesPos })));
const SaleShow = lazy(() => import('./sales.jsx').then((module) => ({ default: module.SaleShow })));
const ProductList = lazy(() => import('./productos.jsx').then((module) => ({ default: module.ProductList })));
const ProductForm = lazy(() => import('./productos.jsx').then((module) => ({ default: module.ProductForm })));
const ProductShow = lazy(() => import('./details.jsx').then((module) => ({ default: module.ProductShow })));
const CategoryShow = lazy(() => import('./details.jsx').then((module) => ({ default: module.CategoryShow })));
const CategoriaGrid = lazy(() => import('./categorias.jsx').then((module) => ({ default: module.CategoriaGrid })));
const UserList = lazy(() => import('./users.jsx').then((module) => ({ default: module.UserList })));
const UserForm = lazy(() => import('./users.jsx').then((module) => ({ default: module.UserForm })));
const ConfirmPassword = lazy(() => import('./auth.jsx').then((module) => ({ default: module.ConfirmPassword })));
const Login = lazy(() => import('./auth.jsx').then((module) => ({ default: module.Login })));
const Profile = lazy(() => import('./auth.jsx').then((module) => ({ default: module.Profile })));

export default function App({ props }) {
    const [toastVisible, setToastVisible] = useState(true);
    const flashType = props.flash?.success ? 'success' : props.flash?.error ? 'error' : props.flash?.warning ? 'warning' : 'info';
    const page = {
        login: <Login props={props} />,
        'password.confirm': <ConfirmPassword props={props} />,
        'profile.edit': <Profile props={props} />,
        'ventas.index': <SalesHistory sales={props.sales} query={props.query} routes={props.routes} />,
        'ventas.pos': <SalesPos routes={props.routes} initialErrors={props.errors} lowStockThreshold={props.lowStockThreshold} />,
        'ventas.show': <SaleShow sale={props.sale} routes={props.routes} />,
        'productos.index': <ProductList categorias={props.categorias} routes={props.routes} lowStockThreshold={props.lowStockThreshold} />,
        'productos.create': <ProductForm categorias={props.categorias} routes={props.routes} />,
        'productos.edit': <ProductForm producto={props.producto} categorias={props.categorias} routes={props.routes} />,
        'productos.show': <ProductShow producto={props.producto} routes={props.routes} lowStockThreshold={props.lowStockThreshold} />,
        'categorias.index': <CategoriaGrid categorias={props.categorias} routes={props.routes} />,
        'categorias.create': <CategoriaGrid categorias={props.categorias} routes={props.routes} initialModal />,
        'categorias.edit': <CategoriaGrid categorias={props.categorias} routes={props.routes} initialModal={props.categoria} />,
        'categorias.show': <CategoryShow categoria={props.categoria} productos={props.productosPage} routes={props.routes} canViewProductCost={props.routes.canViewProductCost} query={props.query} />,
        'admin.users.index': <UserList users={props.users} routes={props.routes} />,
        'admin.users.create': <UserForm routes={props.routes} />,
        'admin.users.edit': <UserForm usuario={props.usuario} routes={props.routes} />,
    }[props.page] || <Dashboard user={props.user} metrics={props.metrics} categorias={props.categorias} routes={props.routes} />;

    const content = <Suspense fallback={<LoadingState label="Cargando página…" />}>{page}</Suspense>;
    if (props.page === 'login' || props.page === 'password.confirm') return content;
    return <><AppShell user={props.user} routes={props.routes} activePage={props.page}>{content}</AppShell><Toast message={toastVisible ? props.flash?.success || props.flash?.error || props.flash?.warning || props.flash?.info : null} type={flashType} onClose={() => setToastVisible(false)} /></>;
}
