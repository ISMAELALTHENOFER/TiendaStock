@component('layouts.landing')
    <main>
        <section class="landing-hero" aria-labelledby="hero-title">
            <div class="landing-container landing-hero__grid">
                <div class="landing-hero__copy">
                    <p class="landing-eyebrow">Inventario sin fricción</p>
                    <h1 id="hero-title">Gestiona tu tienda con claridad</h1>
                    <p class="landing-lede">TiendaStock reúne inventario, stock, categorías y ventas en un solo lugar para que puedas dedicar más tiempo a tu tienda.</p>
                    <div class="landing-actions"><a class="landing-action" href="{{ route('login') }}">Iniciar sesión</a></div>
                </div>
                <div class="landing-hero__visual" aria-hidden="true">
                    <div class="landing-orbit landing-orbit--outer"></div><div class="landing-orbit landing-orbit--inner"></div>
                    <div class="landing-hero__card"><span class="landing-hero__card-label">Vista general</span><strong>Tu stock, en orden</strong><span class="landing-hero__card-status"><i></i> Todo bajo control</span></div>
                </div>
            </div>
        </section>

        <section id="funciones" class="landing-section" aria-labelledby="features-title">
            <div class="landing-container">
                <div class="landing-section__heading"><p class="landing-eyebrow">Una mirada completa</p><h2 id="features-title">Lo importante, siempre a mano</h2></div>
                <div class="landing-features">
                    <article class="landing-feature-card"><span class="landing-feature-card__number">01</span><h3>Inventario preciso</h3><p>Controla productos, talles, colores y cantidades con información actualizada.</p></article>
                    <article class="landing-feature-card"><span class="landing-feature-card__number">02</span><h3>Categorías claras</h3><p>Ordena tu catálogo para encontrar cada artículo rápido y trabajar sin vueltas.</p></article>
                    <article class="landing-feature-card"><span class="landing-feature-card__number">03</span><h3>Ventas conectadas</h3><p>Registra tus ventas y entiende cómo se mueve tu stock día a día.</p></article>
                </div>
            </div>
        </section>

        <section class="landing-section landing-section--steps" aria-labelledby="steps-title">
            <div class="landing-container landing-steps">
                <div class="landing-section__heading"><p class="landing-eyebrow">Hecho para avanzar</p><h2 id="steps-title">De la estantería a la decisión</h2></div>
                <ol class="landing-step-list">
                    <li><span>01</span><div><h3>Carga</h3><p>Organiza tus productos y sus categorías.</p></div></li>
                    <li><span>02</span><div><h3>Consulta</h3><p>Revisa el stock disponible cuando lo necesites.</p></div></li>
                    <li><span>03</span><div><h3>Vende</h3><p>Registra cada operación y mantén el control.</p></div></li>
                </ol>
            </div>
        </section>

        <section class="landing-final-cta" aria-labelledby="cta-title"><div class="landing-container landing-final-cta__inner"><div><p class="landing-eyebrow">Tu próxima jornada empieza acá</p><h2 id="cta-title">Ordená tu operación. Crecé con confianza.</h2></div><a class="landing-action landing-action--light" href="{{ route('login') }}">Entrar a TiendaStock</a></div></section>
    </main>
@endcomponent
