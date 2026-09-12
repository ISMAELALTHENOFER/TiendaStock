import { AppShell } from './layout/AppShell.jsx';
import { Dashboard } from './dashboard.jsx';
import { Toast } from './components/ui/Toast.jsx';
import { SalesHistory, SalesPos, SaleShow } from './sales.jsx';
import { ProductList, ProductForm } from './productos.jsx';
import { CategoriaGrid } from './categorias.jsx';
import { UserList, UserForm } from './users.jsx';

export default function App({ props }) {
    const page = {
        'ventas.index': <SalesHistory sales={props.sales} query={props.query} routes={props.routes} />,
        'ventas.pos': <SalesPos routes={props.routes} initialErrors={props.errors} />,
        'ventas.show': <SaleShow sale={props.sale} routes={props.routes} />,
        'productos.index': <ProductList categorias={props.categorias} routes={props.routes} />,
        'productos.create': <ProductForm categorias={props.categorias} routes={props.routes} />,
        'productos.edit': <ProductForm producto={props.producto} categorias={props.categorias} routes={props.routes} />,
        'categorias.index': <CategoriaGrid categorias={props.categorias} routes={props.routes} />,
        'admin.users.index': <UserList users={props.users} routes={props.routes} />,
        'admin.users.create': <UserForm routes={props.routes} />,
        'admin.users.edit': <UserForm usuario={props.usuario} routes={props.routes} />,
    }[props.page] || <Dashboard user={props.user} metrics={props.metrics} routes={props.routes} />;

    return <><AppShell user={props.user} routes={props.routes}>{page}</AppShell><Toast message={props.flash?.success || props.flash?.error || props.flash?.warning || props.flash?.info} type="success" /></>;
}
