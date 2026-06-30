document.addEventListener('DOMContentLoaded', () => {
    const tree = document.getElementById('categoryTree');

    if (!tree) return;

    const confirmBtn = document.getElementById('btnConfirmCategory');
    const selectedIdInput = document.getElementById('selectedCategoryId');
    const selectedLabel = document.getElementById('selectedCategoryLabel');

    tree.addEventListener('click', function (e) {
        const toggleIcon = e.target.closest('.toggle-icon');
        const item = e.target.closest('.category-tree-item');

        if (!item) return;

        if (toggleIcon) {
            const children = item.nextElementSibling;
            if (children && children.classList.contains('category-tree-children')) {
                children.classList.toggle('open');
                toggleIcon.querySelector('i').classList.toggle('bi-chevron-right');
                toggleIcon.querySelector('i').classList.toggle('bi-chevron-down');
            }
            return;
        }

        tree.querySelectorAll('.category-tree-item').forEach(el => el.classList.remove('selected'));
        item.classList.add('selected');

        selectedIdInput.value = item.dataset.categoryId;
        selectedLabel.textContent = item.dataset.categoryName;
        confirmBtn.disabled = false;
    });

    confirmBtn.addEventListener('click', function () {
        const categoryId = selectedIdInput.value;
        const baseUrl = confirmBtn.dataset.createUrl;
        const url = categoryId ? `${baseUrl}?media_category_id=${categoryId}` : baseUrl;
        window.location.href = url;
    });
});
