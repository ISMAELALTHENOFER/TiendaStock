# Rediseño del Login --- TiendaStock

## Objetivo

Rediseñar la pantalla de inicio de sesión de **TiendaStock** para
conseguir una interfaz más moderna, profesional y consistente con un
sistema de gestión comercial.

El diseño debe transmitir:

-   Simplicidad.
-   Confianza.
-   Profesionalismo.
-   Claridad visual.
-   Identidad propia de TiendaStock.
-   Buena experiencia tanto en escritorio como en dispositivos móviles.

La pantalla no debe sentirse como una plantilla genérica de Laravel.

------------------------------------------------------------------------

## 1. Enfoque visual

Utilizar un diseño **minimalista y moderno**, evitando elementos
decorativos innecesarios.

Características generales:

-   Fondo neutro y claro.
-   Card blanca para el formulario.
-   Sombras suaves.
-   Bordes discretos.
-   Espaciado generoso.
-   Tipografía moderna y legible.
-   Verde como color principal/acento.
-   Excelente contraste entre textos, fondo y controles.
-   Animaciones y transiciones sutiles.

Evitar:

-   Gradientes excesivos.
-   Sombras fuertes.
-   Demasiados colores.
-   Textos innecesarios.
-   Elementos visuales propios de Laravel.
-   Efectos llamativos que resten profesionalismo.

------------------------------------------------------------------------

## 2. Branding

Eliminar el logo de Laravel de la pantalla.

Crear un encabezado propio para **TiendaStock**.

Mientras no exista un logotipo definitivo, utilizar un isotipo simple
relacionado con:

-   Inventario.
-   Cajas.
-   Productos.
-   Stock.
-   Gestión comercial.

Ejemplo conceptual:

``` text
[ Isotipo ] TiendaStock
```

El branding utilizado en el login debe poder reutilizarse posteriormente
en:

-   Sidebar.
-   Navbar.
-   Dashboard.
-   Pantallas de carga.
-   Favicon.

------------------------------------------------------------------------

## 3. Estructura del login

### Desktop

Utilizar una card centrada de aproximadamente:

``` css
max-width: 440px;
width: 100%;
```

Estructura:

``` text
             [ LOGO ]

          Bienvenido

Ingresá a TiendaStock para gestionar
          tu negocio.

Usuario
[ 👤  tu_usuario                  ]

Contraseña
[ 🔒  •••••••••              👁 ]

☐ Recordarme       ¿Olvidaste tu contraseña?

[          Iniciar sesión  →          ]

          © 2026 TiendaStock
```

------------------------------------------------------------------------

## 4. Encabezado

Evitar tener demasiados títulos compitiendo visualmente.

### Título

``` text
Bienvenido
```

### Descripción

``` text
Ingresá a TiendaStock para gestionar tu negocio.
```

El nombre **TiendaStock** debe aparecer junto al logo/isotipo y no
necesariamente como otro título grande dentro del formulario.

------------------------------------------------------------------------

## 5. Campos del formulario

### Usuario

Label:

``` text
Usuario
```

Placeholder:

``` text
Ingresá tu usuario
```

Agregar un ícono de usuario dentro del campo.

------------------------------------------------------------------------

### Contraseña

Label:

``` text
Contraseña
```

Placeholder:

``` text
Ingresá tu contraseña
```

Agregar:

-   Ícono de candado.
-   Botón para mostrar/ocultar contraseña.
-   Soporte para autocompletado del navegador.

Ejemplo:

``` text
🔒  •••••••••                       👁
```

------------------------------------------------------------------------

## 6. Estados de los inputs

Implementar estados visuales claros.

### Normal

-   Borde gris suave.
-   Fondo blanco.

### Hover

-   Borde ligeramente más visible.

### Focus

Utilizar el verde principal.

Ejemplo:

``` css
border-color: var(--primary);
box-shadow: 0 0 0 3px rgba(...);
```

El `box-shadow` debe ser muy suave.

### Error

Mostrar:

-   Borde de error.
-   Mensaje debajo del input.
-   Ícono opcional.

Ejemplo:

``` text
Usuario
[ usuario ]

El usuario es obligatorio.
```

No utilizar solamente el color para comunicar errores.

------------------------------------------------------------------------

## 7. Botón principal

El botón **Iniciar sesión** debe ser el elemento con mayor importancia
visual.

Debe ocupar todo el ancho disponible.

``` text
[             Iniciar sesión  →             ]
```

Características:

-   `width: 100%`
-   Altura aproximada: `46px - 50px`.
-   Verde principal.
-   Texto blanco.
-   Peso de fuente `600`.
-   Border radius coherente con los inputs.
-   Transición suave en hover.

Estados necesarios:

``` text
normal
hover
focus
disabled
loading
```

------------------------------------------------------------------------

## 8. Estado de carga

Al enviar el formulario, evitar múltiples envíos.

Cambiar temporalmente:

``` text
Iniciar sesión
```

por:

``` text
Ingresando...
```

Mostrar un spinner pequeño.

Ejemplo:

``` text
[   ◌ Ingresando...   ]
```

Durante este estado el botón debe permanecer deshabilitado.

------------------------------------------------------------------------

## 9. Recordarme y recuperación de contraseña

Mantener:

``` text
☐ Recordarme
```

y:

``` text
¿Olvidaste tu contraseña?
```

El enlace debe utilizar el color principal.

Agregar estados:

-   hover
-   focus

No utilizar un verde excesivamente brillante.

------------------------------------------------------------------------

## 10. Card

La card debe tener aproximadamente:

``` css
padding: 32px;
border-radius: 14px;
border: 1px solid #e5e7eb;
background: #ffffff;
```

La sombra debe ser discreta.

Ejemplo conceptual:

``` css
box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.04),
    0 2px 8px rgba(0, 0, 0, 0.03);
```

Evitar sombras oscuras o demasiado grandes.

------------------------------------------------------------------------

## 11. Fondo

Utilizar un gris muy claro en lugar de blanco puro.

Ejemplo conceptual:

``` css
background: #f8fafc;
```

También se puede utilizar una textura o degradado extremadamente sutil,
pero no debe competir visualmente con el formulario.

------------------------------------------------------------------------

## 12. Paleta

Definir variables globales para evitar colores hardcodeados.

Ejemplo:

``` css
:root {
    --primary: #16a34a;
    --primary-hover: #15803d;

    --background: #f8fafc;
    --surface: #ffffff;

    --text-primary: #0f172a;
    --text-secondary: #64748b;

    --border: #e2e8f0;

    --danger: #dc2626;
}
```

Estos valores pueden ajustarse posteriormente cuando se defina la
identidad visual definitiva.

------------------------------------------------------------------------

## 13. Tipografía

Utilizar una tipografía moderna y limpia.

Opciones:

``` text
Inter
Manrope
DM Sans
```

Preferencia:

``` text
Inter
```

Jerarquía aproximada:

``` text
Título:       28px / 700
Descripción:  14px / 400
Labels:       13-14px / 500
Inputs:       14-15px / 400
Botón:        14-15px / 600
Footer:       12px / 400
```

------------------------------------------------------------------------

## 14. Responsive

La pantalla debe diseñarse **mobile-first**.

### Mobile

Para resoluciones pequeñas:

``` css
padding-inline: 16px;
```

La card debe utilizar prácticamente todo el ancho disponible.

``` css
width: 100%;
max-width: 440px;
```

Reducir el padding interno:

``` text
Desktop: 32px
Mobile:  24px
```

No deben existir:

-   Scroll horizontal.
-   Inputs fuera del viewport.
-   Textos cortados.
-   Botones demasiado pequeños.

------------------------------------------------------------------------

## 15. Altura de pantalla

El login debe permanecer centrado visualmente.

Preferir:

``` css
min-height: 100dvh;
```

en lugar de depender únicamente de:

``` css
height: 100vh;
```

Esto mejora el comportamiento en navegadores móviles.

------------------------------------------------------------------------

## 16. Accesibilidad

Implementar:

-   `label` asociado correctamente con cada input.
-   Navegación completa mediante teclado.
-   Estados `focus-visible`.
-   Contraste adecuado.
-   `aria-invalid` para campos inválidos.
-   `aria-describedby` para mensajes de error.
-   Botón de mostrar contraseña accesible mediante teclado.
-   `aria-label` para íconos interactivos cuando sea necesario.

No utilizar placeholders como reemplazo de los labels.

------------------------------------------------------------------------

## 17. Autocomplete

Configurar correctamente los campos:

``` html
autocomplete="username"
```

y:

``` html
autocomplete="current-password"
```

Esto permite una mejor integración con navegadores y gestores de
contraseñas.

------------------------------------------------------------------------

## 18. Seguridad

El rediseño visual no debe modificar ni debilitar la autenticación
existente.

Mantener:

-   CSRF de Laravel.
-   Validación backend.
-   Rate limiting si ya está implementado.
-   Manejo seguro de sesión.
-   Regeneración de sesión después del login.
-   Mensajes de autenticación que no expongan información sensible.

No depender exclusivamente de validaciones JavaScript.

------------------------------------------------------------------------

## 19. Feedback de autenticación

Si las credenciales son incorrectas, mostrar un mensaje visible pero
discreto.

Ejemplo:

``` text
No pudimos iniciar sesión con las credenciales ingresadas.
```

Evitar mensajes como:

``` text
El usuario existe pero la contraseña es incorrecta.
```

------------------------------------------------------------------------

## 20. Microinteracciones

Agregar únicamente transiciones sutiles:

``` css
transition: 150ms ease;
```

Aplicar a:

-   Inputs.
-   Botones.
-   Links.
-   Mostrar/ocultar contraseña.

No utilizar animaciones largas.

------------------------------------------------------------------------

## 21. Footer

Simplificar el footer.

``` text
© 2026 TiendaStock
```

Debe utilizar:

-   Tamaño pequeño.
-   Color secundario.
-   Poco protagonismo.

No es necesario mostrar:

``` text
Todos los derechos reservados.
```

------------------------------------------------------------------------

## 22. Arquitectura visual reutilizable

No crear estilos exclusivos difíciles de reutilizar.

Los siguientes componentes deben quedar preparados para utilizarse en
otras pantallas:

``` text
Button
Input
FormGroup
FormError
Checkbox
Card
Logo / Brand
```

Los mismos tokens de diseño deben utilizarse posteriormente en el
dashboard.

------------------------------------------------------------------------

## 23. Consistencia

Definir valores comunes para:

``` text
border-radius
spacing
font-size
colors
shadows
input-height
button-height
```

Evitar valores distintos para componentes visualmente equivalentes.

------------------------------------------------------------------------

## 24. Resultado esperado

La interfaz final debe sentirse como un **software profesional de
gestión de inventario**, no como una pantalla predeterminada de
framework.

Debe ser:

-   Moderna.
-   Minimalista.
-   Profesional.
-   Responsive.
-   Accesible.
-   Fácil de entender.
-   Rápida visualmente.
-   Consistente con futuras pantallas de TiendaStock.

------------------------------------------------------------------------

## 25. Criterios de aceptación

El desarrollo se considera terminado cuando:

-   [ ] Se elimina todo branding visible de Laravel.
-   [ ] TiendaStock posee branding propio en el login.
-   [ ] El formulario funciona correctamente en desktop y mobile.
-   [ ] El botón principal ocupa todo el ancho.
-   [ ] Existe mostrar/ocultar contraseña.
-   [ ] Los inputs poseen estados normal, hover, focus y error.
-   [ ] Los errores de validación se muestran correctamente.
-   [ ] Existe estado loading durante la autenticación.
-   [ ] Se evita el doble submit.
-   [ ] La navegación mediante teclado funciona.
-   [ ] Se utilizan labels reales.
-   [ ] Los campos poseen autocomplete adecuado.
-   [ ] Se mantiene la seguridad y validación backend existente.
-   [ ] Los estilos utilizan variables/tokens reutilizables.
-   [ ] No existe scroll horizontal en resoluciones móviles.
-   [ ] El diseño mantiene una estética limpia y profesional.

------------------------------------------------------------------------

## Instrucción para implementación con IA / SDD

Tomar la pantalla de login existente de **TiendaStock** y refactorizar
exclusivamente su presentación y experiencia de usuario siguiendo esta
especificación.

Antes de modificar código:

1.  Analizar la implementación actual.
2.  Identificar Blade templates, layouts, componentes y estilos
    involucrados.
3.  Reutilizar la arquitectura existente siempre que sea razonable.
4.  No modificar lógica de autenticación sin necesidad.
5.  No introducir dependencias pesadas únicamente por motivos visuales.

Durante la implementación:

1.  Priorizar componentes reutilizables.
2.  Mantener separación entre presentación y lógica.
3.  Implementar responsive mobile-first.
4.  Mantener compatibilidad con la autenticación Laravel existente.
5.  Mantener los mensajes de validación provenientes del backend.
6.  Implementar accesibilidad básica.
7.  Mantener el código simple y mantenible.

No realizar cambios funcionales fuera del alcance del login.

Al finalizar:

1.  Verificar desktop.
2.  Verificar tablet.
3.  Verificar mobile.
4.  Verificar navegación mediante teclado.
5.  Verificar errores de validación.
6.  Verificar credenciales incorrectas.
7.  Verificar login exitoso.
8.  Verificar estado loading y prevención de doble submit.
9.  Verificar mostrar/ocultar contraseña.
10. Confirmar que no se modificó el comportamiento funcional de
    autenticación.
