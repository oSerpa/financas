// Formulário de lançamento: o seletor de tipo mostra só as categorias daquele tipo.
const toggle = document.querySelector('[data-type-toggle]');
const select = document.querySelector('[data-category-select]');

if (toggle && select) {
    const groups = select.querySelectorAll('optgroup[data-type]');

    const apply = () => {
        const type = toggle.querySelector('input:checked')?.value;

        groups.forEach((group) => {
            const visible = group.dataset.type === type;
            group.hidden = !visible;
            group.disabled = !visible;
        });

        // Se a categoria escolhida é do outro tipo, limpa a seleção.
        if (select.selectedOptions[0]?.parentElement.disabled) {
            select.value = '';
        }
    };

    toggle.addEventListener('change', apply);
    apply();
}
