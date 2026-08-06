{{--
    Shared Alpine.data components for the product create/edit forms.

    Included once per page (@once) to register:
      - moneyInput:     (registered via @include('partials._money-input'))
                        masked Argentine money ($1.234,56 display) + hidden
                        numeric submitter so the backend always receives a
                        pure float (defense-in-depth: backend numeric rule
                        still rejects any tampered formatted string). The
                        visible field stays EMPTY when the value is 0 until
                        the user types, avoiding the "$0,00" delete-before-
                        typing friction on the create form.
                        Live (while-typing) thousand-grouping with caret
                        preservation is applied on every keystroke; the
                        canonical "$1.234,56" 2-decimal form is restored on
                        blur. Shared by the product forms and the POS Pago
                        con / Descuento inputs — the only money mask in the
                        app, so format rules never drift between surfaces.
      - productImage:   disk file selection + live preview via
                        URL.createObjectURL (camera capture removed: gallery/
                        disk is the only supported source).
      - inlineCategory: modal that POSTs /categorias/inline and pushes the
                        new <option> into the category <select> without
                        leaving the page.
}}

{{-- moneyInput now lives in the shared partial so the product forms and
     the POS surface use the SAME live Argentine money mask. --}}
@include('partials._money-input')

<script>
document.addEventListener('alpine:init', () => {
    // Disk file selection + live preview (client-side only; upload on submit).
    Alpine.data('productImage', (existingUrl) => ({
        preview: existingUrl || '',
        fileName: '',

        showPreview(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }
            this.preview = URL.createObjectURL(file);
            this.fileName = file.name;
        },
    }));

    // Duplicate detection on the create flow: on the nombre input's @blur,
    // asks the backend whether a product with that exact name already exists
    // (active OR soft-disabled). If it does, prompts the user to edit the
    // existing record instead of creating a duplicate — on confirm, redirects
    // to the edit page; on cancel, lets them keep typing (a variant with a
    // similar name is still legitimate). Advisory only: never blocks typing
    // or submit, and a failed fetch is swallowed silently so product creation
    // still works offline. lastCheckedName dedupes successive blur events on
    // the same name to avoid re-prompting when the user tabs back and forth.
    Alpine.data('duplicateCheck', () => ({
        nombre: '',
        lastCheckedName: '',

        async verificarDuplicado() {
            const nombre = (this.nombre || '').trim();
            if (nombre.length < 2 || nombre === this.lastCheckedName) {
                return;
            }
            this.lastCheckedName = nombre;

            try {
                const res = await fetch('{{ route('productos.check-duplicate') }}?nombre=' + encodeURIComponent(nombre), {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) {
                    return;
                }
                const data = await res.json();
                if (!data.exists) {
                    return;
                }

                const confirm = await confirmDialogShow(
                    'Producto existente',
                    `Ya existe un producto llamado '${data.nombre}'. ¿Deseas editarlo en lugar de crear uno nuevo?`,
                    'Sí, editar',
                    'bg-brand-600 hover:bg-brand-700',
                );
                if (confirm) {
                    window.location.href = '/productos/' + data.id + '/edit';
                }
            } catch (e) {
                // Network/abort failure: never block product creation.
            }
        },
    }));

    // Inline category creation modal: POSTs to /categorias/inline and appends
    // the returned <option> to the categoria <select>, auto-selecting it.
    Alpine.data('inlineCategory', () => ({
        open: false,
        nombre: '',
        submitting: false,
        error: '',

        async submit() {
            const name = (this.nombre || '').trim();
            if (!name) {
                return;
            }
            this.submitting = true;
            this.error = '';
            try {
                const res = await fetch('{{ route('categorias.inline') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ nombre: name }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.error = (data.errors && data.errors.nombre && data.errors.nombre[0])
                        || 'No se pudo crear la categoría.';
                    return;
                }
                const select = document.querySelector('select[name="categoria_id"]');
                if (select) {
                    const opt = document.createElement('option');
                    opt.value = data.id;
                    opt.text = data.nombre;
                    opt.selected = true;
                    select.appendChild(opt);
                }
                this.nombre = '';
                this.open = false;
            } catch (e) {
                this.error = 'Error de red al crear la categoría.';
            } finally {
                this.submitting = false;
            }
        },
    }));
});
</script>
