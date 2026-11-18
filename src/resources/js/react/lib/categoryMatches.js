export function categoryMatches(categorias, text) {
    const prefix = text.trim().toLocaleLowerCase();
    return categorias.filter((categoria) => categoria.nombre.toLocaleLowerCase().startsWith(prefix));
}
