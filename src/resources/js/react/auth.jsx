import { useState } from 'react';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { Input } from './components/ui/Input.jsx';

function Field({ id, label, name = id, type = 'text', value, error, autoComplete, required = true }) {
    const [visible, setVisible] = useState(false);
    const inputType = type === 'password' && visible ? 'text' : type;

    return <div>
        <label htmlFor={id} className="mb-1 block text-sm font-semibold text-ink">{label}</label>
        <div className="flex gap-2">
            <Input id={id} name={name} type={inputType} defaultValue={value} error={error} required={required} autoComplete={autoComplete} aria-describedby={error ? `${id}-error` : undefined} />
            {type === 'password' && <Button type="button" variant="secondary" aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'} aria-pressed={visible} onClick={() => setVisible(!visible)}>{visible ? 'Ocultar' : 'Mostrar'}</Button>}
        </div>
        {error && <p id={`${id}-error`} className="mt-1 text-sm text-red-700">{error}</p>}
    </div>;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

export function Login({ props }) {
    const authError = props.authError;
    const errors = props.errors || {};

    return <main className="login-page flex min-h-dvh items-center justify-center px-3 py-8" style={{ paddingInline: 'max(.75rem, env(safe-area-inset-left))' }}>
        <div className="w-full max-w-md">
            <div className="login-brand" aria-label="TiendaStock"><img src="/images/tiendastock-mark.svg" alt="" className="h-10 w-10" /><span>TiendaStock</span></div>
            <Card className="p-6 sm:p-9">
                <header className="login-heading"><h1>Bienvenido</h1><p>Ingresa a TiendaStock para gestionar tu negocio.</p></header>
                {authError && <p className="mb-4 rounded-control border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">{authError}</p>}
                {props.status && <p className="mb-4" role="status">{props.status}</p>}
                <form method="post" action={props.routes.login} className="space-y-5">
                    <input type="hidden" name="_token" value={csrfToken()} />
                    <Field id="username" label="Usuario" value={props.old?.username} error={!authError && errors.username?.[0]} autoComplete="username" />
                    <Field id="password" label="Contraseña" type="password" error={!authError && errors.password?.[0]} autoComplete="current-password" />
                    <label className="flex min-h-11 items-center gap-2 text-sm text-muted"><input type="checkbox" name="remember" value="1" className="h-4 w-4 accent-primary" />Recordarme</label>
                    <Button className="w-full" type="submit">Iniciar sesión</Button>
                </form>
            </Card>
            <p className="login-footer mt-6 text-center text-sm text-muted">© {new Date().getFullYear()} TiendaStock</p>
        </div>
    </main>;
}

export function ConfirmPassword({ props }) {
    const error = props.errors?.password?.[0];

    return <main className="space-y-5">
        <h1 className="text-xl font-bold text-ink">Confirma tu contraseña</h1>
        <p className="text-sm text-muted">Esta es una sección segura. Confirma tu contraseña para continuar.</p>
        <form method="post" action={props.routes.passwordConfirm} className="space-y-5">
            <input type="hidden" name="_token" value={csrfToken()} />
            <Field id="confirm-password" name="password" label="Contraseña" type="password" autoComplete="current-password" error={error} />
            <Button type="submit" className="w-full sm:w-auto">Confirmar</Button>
        </form>
    </main>;
}

export function Profile({ props }) {
    const profileErrors = props.errors || {};
    const passwordErrors = props.passwordErrors || {};
    const token = csrfToken();
    const profileFields = [
        ['name', 'Nombre', 'name'],
        ['username', 'Nombre de usuario', 'username'],
        ['email', 'Correo electrónico', 'email'],
    ];
    const passwordFields = [
        ['current_password', 'Contraseña actual', 'current-password'],
        ['password', 'Nueva contraseña', 'new-password'],
        ['password_confirmation', 'Confirmar nueva contraseña', 'new-password'],
    ];

    return <div className="py-4 sm:py-8">
        <header className="mb-8 border-b border-border pb-4">
            <nav aria-label="Breadcrumb" className="mb-2 text-xs text-muted"><ol className="flex gap-2"><li>Cuenta</li><li aria-hidden="true">&gt;</li><li aria-current="page" className="font-semibold text-primary">Configuración personal</li></ol></nav>
            <h1 className="page-title">Mi perfil</h1>
            <p className="mt-1 max-w-2xl text-sm text-muted">Administra tu cuenta y actualiza tus datos personales y contraseña.</p>
        </header>
        <div className="w-full space-y-6">
            <Card className="p-4 sm:p-6 lg:p-8">
                <section>
                    <h2 className="text-xl font-bold text-ink">Información del perfil</h2>
                    <p className="mt-1 text-sm text-muted">Actualiza tus datos personales y correo electrónico.</p>
                    <form method="post" action={props.routes.profile} className="mt-6 space-y-5">
                        <input type="hidden" name="_token" value={token} />
                        <input type="hidden" name="_method" value="patch" />
                        {profileFields.map(([key, label, autoComplete]) => <Field key={key} id={key} label={label} name={key} type={key === 'email' ? 'email' : 'text'} value={profileErrors[key] ? props.old?.[key] : props.userProfile?.[key]} error={profileErrors[key]?.[0]} autoComplete={autoComplete} />)}
                        <div className="flex flex-wrap items-center gap-4"><Button type="submit">Guardar</Button>{props.status === 'profile-updated' && <span role="status" className="text-sm text-muted">Guardado.</span>}</div>
                    </form>
                </section>
            </Card>
            <Card className="p-4 sm:p-6 lg:p-8">
                <section>
                    <h2 className="text-xl font-bold text-ink">Cambiar contraseña</h2>
                    <p className="mt-1 text-sm text-muted">Usa una contraseña larga y difícil de adivinar para proteger tu cuenta.</p>
                    <form method="post" action={props.routes.passwordUpdate} className="mt-6 space-y-5">
                        <input type="hidden" name="_token" value={token} />
                        <input type="hidden" name="_method" value="put" />
                        {passwordFields.map(([key, label, autoComplete]) => <Field key={key} id={`update_password_${key}`} label={label} name={key} type="password" autoComplete={autoComplete} error={passwordErrors[key]?.[0]} />)}
                        <Button type="submit">Actualizar contraseña</Button>
                    </form>
                </section>
            </Card>
        </div>
    </div>;
}
