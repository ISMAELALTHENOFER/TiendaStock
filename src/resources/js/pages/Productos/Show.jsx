import { Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Pencil, ArrowLeft } from 'lucide-react';

export default function ProductosShow({ producto }) {
  const ganancia = producto.precio_venta - producto.precio_compra;

  return (
    <AppLayout title={producto.nombre}>
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <Link href="/productos" className="text-gray-500 hover:text-gray-700 flex items-center gap-2">
            <ArrowLeft className="h-4 w-4" />
            Volver
          </Link>
          <div className="flex gap-2">
            <Link href={`/productos/${producto.id}/edit`}>
              <Button variant="outline" className="gap-2">
                <Pencil className="h-4 w-4" />
                Editar
              </Button>
            </Link>
          </div>
        </div>

        {/* Main Info */}
        <Card className="mb-6">
          <div className="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-6">
            <h2 className="text-white font-bold text-xl">{producto.nombre}</h2>
            <p className="text-purple-100 text-sm">Detalles del producto</p>
          </div>
          <CardContent className="p-6">
            <dl className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt className="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Categoría</dt>
                <dd className="text-lg font-bold text-gray-900">{producto.categoria?.nombre}</dd>
              </div>
              <div>
                <dt className="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Stock Actual</dt>
                <dd>
                  <span className={`inline-block px-4 py-2 rounded-full text-sm font-bold ${
                    producto.cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                  }`}>
                    {producto.cantidad} unidades
                  </span>
                </dd>
              </div>
              {producto.talle && (
                <div>
                  <dt className="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Talle</dt>
                  <dd className="text-lg font-semibold text-gray-900">{producto.talle}</dd>
                </div>
              )}
              {producto.color && (
                <div>
                  <dt className="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Color</dt>
                  <dd className="text-lg font-semibold text-gray-900">{producto.color}</dd>
                </div>
              )}
            </dl>
          </CardContent>
        </Card>

        {/* Pricing Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <Card>
            <CardContent className="p-0">
              <div className="bg-blue-50 px-6 py-6">
                <p className="text-sm font-bold text-blue-600 uppercase tracking-wide">Precio de Compra</p>
                <p className="text-3xl font-bold text-blue-700 mt-2">
                  ${Number(producto.precio_compra).toFixed(2)}
                </p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-0">
              <div className="bg-purple-50 px-6 py-6">
                <p className="text-sm font-bold text-purple-600 uppercase tracking-wide">Precio de Venta</p>
                <p className="text-3xl font-bold text-purple-700 mt-2">
                  ${Number(producto.precio_venta).toFixed(2)}
                </p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-0">
              <div className="bg-green-50 px-6 py-6">
                <p className="text-sm font-bold text-green-600 uppercase tracking-wide">Ganancia por Unidad</p>
                <p className="text-3xl font-bold text-green-700 mt-2">
                  ${Number(ganancia).toFixed(2)}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Description */}
        {producto.descripcion && (
          <Card>
            <div className="bg-gradient-to-r from-cyan-600 to-cyan-700 px-6 py-4">
              <h3 className="text-white font-bold text-lg">Descripción</h3>
            </div>
            <CardContent className="p-6">
              <p className="text-gray-700 leading-relaxed text-lg">{producto.descripcion}</p>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}
