import { test } from 'node:test';
import assert from 'node:assert/strict';
import { localizeActivity } from './dashboardActivityCopy.js';

test('localizes sale activity while preserving the sale number', () => {
    assert.deepEqual(localizeActivity({
        type: 'sale.created',
        title: 'Sale created',
        description: 'Sale #14 was created.',
        subject: { id: 14 },
    }), {
        title: 'Venta registrada',
        description: 'Venta #14 registrada.',
    });
});

test('localizes product, category, and user activity with the subject name', () => {
    const cases = [
        ['product.created', 'Product created', 'Product Buzo was created.', { nombre: 'Buzo' }, 'Producto creado', 'Se creó el producto «Buzo».'],
        ['category.updated', 'Category updated', 'Category Ropa was updated.', { nombre: 'Ropa' }, 'Categoría actualizada', 'Se actualizó la categoría «Ropa».'],
        ['user.created', 'User created', 'User Ana was created.', { name: 'Ana' }, 'Usuario creado', 'Se creó el usuario «Ana».'],
    ];

    for (const [type, title, description, subject, expectedTitle, expectedDescription] of cases) {
        assert.deepEqual(localizeActivity({ type, title, description, subject }), {
            title: expectedTitle,
            description: expectedDescription,
        });
    }
});

test('keeps server copy for unknown activity types', () => {
    assert.deepEqual(localizeActivity({ type: 'unknown.event', title: 'Unknown title', description: 'Original text' }), {
        title: 'Unknown title',
        description: 'Original text',
    });
});
