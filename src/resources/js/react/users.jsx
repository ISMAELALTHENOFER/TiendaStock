import { useRef, useState } from 'react';
import { Badge } from './components/ui/Badge.jsx';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { Input } from './components/ui/Input.jsx';
import { Select } from './components/ui/Select.jsx';
import { csrf } from './lib/api.js';

function roleBadge(user) {
    if (user.role === 'ADMIN') return { tone: 'success', short: 'Admin', long: 'Administrador' };
    if (user.role === 'Ventas') return { tone: 'info', short: 'Ventas', long: 'Ventas' };
    if (user.role === 'Control Stock') return { tone: 'success', short: 'Stock', long: 'Control Stock' };
    return { tone: 'neutral', short: 'Sin rol', long: 'Sin rol' };
}

export function UserList({ users, routes }) {
    const data = users?.data || [];
    const currentPage = users?.current_page || 1;
    const lastPage = users?.last_page || 1;

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 className="page-title">Usuarios del Sistema</h2>
                    <p className="mt-1 text-sm text-muted">Gestiona todos los usuarios de la plataforma</p>
                </div>
                <a href={routes.usersCreate} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-5 text-sm font-semibold text-white transition-colors duration-200 hover:bg-primary-700">Nuevo Usuario</a>
            </div>

            {data.length === 0 ? (
                <Card className="p-8 text-center sm:p-12">
                    <EmptyState title="No hay usuarios en el sistema" description="Crea tu primer usuario para comenzar" />
                    <a href={routes.usersCreate} className="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-primary px-6 text-sm font-semibold text-white transition-colors duration-200 hover:bg-primary-700">Crear Primer Usuario</a>
                </Card>
            ) : (
                <div className="space-y-6">
                    <Card className="hidden overflow-x-auto p-0 sm:p-0 md:block">
                         <table className="data-table w-full min-w-[720px] text-left text-sm">
                            <thead>
                                <tr className="border-b border-border text-xs uppercase tracking-wide text-white">
                                    <th className="px-4 py-3">Nombre</th>
                                    <th className="px-4 py-3">Usuario</th>
                                    <th className="px-4 py-3">Email</th>
                                    <th className="px-4 py-3 text-center">Rol</th>
                                    <th className="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.map((user) => {
                                    const badge = roleBadge(user);
                                    return (
                                        <tr key={user.id} className="border-b border-border last:border-0 hover:bg-surface-muted/50">
                                            <td className="px-4 py-3 font-semibold text-ink">{user.name}</td>
                                            <td className="px-4 py-3 text-muted">{user.username}</td>
                                            <td className="px-4 py-3 text-muted">{user.email}</td>
                                            <td className="px-4 py-3 text-center"><Badge tone={badge.tone}>{badge.long}</Badge></td>
                                             <td className="px-4 py-3">
                                                 <div className="data-table-actions"><a href={`${routes.users}/${user.id}/edit`} className="data-table-action text-primary transition-colors duration-200 hover:bg-surface-muted" aria-label={`Editar usuario ${user.username}`}>Editar</a></div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </Card>

                    <div className="space-y-4 md:hidden">
                        {data.map((user) => {
                            const badge = roleBadge(user);
                            return (
                                <Card key={user.id}>
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="font-bold text-ink">{user.name}</p>
                                            <p className="mt-0.5 text-sm text-muted">@ {user.username}</p>
                                        </div>
                                        <Badge tone={badge.tone}>{badge.short}</Badge>
                                    </div>
                                     <dl className="mt-4 space-y-3 text-sm"><div className="data-card-field"><dt>Usuario</dt><dd>@{user.username}</dd></div><div className="data-card-field"><dt>Email</dt><dd className="break-words">{user.email}</dd></div><div className="data-card-field"><dt>Rol</dt><dd><Badge tone={badge.tone}>{badge.long}</Badge></dd></div></dl>
                                     <a href={`${routes.users}/${user.id}/edit`} className="mt-4 block min-h-11 w-full rounded-control bg-surface-muted px-4 py-3 text-center text-sm font-semibold text-primary transition-colors duration-200 hover:bg-border" aria-label={`Editar usuario ${user.username}`}>Editar usuario</a>
                                </Card>
                            );
                        })}
                    </div>

                    <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted">Mostrando <strong className="text-ink">{users?.from ?? 0}</strong>–<strong className="text-ink">{users?.to ?? 0}</strong> de <strong className="text-ink">{users?.total ?? 0}</strong> {(users?.total ?? 0) === 1 ? 'usuario' : 'usuarios'}</p>
                        {lastPage > 1 && (
                            <div className="flex items-center gap-3">
                                {currentPage > 1 && <a href={`${routes.users}?page=${currentPage - 1}`} className="inline-flex min-h-11 items-center rounded-control border border-border px-4 text-sm font-semibold text-ink transition-colors duration-200 hover:bg-surface-muted">Anterior</a>}
                                <span className="text-sm text-muted">Página <strong className="text-ink">{currentPage}</strong> de <strong className="text-ink">{lastPage}</strong></span>
                                {currentPage < lastPage && <a href={`${routes.users}?page=${currentPage + 1}`} className="inline-flex min-h-11 items-center rounded-control border border-border px-4 text-sm font-semibold text-ink transition-colors duration-200 hover:bg-surface-muted">Siguiente</a>}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

export function UserForm({ routes, usuario = null }) {
    const isEdit = Boolean(usuario);
    const formRef = useRef(null);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    const action = isEdit ? `${routes.users}/${usuario.id}` : routes.users;

    async function submit() {
        setSubmitting(true);
        setErrors({});
        const data = new FormData(formRef.current);
        if (isEdit) data.append('_method', 'PUT');
        const headers = { Accept: 'application/json' };
        const token = csrf();
        if (token) headers['X-CSRF-TOKEN'] = token;

        try {
            const response = await fetch(action, { method: 'POST', headers, body: data });
            if (response.redirected) {
                window.location.assign(response.url);
                return;
            }
            const contentType = response.headers.get('content-type') || '';
            const body = contentType.includes('application/json') ? await response.json() : null;
            if (!response.ok) {
                setSubmitting(false);
                setErrors(body?.errors || {});
                return;
            }
            window.location.assign(body?.redirect || routes.users);
        } catch {
            setSubmitting(false);
            setErrors({ _general: ['No se pudo guardar el usuario. Inténtelo de nuevo.'] });
        }
    }

    function handleSubmit(event) {
        event.preventDefault();
        submit();
    }

    return (
        <div className="py-4 sm:py-8">
            <div className="mx-auto max-w-2xl overflow-hidden rounded-card border border-border bg-surface shadow-subtle">
                <div className="bg-gradient-to-r from-primary to-primary-700 px-6 py-8">
                    <h3 className="text-lg font-bold text-white">{isEdit ? 'Modificar Usuario' : 'Información del Usuario'}</h3>
                    <p className="mt-1 text-sm text-white/80">{isEdit ? 'Actualiza los detalles del usuario' : 'Completa los detalles para crear un nuevo usuario'}</p>
                </div>

                <form ref={formRef} action={action} method="POST" onSubmit={handleSubmit} noValidate className="space-y-6 p-6 sm:p-8">
                    {errors._general && <div className="rounded-card bg-red-50 p-3 text-sm text-red-700" role="alert">{errors._general[0]}</div>}

                    <div className="space-y-5">
                        <div>
                            <label htmlFor="name-field" className="mb-1.5 block text-sm font-medium text-ink">Nombre completo <span className="text-red-600">*</span></label>
                            <Input id="name-field" name="name" required autoComplete="name" placeholder="Ej: Juan Pérez" defaultValue={usuario?.name ?? ''} error={errors?.name?.[0]} />
                            {errors?.name && <p className="mt-1.5 text-sm text-red-600">{errors.name[0]}</p>}
                        </div>

                        <div>
                            <label htmlFor="username-field" className="mb-1.5 block text-sm font-medium text-ink">Nombre de usuario <span className="text-red-600">*</span></label>
                            <Input id="username-field" name="username" required autoComplete="username" placeholder="Ej: juanperez" defaultValue={usuario?.username ?? ''} error={errors?.username?.[0]} />
                            {errors?.username && <p className="mt-1.5 text-sm text-red-600">{errors.username[0]}</p>}
                        </div>

                        <div>
                            <label htmlFor="email-field" className="mb-1.5 block text-sm font-medium text-ink">Correo electrónico <span className="text-red-600">*</span></label>
                            <Input id="email-field" type="email" name="email" required autoComplete="email" placeholder="Ej: juan@example.com" defaultValue={usuario?.email ?? ''} error={errors?.email?.[0]} />
                            {errors?.email && <p className="mt-1.5 text-sm text-red-600">{errors.email[0]}</p>}
                        </div>

                        <div>
                            <label htmlFor="password-field" className="mb-1.5 block text-sm font-medium text-ink">
                                Contraseña{' '}
                                {isEdit
                                    ? <span className="font-normal text-muted">(dejar en blanco para mantener la actual)</span>
                                    : <span className="text-red-600">*</span>}
                            </label>
                            <Input id="password-field" type="password" name="password" autoComplete="new-password" placeholder="••••••••" error={errors?.password?.[0]} />
                            {errors?.password && <p className="mt-1.5 text-sm text-red-600">{errors.password[0]}</p>}
                        </div>

                        <div>
                            <label htmlFor="password_confirmation-field" className="mb-1.5 block text-sm font-medium text-ink">Confirmar contraseña</label>
                            <Input id="password_confirmation-field" type="password" name="password_confirmation" autoComplete="new-password" placeholder="••••••••" />
                        </div>

                        <div>
                            <label htmlFor="role-field" className="mb-1.5 block text-sm font-medium text-ink">Rol de usuario <span className="text-red-600">*</span></label>
                            <Select id="role-field" name="role" required defaultValue={usuario?.role ?? 'Control Stock'}>
                                <option value="Control Stock">Control Stock</option>
                                <option value="Ventas">Ventas</option>
                                <option value="ADMIN">Administrador</option>
                            </Select>
                            {errors?.role && <p className="mt-1.5 text-sm text-red-600">{errors.role[0]}</p>}
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                        <a href={routes.users} className="inline-flex min-h-11 items-center justify-center rounded-control border border-border bg-surface px-4 text-sm font-semibold text-ink transition-colors duration-200 hover:bg-surface-muted">Cancelar</a>
                        <Button type="submit" disabled={submitting}>{submitting ? 'Guardando...' : isEdit ? 'Actualizar Usuario' : 'Crear Usuario'}</Button>
                    </div>
                </form>
            </div>
        </div>
    );
}
