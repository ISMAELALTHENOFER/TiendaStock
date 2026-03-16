import AppLayout from './Layouts/AppLayout';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Package, Tags, CheckCircle, DollarSign, Plus } from 'lucide-react';
import { Link } from '@inertiajs/react';

export default function Dashboard({ productosCount = 0, categoriasCount = 0, productosActivos = 0, valorTotal = 0 }) {
  const stats = [
    {
      name: 'Total Productos',
      value: productosCount,
      icon: Package,
      color: 'from-purple-100 to-purple-50',
      iconColor: 'text-purple-600',
    },
    {
      name: 'Categorías',
      value: categoriasCount,
      icon: Tags,
      color: 'from-pink-100 to-pink-50',
      iconColor: 'text-pink-600',
    },
    {
      name: 'Productos Activos',
      value: productosActivos,
      icon: CheckCircle,
      color: 'from-green-100 to-green-50',
      iconColor: 'text-green-600',
    },
    {
      name: 'Valor Total',
      value: `$${Number(valorTotal || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 })}`,
      icon: DollarSign,
      gradient: true,
    },
  ];

  const activities = [
    {
      title: 'Nuevo producto agregado',
      description: 'Producto "Ejemplo" fue creado hace 2 horas',
      icon: Plus,
      color: 'from-purple-600 to-purple-400',
    },
    {
      title: 'Categoría actualizada',
      description: 'Categoría "Electrónicos" fue modificada',
      icon: CheckCircle,
      color: 'from-green-600 to-green-400',
    },
  ];

  return (
    <AppLayout title="Dashboard">
      <div className="space-y-6">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {stats.map((stat) => {
            const Icon = stat.icon;
            return (
              <Card 
                key={stat.name} 
                className={`${stat.gradient ? 'bg-gradient-to-br from-purple-600 to-pink-500 text-white' : ''} overflow-hidden`}
              >
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className={`text-sm font-medium ${stat.gradient ? 'text-purple-100' : 'text-gray-600'} mb-1`}>
                        {stat.name}
                      </p>
                      <p className={`text-3xl font-bold ${stat.gradient ? 'text-white' : 'text-gray-900'}`}>
                        {stat.value}
                      </p>
                    </div>
                    <div className={`p-3 rounded-lg ${stat.gradient ? 'bg-white/20 backdrop-blur-sm' : `bg-gradient-to-br ${stat.color}`}`}>
                      <Icon className={`h-8 w-8 ${stat.gradient ? 'text-white' : stat.iconColor}`} />
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Link href="/productos/create">
            <Card className="hover:shadow-xl transition-all duration-300 cursor-pointer border-2 border-transparent hover:border-purple-300">
              <CardContent className="p-6 flex items-center gap-4">
                <div className="p-4 rounded-xl bg-gradient-to-r from-purple-100 to-pink-100">
                  <Plus className="h-6 w-6 text-purple-600" />
                </div>
                <div>
                  <h3 className="font-semibold text-gray-900">Nuevo Producto</h3>
                  <p className="text-sm text-gray-500">Agregar un nuevo producto al inventario</p>
                </div>
              </CardContent>
            </Card>
          </Link>
          <Link href="/categorias/create">
            <Card className="hover:shadow-xl transition-all duration-300 cursor-pointer border-2 border-transparent hover:border-pink-300">
              <CardContent className="p-6 flex items-center gap-4">
                <div className="p-4 rounded-xl bg-gradient-to-r from-pink-100 to-purple-100">
                  <Tags className="h-6 w-6 text-pink-600" />
                </div>
                <div>
                  <h3 className="font-semibold text-gray-900">Nueva Categoría</h3>
                  <p className="text-sm text-gray-500">Crear una nueva categoría de productos</p>
                </div>
              </CardContent>
            </Card>
          </Link>
        </div>

        {/* Recent Activity */}
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-2 mb-6">
              <div className="w-1 h-6 bg-gradient-to-b from-purple-600 to-pink-500 rounded"></div>
              <h3 className="text-xl font-bold text-gray-900">Actividad Reciente</h3>
            </div>
            <div className="space-y-4">
              {activities.map((activity, index) => {
                const Icon = activity.icon;
                return (
                  <div 
                    key={index}
                    className="flex items-center gap-4 p-4 rounded-lg bg-gradient-to-r from-purple-50 to-transparent border-l-4 border-purple-500"
                  >
                    <div className={`h-10 w-10 rounded-full flex items-center justify-center bg-gradient-to-r ${activity.color}`}>
                      <Icon className="h-5 w-5 text-white" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold text-gray-900">{activity.title}</p>
                      <p className="text-sm text-gray-500 truncate">{activity.description}</p>
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
