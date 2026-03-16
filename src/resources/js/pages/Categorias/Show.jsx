import { Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Pencil, ArrowLeft, Package } from 'lucide-react';

export default function CategoriasShow({ categoria }) {
  return (
    <AppLayout title={categoria.nombre}>
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <Link href="/categorias" className="text-gray-500 hover:text-gray-700 flex items-center gap-2">
            <ArrowLeft className="h-4 w-4" />
            Volver
          </Link>
          <div className="flex gap-2">
            <Link href={`/categorias/${categoria.id}/edit`}>
              <Button variant="outline" className="gap-2">
                <Pencil className="h-4 w-4" />
                Editar
              </Button>
            </Link>
          </div>
        </div>

        {/* Category Info */}
        <Card className="mb-6">
          <div className="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-6">
            <h2 className="text-white font-bold text-xl">{categoria.nombre}</h2>
            <p className="text-purple-100 text-sm">Detalles de la categoría</p>
          </div>
          <CardContent className="p-6">
            <dl className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt className="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Nombre</dt>
                <dd className="text-2xl font-bold text-gray-900">{categoria.nombre}</dd>
              </div>
              <div>
                <dt className="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Total de Productos</dt>
                <dd className="text-2xl font-bold text-purple-600">{categoria.productos_count || 0}</dd>
              </div>
              {categoria.descripcion && (
                <div className="md:col-span-2">
                  <dt className="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Descripción</dt>
                  <dd className="text-gray-900 leading-relaxed">{categoria.descripcion}</dd>
                </div>
              )}
            </dl>
          </CardContent>
        </Card>

        {/* Products in Category */}
        <Card>
          <div className="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-4">
            <h3 className="text-white font-bold text-lg">Productos en esta Categoría</h3>
            <p className="text-purple-100 text-sm">{categoria.productos?.length || 0} producto(s)</p>
          </div>
          {categoria.productos && categoria.productos.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Nombre</th>
                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Stock</th>
                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Precio Compra</th>
                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Precio Venta</th>
                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Ganancia</th>
                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Opciones</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {categoria.productos.map((producto) => (
                    <tr key={producto.id} className="hover:bg-gray-50 transition-colors">
                      <td className="px-6 py-4 font-medium text-gray-900">{producto.nombre}</td>
                      <td className="px-6 py-4">
                        <span className={`px-3 py-1 rounded-full text-xs font-bold ${
                          producto.cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                        }`}>
                          {producto.cantidad}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-gray-600">${Number(producto.precio_compra).toFixed(2)}</td>
                      <td className="px-6 py-4 text-gray-600">${Number(producto.precio_venta).toFixed(2)}</td>
                      <td className="px-6 py-4 font-bold text-green-600">
                        ${Number(producto.precio_venta - producto.precio_compra).toFixed(2)}
                      </td>
                      <td className="px-6 py-4">
                        <Link 
                          href={`/productos/${producto.id}`}
                          className="text-purple-600 hover:text-purple-800 text-sm font-medium"
                        >
                          Ver
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <CardContent className="p-8 text-center">
              <div className="mx-auto h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                <Package className="h-6 w-6 text-gray-400" />
              </div>
              <p className="text-gray-500 font-medium">Esta categoría aún no tiene productos</p>
            </CardContent>
          )}
        </Card>
      </div>
    </AppLayout>
  );
}
