// First, last and the current page's neighbours, with gaps collapsed into an ellipsis.
export function pageItems(page, last) {
    if (last <= 7) return Array.from({ length: last }, (_, index) => index + 1);
    const pages = [...new Set([1, page - 1, page, page + 1, last])].filter((value) => value >= 1 && value <= last).sort((a, b) => a - b);
    // A gap hiding exactly one page shows that page instead of an ellipsis.
    return pages.flatMap((value, index) => {
        const gap = index > 0 ? value - pages[index - 1] : 1;
        return gap === 2 ? [value - 1, value] : gap > 2 ? [`gap-${value}`, value] : [value];
    });
}
