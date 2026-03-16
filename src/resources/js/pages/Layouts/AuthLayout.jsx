import { Link } from '@inertiajs/react';
import { Package } from 'lucide-react';

export default function AuthLayout({ children, title }) {
  return (
    <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-purple-50 via-white to-pink-50">
      {/* Background decoration */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
      </div>

      <div className="relative w-full sm:max-w-md px-6">
        {/* Logo */}
        <div className="flex justify-center mb-8">
          <Link href="/" className="flex items-center gap-3">
            <div className="h-14 w-14 rounded-2xl bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
              <Package className="h-8 w-8 text-white" />
            </div>
          </Link>
        </div>

        {/* Card */}
        <div className="bg-white shadow-2xl rounded-2xl overflow-hidden border border-purple-100 relative">
          <div className="px-8 py-8">
            {title && (
              <div className="text-center mb-8">
                <h1 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                  {title}
                </h1>
              </div>
            )}
            {children}
          </div>
        </div>

        {/* Footer */}
        <div className="mt-8 text-center text-sm text-gray-500">
          <p>&copy; {new Date().getFullYear()} TiendaStock. Todos los derechos reservados.</p>
        </div>
      </div>
    </div>
  );
}
