import { Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Eye, Pencil, Trash2, Plus, Package } from 'lucide-react';

export default function ProductosIndex({ productos, categorias }) {
  return (
    <AppLayout title="Inventario">
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 className="text-2xl font-bold text-gray-900">Inventario de Productos</h2>
            <p className="text-gray-500">Gestiona todos tus productos en un solo lugar</p>
          </div>
          <Link href="/productos/create">
            <Button className="gap-2">
              <Plus className="h-4 w-4" />
              Nuevo Producto
            </Button>
          </Link>
        </div>

        {/* Products Table */}
        {productos.data.length > 0 ? (
          <Card className="overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gradient-to-r from-purple-50 to-pink-50 border-b border-purple-200">
                  <tr>
                    <th className="px-4 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Nombre</th>
                    <th className="px-4 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                    <th className="px-4 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider hidden lg:table-cell">Talle</th>
                    <th className="px-4 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider hidden lg:table-cell">Color</th>
                    <th className="px-4 py-3 text-right text-xs font-bold text-purple-900 uppercase tracking-wider hidden sm:table-cell">P. Compra</th>
                    <th className="px-4 py-3 text-right text-xs font-bold text-purple-900 uppercase tracking-wider hidden sm:table-cell">P. Venta</th>
                    <th className="px-4 py-3 text-right text-xs font-bold text-purple-900 uppercase tracking-wider hidden sm:table-cell">Ganancia</th>
                    <th className="px-4 py-3 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Stock</th>
                    <th className="px-4 py-3 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Acciones</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {productos.data.map((producto) => (
                    <tr key={producto.id} className="hover:bg-purple-50 transition-colors">
                      <td className="px-4 py-4">
                        <div className="font-semibold text-gray-900">{producto.nombre}</div>
                        {producto.descripcion && (
                          <div className="text-xs text-gray-500 truncate max-w-xs md:hidden">{producto.descripcion}</div>
                        )}
                      </td>
                      <td className="px-4 py-4 hidden md:table-cell">
                        <span className="inline-block bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800 rounded-full px-3 py-1 text-xs font-semibold">
                          {producto.categoria?.nombre}
                        </span>
                      </td>
                      <td className="px-4 py-4 text-gray-600 hidden lg:table-cell">{producto.talle || '—'}</td>
                      <td className="px-4 py-4 text-gray-600 hidden lg:table-cell">{producto.color || '—'}</td>
                      <td className="px-4 py-4 text-right text-gray-600 font-medium hidden sm:table-cell">
                        ${Number(producto.precio_compra).toFixed(2)}
                      </td>
                      <td className="px-4 py-4 text-right text-gray-600 font-medium hidden sm:table-cell">
                        ${Number(producto.precio_venta).toFixed(2)}
                      </td>
                      <td className="px-4 py-4 text-right hidden sm:table-cell">
                        <span className="text-green-600 font-bold">
                          ${Number(producto.precio_venta - producto.precio_compra).toFixed(2)}
                        </span>
                      </td>
                      <td className="px-4 py-4 text-center">
                        <span className={`inline-block px-3 py-1 rounded-full text-xs font-bold ${
                          producto.cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                        }`}>
                          {producto.cantidad}
                        </span>
                      </td>
                      <td className="px-4 py-4">
                        <div className="flex justify-center gap-1">
                          <Link href={`/productos/${producto.id}`}>
                            <Button variant="ghost" size="icon" className="h-8 w-8 text-purple-600 hover:text-purple-800 hover:bg-purple-50">
                              <Eye className="h-4 w-4" />
                            </Button>
                          </Link>
                          <Link href={`/productos/${producto.id}/edit`}>
                            <Button variant="ghost" size="icon" className="h-8 w-8 text-pink-600 hover:text-pink-800 hover:bg-pink-50">
                              <Pencil className="h-4 w-4" />
                            </Button>
                          </Link>
                          <form 
                            action={`/productos/${producto.id}`} 
                            method="post"
                            className="inline"
                            onSubmit={(e) => {
                              if (!confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                                e.preventDefault();
                              }
                            }}
                          >
                            <input type="hidden" name="_token" value={window.csrfToken} />
                            <input type="hidden" name="_method" value="delete" />
                            <Button variant="ghost" size="icon" className="h-8 w-8 text-red-600 hover:text-red-800 hover:bg-red-50">
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="p-4 bg-gray-50 border-t border-gray-200">
              <div className="flex flex-wrap justify-center gap-2">
                {productos.links.map((link, index) => (
                  link.url ? (
                    <Link
                      key={index}
                      href={link.url}
                      className={`px-3 py-1 rounded-lg text-sm font-medium transition-colors ${
                        link.active
                          ? 'bg-purple-600 text-white'
                          : 'bg-white text-gray-700 hover:bg-purple-50 border border-purple-200'
                      }`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span
                      key={index}
                      className="px-3 py-1 rounded-lg text-sm font-medium text-gray-400"
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  )
                ))}
              </div>
            </div>
          </Card>
        ) : (
          <Card>
            <CardContent className="p-12 text-center">
              <div className="mx-auto h-16 w-16 rounded-full bg-purple-100 flex items-center justify-center mb-4">
                <Package className="h-8 w-8 text-purple-600" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">No hay productos cargados aún</h3>
              <p className="text-gray-500 mb-6">Comienza creando tu primer producto</p>
              <Link href="/productos/create">
                <Button className="gap-2">
                  <Plus className="h-4 w-4" />
                  Crear Primer Producto
                </Button>
              </Link>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}
