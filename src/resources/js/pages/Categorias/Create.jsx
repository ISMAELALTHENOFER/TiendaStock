import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export default function CategoriasCreate({ errors = {} }) {
  const { data, setData, post, processing } = useForm({
    nombre: '',
    descripcion: '',
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    post('/categorias');
  };

  return (
    <AppLayout title="Nueva Categoría">
      <div className="max-w-2xl mx-auto">
        {/* Header */}
        <div className="flex items-center gap-4 mb-6">
          <Link href="/categorias" className="text-gray-500 hover:text-gray-700">
            ← Volver
          </Link>
        </div>

        <Card>
          <div className="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-4">
            <h2 className="text-white font-bold text-lg">Nueva Categoría</h2>
            <p className="text-purple-100 text-sm">Completa los detalles para crear una nueva categoría</p>
          </div>
          <CardContent className="p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Nombre */}
              <div>
                <Label htmlFor="nombre">
                  Nombre de la Categoría <span className="text-red-500">*</span>
                </Label>
                <Input
                  id="nombre"
                  type="text"
                  value={data.nombre}
                  onChange={(e) => setData('nombre', e.target.value)}
                  placeholder="Ej: Ropa, Electrónica, Alimentos..."
                  className="mt-2"
                />
                {errors.nombre && (
                  <p className="text-red-500 text-sm mt-1">{errors.nombre}</p>
                )}
              </div>

              {/* Descripción */}
              <div>
                <Label htmlFor="descripcion">Descripción (Opcional)</Label>
                <Textarea
                  id="descripcion"
                  value={data.descripcion}
                  onChange={(e) => setData('descripcion', e.target.value)}
                  placeholder="Describe los tipos de productos que incluirá esta categoría..."
                  rows={4}
                  className="mt-2"
                />
                {errors.descripcion && (
                  <p className="text-red-500 text-sm mt-1">{errors.descripcion}</p>
                )}
              </div>

              {/* Buttons */}
              <div className="flex gap-4 pt-4">
                <Link href="/categorias" className="flex-1">
                  <Button type="button" variant="secondary" className="w-full">
                    Cancelar
                  </Button>
                </Link>
                <Button type="submit" className="flex-1" disabled={processing}>
                  Crear Categoría
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
