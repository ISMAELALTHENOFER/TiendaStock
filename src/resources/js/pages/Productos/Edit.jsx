import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectOption } from '@/components/ui/select';

export default function ProductosEdit({ producto, categorias, errors = {} }) {
  const { data, setData, put, processing } = useForm({
    nombre: producto.nombre || '',
    categoria_id: producto.categoria_id || '',
    cantidad: producto.cantidad || 0,
    precio_compra: producto.precio_compra || '',
    precio_venta: producto.precio_venta || '',
    talle: producto.talle || '',
    color: producto.color || '',
    descripcion: producto.descripcion || '',
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    put(`/productos/${producto.id}`);
  };

  const talles = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Único'];

  return (
    <AppLayout title="Editar Producto">
      <div className="max-w-3xl mx-auto">
        {/* Header */}
        <div className="flex items-center gap-4 mb-6">
          <Link href="/productos" className="text-gray-500 hover:text-gray-700">
            ← Volver
          </Link>
        </div>

        <Card>
          <div className="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
            <h2 className="text-white font-bold text-lg">Modificar Producto</h2>
            <p className="text-amber-100 text-sm">Actualiza los detalles del producto</p>
          </div>
          <CardContent className="p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Nombre */}
                <div className="md:col-span-2">
                  <Label htmlFor="nombre">
                    Nombre del Producto <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="nombre"
                    type="text"
                    value={data.nombre}
                    onChange={(e) => setData('nombre', e.target.value)}
                    placeholder="Ej: Remera Deportiva, Zapatillas..."
                    className="mt-2"
                  />
                  {errors.nombre && (
                    <p className="text-red-500 text-sm mt-1">{errors.nombre}</p>
                  )}
                </div>

                {/* Categoría */}
                <div>
                  <Label htmlFor="categoria_id">
                    Categoría <span className="text-red-500">*</span>
                  </Label>
                  <Select
                    id="categoria_id"
                    value={data.categoria_id}
                    onChange={(e) => setData('categoria_id', e.target.value)}
                    className="mt-2"
                  >
                    <option value="">-- Seleccionar --</option>
                    {categorias.map((cat) => (
                      <SelectOption key={cat.id} value={cat.id}>
                        {cat.nombre}
                      </SelectOption>
                    ))}
                  </Select>
                  {errors.categoria_id && (
                    <p className="text-red-500 text-sm mt-1">{errors.categoria_id}</p>
                  )}
                </div>

                {/* Cantidad */}
                <div>
                  <Label htmlFor="cantidad">
                    Cantidad <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="cantidad"
                    type="number"
                    value={data.cantidad}
                    onChange={(e) => setData('cantidad', e.target.value)}
                    min="0"
                    className="mt-2"
                  />
                  {errors.cantidad && (
                    <p className="text-red-500 text-sm mt-1">{errors.cantidad}</p>
                  )}
                </div>

                {/* Precio Compra */}
                <div>
                  <Label htmlFor="precio_compra">
                    Precio de Compra <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="precio_compra"
                    type="number"
                    value={data.precio_compra}
                    onChange={(e) => setData('precio_compra', e.target.value)}
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    className="mt-2"
                  />
                  {errors.precio_compra && (
                    <p className="text-red-500 text-sm mt-1">{errors.precio_compra}</p>
                  )}
                </div>

                {/* Precio Venta */}
                <div>
                  <Label htmlFor="precio_venta">
                    Precio de Venta <span className="text-red-500">*</span>
                  </Label>
                  <Input
                    id="precio_venta"
                    type="number"
                    value={data.precio_venta}
                    onChange={(e) => setData('precio_venta', e.target.value)}
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    className="mt-2"
                  />
                  {errors.precio_venta && (
                    <p className="text-red-500 text-sm mt-1">{errors.precio_venta}</p>
                  )}
                </div>

                {/* Talle */}
                <div>
                  <Label htmlFor="talle">Talle (Opcional)</Label>
                  <Select
                    id="talle"
                    value={data.talle}
                    onChange={(e) => setData('talle', e.target.value)}
                    className="mt-2"
                  >
                    <option value="">-- Sin talle --</option>
                    {talles.map((t) => (
                      <SelectOption key={t} value={t}>{t}</SelectOption>
                    ))}
                  </Select>
                </div>

                {/* Color */}
                <div>
                  <Label htmlFor="color">Color (Opcional)</Label>
                  <Input
                    id="color"
                    type="text"
                    value={data.color}
                    onChange={(e) => setData('color', e.target.value)}
                    placeholder="Ej: Rojo, Azul marino, Verde..."
                    className="mt-2"
                  />
                </div>

                {/* Descripción */}
                <div className="md:col-span-2">
                  <Label htmlFor="descripcion">Descripción (Opcional)</Label>
                  <Textarea
                    id="descripcion"
                    value={data.descripcion}
                    onChange={(e) => setData('descripcion', e.target.value)}
                    placeholder="Añade detalles adicionales sobre el producto..."
                    rows={3}
                    className="mt-2"
                  />
                </div>
              </div>

              {/* Buttons */}
              <div className="flex gap-4 pt-4 border-t border-gray-200">
                <Link href="/productos" className="flex-1">
                  <Button type="button" variant="secondary" className="w-full">
                    Cancelar
                  </Button>
                </Link>
                <Button type="submit" className="flex-1" disabled={processing}>
                  Actualizar Producto
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
