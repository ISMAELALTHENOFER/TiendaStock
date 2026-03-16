import { useState } from 'react';
import { Link, useForm, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Package, Eye, Pencil, Trash2, Plus } from 'lucide-react';

export default function CategoriasIndex({ categorias }) {
  return (
    <AppLayout title="Categorías">
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 className="text-2xl font-bold text-gray-900">Categorías de Productos</h2>
            <p className="text-gray-500">Gestiona todas tus categorías de productos</p>
          </div>
          <Link href="/categorias/create">
            <Button className="gap-2">
              <Plus className="h-4 w-4" />
              Nueva Categoría
            </Button>
          </Link>
        </div>

        {/* Success Message */}
        {typeof window !== 'undefined' && window.flashSuccess && (
          <div className="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-2">
            <Package className="h-5 w-5" />
            {window.flashSuccess}
          </div>
        )}

        {/* Categories Grid */}
        {categorias.data.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {categorias.data.map((categoria) => (
              <Card key={categoria.id} className="overflow-hidden group">
                <div className="h-24 bg-gradient-to-r from-purple-600 via-pink-500 to-pink-400 opacity-90 group-hover:opacity-100 transition-opacity"></div>
                <CardContent className="p-6">
                  <h3 className="text-xl font-bold text-gray-900 mb-2">{categoria.nombre}</h3>
                  {categoria.descripcion && (
                    <p className="text-gray-600 text-sm mb-4 line-clamp-2">{categoria.descripcion}</p>
                  )}
                  <div className="inline-block bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800 rounded-full px-4 py-1 text-sm font-semibold mb-4">
                    {categoria.productos_count} {categoria.productos_count === 1 ? 'producto' : 'productos'}
                  </div>
                  <div className="flex gap-2 mt-4">
                    <Link href={`/categorias/${categoria.id}`} className="flex-1">
                      <Button variant="outline" size="sm" className="w-full gap-1">
                        <Eye className="h-4 w-4" />
                        Ver
                      </Button>
                    </Link>
                    <Link href={`/categorias/${categoria.id}/edit`} className="flex-1">
                      <Button variant="outline" size="sm" className="w-full gap-1">
                        <Pencil className="h-4 w-4" />
                        Editar
                      </Button>
                    </Link>
                    <form 
                      action={`/categorias/${categoria.id}`} 
                      method="post" 
                      className="flex-1"
                      onSubmit={(e) => {
                        if (!confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
                          e.preventDefault();
                        }
                      }}
                    >
                      <input type="hidden" name="_token" value={window.csrfToken} />
                      <input type="hidden" name="_method" value="delete" />
                      <Button variant="outline" size="sm" className="w-full gap-1 text-red-600 hover:bg-red-50 hover:text-red-700 hover:border-red-300">
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </form>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="p-12 text-center">
              <div className="mx-auto h-16 w-16 rounded-full bg-purple-100 flex items-center justify-center mb-4">
                <Package className="h-8 w-8 text-purple-600" />
              </div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">No hay categorías creadas aún</h3>
              <p className="text-gray-500 mb-6">Comienza creando tu primera categoría</p>
              <Link href="/categorias/create">
                <Button className="gap-2">
                  <Plus className="h-4 w-4" />
                  Crear Primera Categoría
                </Button>
              </Link>
            </CardContent>
          </Card>
        )}

        {/* Pagination */}
        {categorias.data.length > 0 && (
          <div className="flex justify-center gap-2">
            {categorias.links.map((link, index) => (
              link.url ? (
                <Link
                  key={index}
                  href={link.url}
                  className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                    link.active
                      ? 'bg-purple-600 text-white'
                      : 'bg-white text-gray-700 hover:bg-purple-50 border border-purple-200'
                  }`}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ) : (
                <span
                  key={index}
                  className="px-4 py-2 rounded-lg text-sm font-medium text-gray-400"
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              )
            ))}
          </div>
        )}
      </div>
    </AppLayout>
  );
}
