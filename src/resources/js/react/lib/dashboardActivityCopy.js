const titles = {
    'sale.created': 'Venta registrada',
    'sale.cancelled': 'Venta anulada',
    'product.created': 'Producto creado',
    'product.updated': 'Producto actualizado',
    'product.deactivated': 'Producto desactivado',
    'product.activated': 'Producto activado',
    'category.created': 'Categoría creada',
    'category.updated': 'Categoría actualizada',
    'category.deleted': 'Categoría eliminada',
    'user.created': 'Usuario creado',
    'user.updated': 'Usuario actualizado',
};

const subjectName = (event) => event.subject?.nombre
    || event.subject?.name
    || event.description?.match(/^(?:Product|Category|User) (.+) was (?:created|updated|deactivated|activated|deleted)\.$/)?.[1];

const eventId = (event) => event.subject?.id || event.description?.match(/#(\d+)/)?.[1];

export function localizeActivity(event) {
    const title = titles[event.type];
    if (!title) return { title: event.title, description: event.description };

    if (event.type === 'sale.created' || event.type === 'sale.cancelled') {
        const id = eventId(event);
        return {
            title,
            description: event.type === 'sale.created'
                ? id ? `Venta #${id} registrada.` : 'Venta registrada.'
                : id ? `Venta #${id} anulada; se restauró el stock.` : 'Venta anulada; se restauró el stock.',
        };
    }

    const entity = event.type.startsWith('product.') ? 'producto' : event.type.startsWith('category.') ? 'categoría' : 'usuario';
    const action = {
        created: 'Se creó',
        updated: 'Se actualizó',
        deactivated: 'Se desactivó',
        activated: 'Se activó',
        deleted: 'Se eliminó',
    }[event.type.split('.')[1]];
    const name = subjectName(event);
    const article = entity === 'categoría' ? 'la' : 'el';

    return {
        title,
        description: name ? `${action} ${article} ${entity} «${name}».` : `${action} ${article} ${entity}.`,
    };
}
