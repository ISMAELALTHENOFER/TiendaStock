import { useRef, useState } from 'react';
import { Badge } from './components/ui/Badge.jsx';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { EmptyState } from './components/ui/EmptyState.jsx';
import { Input } from './components/ui/Input.jsx';
import { Select } from './components/ui/Select.jsx';
import { Pagination } from './components/ui/Pagination.jsx';
import { csrf } from './lib/api.js';
import { useLiveSearchResults } from './lib/useLiveSearchResults.js';
import { ConfirmDialog } from './components/ui/ConfirmDialog.jsx';
import { useConfirm } from './lib/useConfirm.jsx';

function roleBadge(user) {
    if (user.role === 'ADMIN') return { tone: 'success', short: 'Admin', long: 'Administrador' };
    if (user.role === 'Ventas') return { tone: 'info', short: 'Ventas', long: 'Ventas' };
    if (user.role === 'Control Stock') return { tone: 'success', short: 'Stock', long: 'Control Stock' };
    return { tone: 'neutral', short: 'Sin rol', long: 'Sin rol' };
}

export function UserList({ users, routes }) {
    const params = new URLSearchParams(window.location.search);
    const [buscar, setBuscar] = useState(params.get('buscar') || '');
    const [verInactivos, setVerInactivos] = useState(params.get('inactivos') === '1');
    const [pending, setPending] = useState(null);
    const [actionError, setActionError] = useState('');
    const liveSearch = useLiveSearchResults(users, { query: buscar, route: routes.users, pageSize: Number(users?.per_page) || 15, filters: { inactivos: verInactivos ? '1' : '' } });
    const page = liveSearch.results || users;
    const data = page?.data || [];
    const currentPage = page?.current_page || 1;
    const lastPage = page?.last_page || 1;
    const pageSize = Number(page?.per_page) || 15;
    const paginationUrl = (page) => { const next = new URLSearchParams(window.location.search); next.set('page', String(page)); next.set('per_page', String(pageSize)); return `${routes.users}?${next}`; };
    async function toggleActive() {
        const url = (pending.active ? routes.usersActivate : routes.usersDeactivate).replace('__USER__', pending.user.id);
        const token = csrf();
        try {
            const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' } });
            if (!response.ok) throw new Error();
            window.location.reload();
        } catch {
            setActionError('No se pudo actualizar el estado del usuario. Intenta de nuevo.');
        }
    }

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="min-w-0">
                    <p className="eyebrow">Administración</p>
                    <h1 className="page-title mt-2">Usuarios del sistema</h1>
                    <p className="mt-1 text-sm text-muted">Gestiona los usuarios de la plataforma.</p>
                </div>
                <Button href={routes.usersCreate} className="w-full px-5 sm:w-auto">Nuevo usuario</Button>
            </div>

            <form method="get" action={routes.users} onSubmit={(event) => event.preventDefault()} className="mb-5 flex w-full flex-col gap-3 sm:flex-row">
                <label htmlFor="users-search" className="sr-only">Buscar usuarios</label>
                <Input id="users-search" name="buscar" value={buscar} onChange={(event) => setBuscar(event.target.value)} placeholder="Buscar por nombre, usuario o email…" className="min-w-0 flex-1" />
                <input type="hidden" name="per_page" value={pageSize} />
                <Button type="submit">Buscar</Button>
            </form>
            <label className="mb-5 flex min-h-11 items-center gap-2 text-sm text-ink"><input type="checkbox" checked={verInactivos} onChange={(event) => setVerInactivos(event.target.checked)} className="h-4 w-4 accent-primary" />Ver inactivos</label>
            {actionError && <p role="alert" className="mb-3 text-sm text-red-700">{actionError}</p>}
            {liveSearch.searching && <p role="status" className="mb-3 text-sm text-muted">Buscando usuarios…</p>}
            {liveSearch.searchError && <p role="alert" className="mb-3 text-sm text-red-700">No se pudo buscar. Modifica la búsqueda para reintentar.</p>}

            {data.length === 0 ? (
                <Card className="p-8 text-center sm:p-12">
                    {buscar.trim() ? <EmptyState title="No se encontraron usuarios para tu búsqueda." />
                        : verInactivos ? <EmptyState title="No hay usuarios inactivos." />
                        : <>
                            <EmptyState title="No hay usuarios en el sistema" description="Crea tu primer usuario para empezar." />
                            <Button href={routes.usersCreate} className="mt-6 px-6">Crear primer usuario</Button>
                        </>}
                </Card>
            ) : (
                <div className="space-y-6">
                    <Card className="hidden overflow-x-auto p-0 sm:p-0 md:block">
                         <table className="data-table w-full min-w-[720px] text-left text-sm">
                            <thead>
                                <tr className="border-b border-border text-xs uppercase tracking-wide text-white">
                                    <th className="px-4 py-3">Nombre</th>
                                    <th className="px-4 py-3">Usuario</th>
                                    <th className="px-4 py-3">Correo electrónico</th>
                                    <th className="px-4 py-3 text-center">Rol</th><th className="px-4 py-3 text-center">Estado</th>
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
                                            <td className="px-4 py-3 text-center"><Badge tone={badge.tone}>{badge.long}</Badge></td><td className="px-4 py-3 text-center"><Badge tone={user.activo ? 'success' : 'neutral'}>{user.activo ? 'Activo' : 'Inactivo'}</Badge></td>
                                             <td className="px-4 py-3">
                                                  <div className="data-table-actions"><a href={`${routes.users}/${user.id}/edit`} className="data-table-action text-primary transition-colors duration-200 hover:bg-surface-muted" aria-label={`Editar usuario ${user.username}`}>Editar</a>{user.id !== routes.currentUserId && <Button type="button" variant={user.activo ? 'danger-ghost' : 'secondary'} onClick={() => setPending({ user, active: !user.activo })}>{user.activo ? 'Desactivar' : 'Activar'}</Button>}</div>
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
                                     <dl className="mt-4 space-y-3 text-sm"><div className="data-card-field"><dt>Usuario</dt><dd>@{user.username}</dd></div><div className="data-card-field"><dt>Correo electrónico</dt><dd className="break-words">{user.email}</dd></div><div className="data-card-field"><dt>Rol</dt><dd><Badge tone={badge.tone}>{badge.long}</Badge></dd></div><div className="data-card-field"><dt>Estado</dt><dd><Badge tone={user.activo ? 'success' : 'neutral'}>{user.activo ? 'Activo' : 'Inactivo'}</Badge></dd></div></dl>
                                     {user.id !== routes.currentUserId && <Button type="button" className="mt-3 w-full" variant={user.activo ? 'danger-ghost' : 'secondary'} onClick={() => setPending({ user, active: !user.activo })}>{user.activo ? 'Desactivar' : 'Activar'}</Button>}
                                     <Button href={`${routes.users}/${user.id}/edit`} variant="secondary" className="mt-3 w-full text-primary" aria-label={`Editar usuario ${user.username}`}>Editar usuario</Button>
                                </Card>
                            );
                        })}
                    </div>

                </div>
            )}
            <Pagination currentPage={currentPage} lastPage={lastPage} pageSize={pageSize} ariaLabel="Paginación de usuarios" summary={`Mostrando ${page?.from ?? 0}–${page?.to ?? 0} de ${page?.total ?? 0} usuarios`} hrefForPage={paginationUrl} onPageSizeChange={(size) => { const next = new URLSearchParams(window.location.search); next.set('per_page', String(size)); next.set('page', '1'); window.location.assign(`${routes.users}?${next}`); }} />
            <ConfirmDialog open={Boolean(pending)} title={pending?.active ? 'Activar usuario' : 'Desactivar usuario'} message={`¿Confirmas ${pending?.active ? 'la activación' : 'la desactivación'} de ${pending?.user.name}?`} confirmLabel={pending?.active ? 'Activar' : 'Desactivar'} tone={pending?.active ? 'primary' : 'danger'} onConfirm={toggleActive} onClose={() => setPending(null)} />
        </div>
    );
}

export function UserForm({ routes, usuario = null }) {
    const isEdit = Boolean(usuario);
    const formRef = useRef(null);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const [confirm, confirmDialog] = useConfirm();

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
            setErrors({ _general: ['No se pudo guardar el usuario. Inténtalo de nuevo.'] });
        }
    }

    function handleSubmit(event) {
        event.preventDefault();
        confirm(submit, isEdit ? {} : { title: '¿Crear usuario?', message: 'Se creará el usuario con los datos ingresados.', confirmLabel: 'Crear' });
    }

    return (
        <div className="py-4 sm:py-8">
            <div className="mb-7 min-w-0">
                <p className="eyebrow">Administración</p>
                <h1 className="page-title mt-2">{isEdit ? 'Modificar usuario' : 'Nuevo usuario'}</h1>
                <p className="mt-1 text-sm text-muted">{isEdit ? 'Actualiza los datos del usuario.' : 'Completa los datos para crear un usuario.'}</p>
            </div>
            <div className="min-w-0 rounded-card border border-border bg-surface shadow-subtle">
                <form ref={formRef} action={action} method="POST" onSubmit={handleSubmit} noValidate className="space-y-6 p-4 sm:p-8">
                    {errors._general && <div className="rounded-card bg-red-50 p-3 text-sm text-red-700" role="alert">{errors._general[0]}</div>}

                    <fieldset className="min-w-0">
                        <legend className="text-base font-bold text-ink">Datos</legend>
                        <p className="mt-1 text-sm text-muted">Información del usuario y su correo de contacto.</p>
                    <div className="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
                    </div>
                    </fieldset>

                    <fieldset className="min-w-0">
                        <legend className="w-full border-t border-border pt-6 text-base font-bold text-ink">Acceso</legend>
                        <p className="mt-1 text-sm text-muted">Contraseña y rol que definen qué puede hacer en el sistema.</p>
                    <div className="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label htmlFor="password-field" className="mb-1.5 block text-sm font-medium text-ink">
                                Contraseña{' '}
                                {isEdit
                                ? <span className="font-normal text-muted">(nueva contraseña; dejar en blanco para mantener la actual)</span>
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
                    </fieldset>

                    <div className="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                        <Button href={routes.users} variant="secondary">Cancelar</Button>
                        <Button type="submit" disabled={submitting}>{submitting ? 'Guardando...' : isEdit ? 'Actualizar usuario' : 'Crear usuario'}</Button>
                    </div>
                </form>
            </div>
            {confirmDialog}
        </div>
    );
}
