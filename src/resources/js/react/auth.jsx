import { useState } from 'react';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { Input } from './components/ui/Input.jsx';
import { useConfirm } from './lib/useConfirm.jsx';
import { ArrowRight, Eye, EyeOff, LockKeyhole, ShieldCheck, UserRound } from 'lucide-react';

function Field({ id, label, name = id, type = 'text', value, error, autoComplete, required = true }) {
    const [visible, setVisible] = useState(false);
    const inputType = type === 'password' && visible ? 'text' : type;

    return <div>
        <label htmlFor={id} className="mb-1 block text-sm font-semibold text-ink">{label}</label>
        <div className="flex gap-2">
            <Input id={id} name={name} type={inputType} defaultValue={value} error={error} required={required} autoComplete={autoComplete} aria-describedby={error ? `${id}-error` : undefined} className="min-w-0" />
            {type === 'password' && <Button type="button" variant="secondary" className="shrink-0 whitespace-nowrap" aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'} aria-pressed={visible} onClick={() => setVisible(!visible)}>{visible ? 'Ocultar' : 'Mostrar'}</Button>}
        </div>
        {error && <p id={`${id}-error`} className="mt-1 text-sm text-red-700 dark:text-red-200">{error}</p>}
    </div>;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

export function Login({ props }) {
    const authError = props.authError;
    const errors = props.errors || {};
    const [visible, setVisible] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    return <main className="login-react">
        <aside className="login-react__visual" aria-label="TiendaStock">
            <div className="login-react__visual-inner">
                <div className="login-react__brand"><img src="/images/tiendastock-mark.svg" alt="" /><span>TiendaStock</span></div>
                <div className="login-react__intro"><p>TODO EN UN SOLO LUGAR</p><h2>Gestiona tu negocio de forma simple</h2><span>Organiza tu inventario y mantén tus ventas a la vista.</span></div>
            </div>
        </aside>
        <section className="login-react__content" aria-labelledby="login-title">
            <div className="login-react__form">
                <div className="login-react__mobile-brand"><img src="/images/tiendastock-mark.svg" alt="" /><span>TiendaStock</span></div>
                <header className="login-react__heading"><span>TE DAMOS LA BIENVENIDA</span><h1 id="login-title">Inicia sesión</h1><p>Ingresa tus datos para continuar a TiendaStock.</p></header>
                {authError && <p id="login-auth-error" className="login-react__error" role="alert">{authError}</p>}
                {props.status && <p className="login-react__status" role="status">{props.status}</p>}
                <form method="post" action={props.routes.login} className="login-react__fields" onSubmit={() => setSubmitting(true)}>
                    <input type="hidden" name="_token" value={csrfToken()} />
                    <div><label htmlFor="username">Usuario</label><div className="login-react__input"><UserRound size={19} aria-hidden="true" /><input id="username" name="username" type="text" defaultValue={props.old?.username} autoComplete="username" placeholder="Ingresa tu usuario" required autoFocus aria-invalid={Boolean(authError || errors.username)} aria-describedby={authError ? 'login-auth-error' : errors.username?.[0] ? 'login-username-error' : undefined} /></div>{!authError && errors.username?.[0] && <p id="login-username-error" className="login-react__field-error">{errors.username[0]}</p>}</div>
                    <div><label htmlFor="password">Contraseña</label><div className="login-react__input"><LockKeyhole size={19} aria-hidden="true" /><input id="password" name="password" type={visible ? 'text' : 'password'} autoComplete="current-password" placeholder="Ingresa tu contraseña" required aria-invalid={Boolean(authError || errors.password)} aria-describedby={authError ? 'login-auth-error' : errors.password?.[0] ? 'login-password-error' : undefined} /><button type="button" className="login-react__reveal" aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'} aria-pressed={visible} onClick={() => setVisible(!visible)}>{visible ? <EyeOff size={19} aria-hidden="true" /> : <Eye size={19} aria-hidden="true" />}</button></div>{!authError && errors.password?.[0] && <p id="login-password-error" className="login-react__field-error">{errors.password[0]}</p>}</div>
                    <label className="login-react__remember"><input type="checkbox" name="remember" value="1" />Recordarme</label>
                    <button className="login-react__submit" type="submit" disabled={submitting} aria-busy={submitting}>{submitting ? 'Ingresando…' : 'Iniciar sesión'} <ArrowRight size={19} aria-hidden="true" /></button>
                </form>
                <p className="login-react__security"><ShieldCheck size={16} aria-hidden="true" /> Acceso seguro a tu cuenta</p>
            </div>
            <p className="login-react__footer">© {new Date().getFullYear()} TiendaStock</p>
        </section>
    </main>;
}

export function ConfirmPassword({ props }) {
    const error = props.errors?.password?.[0];

    // The guest host centers a shrink-to-fit root, so the card sets its own fluid width.
    return <main className="w-[min(28rem,calc(100vw-1.5rem))] space-y-5 rounded-card border border-border bg-surface p-5 shadow-subtle sm:p-8">
        <div>
            <p className="eyebrow">Cuenta</p>
            <h1 className="mt-2 text-xl font-bold text-ink">Confirma tu contraseña</h1>
            <p className="mt-1 text-sm text-muted">Esta es una sección segura. Confirma tu contraseña para continuar.</p>
        </div>
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
    const [confirm, confirmDialog] = useConfirm();
    // Native submit() skips onSubmit, so the confirmed post goes straight to the server.
    const confirmSubmit = (copy) => (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        confirm(() => form.submit(), copy);
    };
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
        <header className="mb-7 min-w-0">
            <p className="eyebrow">Cuenta</p>
            <h1 className="page-title mt-2">Mi perfil</h1>
            <p className="mt-1 text-sm text-muted">Administra tu cuenta y actualiza tus datos personales y contraseña.</p>
        </header>
        <div className="w-full space-y-6">
            <Card className="p-4 sm:p-6 lg:p-8">
                <section>
                    <h2 className="text-xl font-bold text-ink">Información del perfil</h2>
                    <p className="mt-1 text-sm text-muted">Actualiza tus datos personales y correo electrónico.</p>
                    <form method="post" action={props.routes.profile} onSubmit={confirmSubmit()} className="mt-6 space-y-5">
                        <input type="hidden" name="_token" value={token} />
                        <input type="hidden" name="_method" value="patch" />
                        <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">{profileFields.map(([key, label, autoComplete]) => <Field key={key} id={key} label={label} name={key} type={key === 'email' ? 'email' : 'text'} value={profileErrors[key] ? props.old?.[key] : props.userProfile?.[key]} error={profileErrors[key]?.[0]} autoComplete={autoComplete} />)}</div>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4"><Button type="submit" className="w-full sm:w-auto">Guardar</Button>{props.status === 'profile-updated' && <span role="status" className="text-sm text-muted">Guardado.</span>}</div>
                    </form>
                </section>
            </Card>
            <Card className="p-4 sm:p-6 lg:p-8">
                <section>
                    <h2 className="text-xl font-bold text-ink">Cambiar contraseña</h2>
                    <p className="mt-1 text-sm text-muted">Usa una contraseña larga y difícil de adivinar para proteger tu cuenta.</p>
                    <form method="post" action={props.routes.passwordUpdate} onSubmit={confirmSubmit({ title: '¿Actualizar contraseña?', message: 'Tu contraseña será reemplazada por la nueva.', confirmLabel: 'Actualizar' })} className="mt-6 space-y-5">
                        <input type="hidden" name="_token" value={token} />
                        <input type="hidden" name="_method" value="put" />
                        <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">{passwordFields.map(([key, label, autoComplete]) => <Field key={key} id={`update_password_${key}`} label={label} name={key} type="password" autoComplete={autoComplete} error={passwordErrors[key]?.[0]} />)}</div>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4"><Button type="submit" className="w-full sm:w-auto">Actualizar contraseña</Button>{props.status === 'password-updated' && <span role="status" className="text-sm text-muted">Contraseña actualizada.</span>}</div>
                    </form>
                </section>
            </Card>
        </div>
        {confirmDialog}
    </div>;
}
