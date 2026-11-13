import { useState } from 'react';
import { Button } from './components/ui/Button.jsx';
import { Card } from './components/ui/Card.jsx';
import { Input } from './components/ui/Input.jsx';
import { ArrowRight, BarChart3, Boxes, Eye, EyeOff, LockKeyhole, ShieldCheck, UserRound } from 'lucide-react';

function Field({ id, label, name = id, type = 'text', value, error, autoComplete, required = true }) {
    const [visible, setVisible] = useState(false);
    const inputType = type === 'password' && visible ? 'text' : type;

    return <div>
        <label htmlFor={id} className="mb-1 block text-sm font-semibold text-ink">{label}</label>
        <div className="flex gap-2">
            <Input id={id} name={name} type={inputType} defaultValue={value} error={error} required={required} autoComplete={autoComplete} aria-describedby={error ? `${id}-error` : undefined} className="min-w-0" />
            {type === 'password' && <Button type="button" variant="secondary" className="shrink-0 whitespace-nowrap" aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'} aria-pressed={visible} onClick={() => setVisible(!visible)}>{visible ? 'Ocultar' : 'Mostrar'}</Button>}
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
    const [visible, setVisible] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    return <main className="login-react">
        <aside className="login-react__visual" aria-label="Vista ilustrativa de TiendaStock">
            <div className="login-react__visual-inner">
                <div className="login-react__brand"><img src="/images/tiendastock-mark.svg" alt="" /><span>TiendaStock</span></div>
                <div className="login-react__intro"><p>TODO EN UN SOLO LUGAR</p><h2>Gestioná tu negocio de forma simple</h2><span>Organiza tu inventario y mantén tus ventas a la vista.</span></div>
                <div className="login-preview">
                    <div className="login-preview__header"><div><span>VISTA DE EJEMPLO</span><h3>Resumen del negocio</h3></div><BarChart3 size={22} aria-hidden="true" /></div>
                    <div className="login-preview__stats"><div><span>Ventas del día</span><strong>$ 124.500</strong><small>Ejemplo ilustrativo</small></div><div><span>Productos</span><strong>248</strong><small>Ejemplo ilustrativo</small></div></div>
                    <svg className="login-preview__chart" viewBox="0 0 400 64" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M0 50H400M0 25H400" fill="none" stroke="#4c7c6c" strokeWidth="1" /><path d="M0 56 L45 47 L90 50 L135 33 L180 38 L225 23 L270 29 L315 13 L360 19 L400 5" fill="none" stroke="#a3f3cb" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" vectorEffect="non-scaling-stroke" /></svg>
                    <div className="login-preview__inventory"><div><Boxes size={19} aria-hidden="true" /><strong>Inventario</strong><span>Ejemplo</span></div><p>Camisa clásica <b>32 unidades</b></p><p>Remera básica <b>18 unidades</b></p><p>Pantalón recto <b>12 unidades</b></p></div>
                </div>
                <p className="login-react__visual-note">Una vista más simple de tu negocio.</p>
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
