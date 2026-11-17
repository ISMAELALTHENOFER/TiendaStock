const currency = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });

export const formatMoney = (value) => currency.format(Number(value || 0));
