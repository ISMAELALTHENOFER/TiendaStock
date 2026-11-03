import { useState } from 'react';
import { AppShell } from './layout/AppShell.jsx';
import { Dashboard } from './dashboard.jsx';
import { Toast } from './components/ui/Toast.jsx';
import { SalesHistory, SalesPos, SaleShow } from './sales.jsx';
import { ProductList, ProductForm } from './productos.jsx';
import { CategoriaGrid } from './categorias.jsx';
import { UserList, UserForm } from './users.jsx';
import { ConfirmPassword, Login, Profile } from './auth.jsx';

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
        'categorias.index': <CategoriaGrid categorias={props.categorias} routes={props.routes} />,
        'categorias.create': <CategoriaGrid categorias={props.categorias} routes={props.routes} initialModal />,
        'categorias.edit': <CategoriaGrid categorias={props.categorias} routes={props.routes} initialModal={props.categoria} />,
        'admin.users.index': <UserList users={props.users} routes={props.routes} />,
        'admin.users.create': <UserForm routes={props.routes} />,
        'admin.users.edit': <UserForm usuario={props.usuario} routes={props.routes} />,
    }[props.page] || <Dashboard user={props.user} metrics={props.metrics} categorias={props.categorias} routes={props.routes} />;

    if (props.page === 'login' || props.page === 'password.confirm') return page;
    return <><AppShell user={props.user} routes={props.routes} activePage={props.page}>{page}</AppShell><Toast message={toastVisible ? props.flash?.success || props.flash?.error || props.flash?.warning || props.flash?.info : null} type={flashType} onClose={() => setToastVisible(false)} /></>;
}
