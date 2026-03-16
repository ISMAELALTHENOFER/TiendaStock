import { useState } from 'react';
import { useForm, Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertTriangle, Save } from 'lucide-react';

export default function ProfileEdit({ user, mustVerifyEmail, status }) {
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  
  const { data, setData, patch, processing, errors } = useForm({
    name: user.name,
    email: user.email,
  });

  const { data: passwordData, setData: setPasswordData, put: updatePassword, processing: processingPassword, errors: passwordErrors } = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const { data: deleteData, setData: setDeleteData, delete: deleteAccount, processing: processingDelete, errors: deleteErrors } = useForm({
    password: '',
  });

  const handleProfileSubmit = (e) => {
    e.preventDefault();
    patch('/profile');
  };

  const handlePasswordSubmit = (e) => {
    e.preventDefault();
    updatePassword('/password');
  };

  const handleDeleteSubmit = (e) => {
    e.preventDefault();
    deleteAccount('/profile', {
      onSuccess: () => {
        window.location.href = '/';
      }
    });
  };

  return (
    <AppLayout title="Perfil">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Profile Information */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Save className="h-5 w-5" />
              Información del Perfil
            </CardTitle>
            <CardDescription>
              Actualiza la información de tu cuenta y correo electrónico
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleProfileSubmit} className="space-y-4">
              {/* Name */}
              <div>
                <Label htmlFor="name">Nombre</Label>
                <Input
                  id="name"
                  type="text"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  className="mt-2"
                  required
                />
                {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
              </div>

              {/* Email */}
              <div>
                <Label htmlFor="email">Correo electrónico</Label>
                <Input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className="mt-2"
                  required
                />
                {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                
                {mustVerifyEmail && user.email_verified_at === null && (
                  <div className="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p className="text-sm text-yellow-800">
                      Tu correo electrónico no está verificado.
                      <button form="send-verification" className="underline ml-1 hover:text-yellow-900">
                        Reenviar verificación
                      </button>
                    </p>
                    {status === 'verification-link-sent' && (
                      <p className="text-sm text-green-600 mt-1">
                        Se ha enviado un nuevo enlace de verificación
                      </p>
                    )}
                  </div>
                )}
              </div>

              <div className="flex items-center gap-4">
                <Button type="submit" disabled={processing}>
                  Guardar
                </Button>
                {status === 'profile-updated' && (
                  <p className="text-sm text-green-600">Guardado exitosamente</p>
                )}
              </div>
            </form>
          </CardContent>
        </Card>

        {/* Update Password */}
        <Card>
          <CardHeader>
            <CardTitle>Actualizar Contraseña</CardTitle>
            <CardDescription>
              Asegúrate de usar una contraseña larga y aleatoria
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handlePasswordSubmit} className="space-y-4">
              {/* Current Password */}
              <div>
                <Label htmlFor="current_password">Contraseña actual</Label>
                <Input
                  id="current_password"
                  type="password"
                  value={passwordData.current_password}
                  onChange={(e) => setPasswordData('current_password', e.target.value)}
                  className="mt-2"
                  autoComplete="current-password"
                />
                {passwordErrors.current_password && (
                  <p className="text-red-500 text-sm mt-1">{passwordErrors.current_password}</p>
                )}
              </div>

              {/* New Password */}
              <div>
                <Label htmlFor="password">Nueva contraseña</Label>
                <Input
                  id="password"
                  type="password"
                  value={passwordData.password}
                  onChange={(e) => setPasswordData('password', e.target.value)}
                  className="mt-2"
                  autoComplete="new-password"
                />
                {passwordErrors.password && (
                  <p className="text-red-500 text-sm mt-1">{passwordErrors.password}</p>
                )}
              </div>

              {/* Confirm Password */}
              <div>
                <Label htmlFor="password_confirmation">Confirmar contraseña</Label>
                <Input
                  id="password_confirmation"
                  type="password"
                  value={passwordData.password_confirmation}
                  onChange={(e) => setPasswordData('password_confirmation', e.target.value)}
                  className="mt-2"
                  autoComplete="new-password"
                />
                {passwordErrors.password_confirmation && (
                  <p className="text-red-500 text-sm mt-1">{passwordErrors.password_confirmation}</p>
                )}
              </div>

              <div className="flex items-center gap-4">
                <Button type="submit" disabled={processingPassword}>
                  Guardar
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        {/* Delete Account */}
        <Card className="border-red-200">
          <CardHeader>
            <CardTitle className="text-red-600 flex items-center gap-2">
              <AlertTriangle className="h-5 w-5" />
              Eliminar Cuenta
            </CardTitle>
            <CardDescription>
              Una vez eliminada tu cuenta, todos sus recursos y datos se eliminarán permanentemente
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button 
              variant="destructive" 
              onClick={() => setShowDeleteModal(!showDeleteModal)}
            >
              Eliminar Cuenta
            </Button>

            {showDeleteModal && (
              <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <form onSubmit={handleDeleteSubmit} className="space-y-4">
                  <div>
                    <Label htmlFor="delete_password">Ingresa tu contraseña para confirmar</Label>
                    <Input
                      id="delete_password"
                      type="password"
                      value={deleteData.password}
                      onChange={(e) => setDeleteData('password', e.target.value)}
                      className="mt-2"
                      required
                    />
                    {deleteErrors.password && (
                      <p className="text-red-500 text-sm mt-1">{deleteErrors.password}</p>
                    )}
                  </div>
                  <div className="flex gap-2">
                    <Button 
                      type="button" 
                      variant="secondary"
                      onClick={() => setShowDeleteModal(false)}
                    >
                      Cancelar
                    </Button>
                    <Button 
                      type="submit" 
                      variant="destructive"
                      disabled={processingDelete}
                    >
                      Confirmar eliminación
                    </Button>
                  </div>
                </form>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
