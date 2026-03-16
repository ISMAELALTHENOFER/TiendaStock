import { useForm, Link } from '@inertiajs/react';
import AuthLayout from '../Layouts/AuthLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function Login({ status, canResetPassword, errors = {} }) {
  const { data, setData, post, processing } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    post('/login');
  };

  return (
    <AuthLayout title="Bienvenido de vuelta">
      {/* Session Status */}
      {status && (
        <div className="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded text-sm">
          {status}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-5">
        {/* Email */}
        <div>
          <Label htmlFor="email">Correo electrónico</Label>
          <Input
            id="email"
            type="email"
            value={data.email}
            onChange={(e) => setData('email', e.target.value)}
            placeholder="tu@email.com"
            className="mt-2"
            required
            autoFocus
          />
          {errors.email && (
            <p className="text-red-500 text-sm mt-1">{errors.email}</p>
          )}
        </div>

        {/* Password */}
        <div>
          <Label htmlFor="password">Contraseña</Label>
          <Input
            id="password"
            type="password"
            value={data.password}
            onChange={(e) => setData('password', e.target.value)}
            placeholder="••••••••"
            className="mt-2"
            required
            autoComplete="current-password"
          />
          {errors.password && (
            <p className="text-red-500 text-sm mt-1">{errors.password}</p>
          )}
        </div>

        {/* Remember Me */}
        <div className="flex items-center justify-between">
          <label className="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              checked={data.remember}
              onChange={(e) => setData('remember', e.target.checked)}
              className="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
            />
            <span className="text-sm text-gray-600">Recordarme</span>
          </label>

          {canResetPassword && (
            <Link href="/forgot-password" className="text-sm text-purple-600 hover:text-purple-700 font-medium">
              ¿Olvidaste tu contraseña?
            </Link>
          )}
        </div>

        {/* Submit */}
        <div className="pt-4">
          <Button type="submit" className="w-full py-3" disabled={processing}>
            Iniciar sesión
          </Button>
        </div>

        {/* Register Link */}
        <div className="text-center pt-4 border-t border-gray-100">
          <p className="text-sm text-gray-600">
            ¿No tienes cuenta?{' '}
            <Link href="/register" className="text-purple-600 hover:text-purple-700 font-semibold">
              Regístrate ahora
            </Link>
          </p>
        </div>
      </form>
    </AuthLayout>
  );
}
