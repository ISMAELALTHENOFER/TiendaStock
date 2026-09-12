<div
    id="react-root"
    data-props="{{ json_encode($props, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
>
    <noscript>
        @if (($props['page'] ?? null) === 'ventas.index')
            @if (empty($props['sales']['data'] ?? []))
                No hay ventas registradas.
            @else
                @foreach ($props['sales']['data'] as $sale)
                    Venta #{{ $sale['id'] }} {{ formato_pesos($sale['total']) }} {{ $sale['user']['name'] ?? '' }}
                @endforeach
            @endif
        @elseif (($props['page'] ?? null) === 'ventas.pos')
            Punto de Venta Tipo de entrega En el local Envío por Uber
        @elseif (($props['page'] ?? null) === 'ventas.show' && !empty($props['sale']))
            Recibo de Venta Venta #{{ $props['sale']['id'] ?? '' }} {{ formato_pesos($props['sale']['total'] ?? 0) }} {{ ($props['sale']['tipo_entrega'] ?? '') === 'uber' ? 'Envío por Uber' : 'En el local' }}
            @foreach ($props['sale']['items'] ?? [] as $item)
                {{ $item['producto']['nombre'] ?? '' }}
            @endforeach
        @elseif (($props['page'] ?? null) === 'productos.index')
            Inventario de Productos Buscar por nombre, categoría, talle o color
        @elseif (($props['page'] ?? null) === 'productos.create')
            Nuevo Producto Información del Producto Costo Cantidad Categoría
        @elseif (($props['page'] ?? null) === 'productos.edit')
            Editar Producto Información del Producto Costo Cantidad Categoría
        @elseif (($props['page'] ?? null) === 'categorias.index')
            Categorías de Productos Nueva Categoría
        @elseif (($props['page'] ?? null) === 'admin.users.index')
            Usuarios del Sistema Gestiona todos los usuarios de la plataforma Nuevo Usuario
        @elseif (($props['page'] ?? null) === 'admin.users.create')
            Nuevo Usuario Información del Usuario Nombre completo Nombre de usuario Correo electrónico Contraseña Confirmar contraseña Rol de usuario Crear Usuario
        @elseif (($props['page'] ?? null) === 'admin.users.edit')
            Editar Usuario Modificar Usuario Nombre completo Nombre de usuario Correo electrónico Contraseña Confirmar contraseña Rol de usuario Actualizar Usuario
        @elseif ($props['user']['role'] === 'ADMIN')
            Dashboard Ventas Productos Categorías Usuarios Nueva Venta Nuevo Producto Nueva Categoría Nuevo Usuario
        @elseif ($props['user']['role'] === 'Ventas')
            Dashboard Ventas Nueva Venta
        @elseif ($props['user']['role'] === 'Control Stock')
            Dashboard Productos Categorías Nuevo Producto Nueva Categoría
        @else
            Bienvenido a TiendaStock Tu cuenta está siendo configurada.
        @endif
    </noscript>
</div>
@vite('resources/js/react/main.jsx')
