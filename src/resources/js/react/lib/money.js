import { productMoneyInput } from './productMoneyInput.js';

export const pesos = (value) => value.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
// Natural typing: digits are whole pesos, a comma starts decimals ("20000,5" -> "20.000,5"); invalid input keeps the previous value.
export const typeMoney = (value, previous) => productMoneyInput(value)?.display ?? previous;
export const parseMoney = (value) => Number(String(value).replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '')) || 0;
export const blurMoney = (value) => value ? pesos(parseMoney(value)) : '';
