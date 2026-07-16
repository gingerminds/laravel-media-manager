/**
 * Rebuilds the category tree from the flat list returned by
 * /api/media-categories (id + parent_id), and flattens it into an ordered
 * list [{ id, label }] with per-depth indentation, using the same convention
 * as modal-choose-category-options.blade.php ('— ').
 */
export function buildCategoryTreeOptions(categories, allowedIds) {
    // All keys are normalized to string to avoid false negatives if the API
    // returns id/parent_id with different JSON types (number vs string)
    // depending on the property.
    const ROOT = '__root__';

    let list = categories;
    if (Array.isArray(allowedIds) && allowedIds.length > 0) {
        const allowedSet = new Set(allowedIds.map(String));
        list = categories.filter((category) => allowedSet.has(String(category.id)));
    }
    const idSet = new Set(list.map((category) => String(category.id)));

    const byParent = new Map();
    list.forEach((category) => {
        const rawParent = category.parent_id;
        // If the actual parent isn't in the retained (filtered) list, the
        // category becomes a visual root of the displayed tree.
        const parentId = (rawParent === null || rawParent === undefined || !idSet.has(String(rawParent)))
            ? ROOT
            : String(rawParent);
        if (!byParent.has(parentId)) {
            byParent.set(parentId, []);
        }
        byParent.get(parentId).push(category);
    });
    byParent.forEach((siblings) => {
        siblings.sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
    });

    const options = [];
    const walk = (parentId, depth, seen) => {
        (byParent.get(parentId) || []).forEach((category) => {
            const key = String(category.id);
            if (seen.has(key)) {
                return; // safeguard against loops if the data is corrupted
            }
            seen.add(key);
            options.push({
                id: category.id,
                label: `${'— '.repeat(depth)}${category.name || ''}`,
            });
            walk(key, depth + 1, seen);
        });
    };
    walk(ROOT, 0, new Set());

    return options;
}
