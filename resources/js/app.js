/*
 * JavaScript da Caderneta. Arquivo único, sem framework, compilado pelo Vite
 * e incluído nos layouts com @vite. Cada bloco só roda se os elementos dele
 * existirem na página, então é seguro carregar em todas as telas.
 */

// Formulário de lançamento: o seletor de tipo mostra só as categorias daquele tipo.
// Os elementos são achados pelos atributos data-*, que a view coloca no HTML.
const toggle = document.querySelector('[data-type-toggle]');
const select = document.querySelector('[data-category-select]');

if (toggle && select) {
    const groups = select.querySelectorAll('optgroup[data-type]');

    const apply = () => {
        const type = toggle.querySelector('input:checked')?.value;

        groups.forEach((group) => {
            const visible = group.dataset.type === type;
            group.hidden = !visible;
            group.disabled = !visible; // desabilitado também não pode ser escolhido pelo teclado
        });

        // Se a categoria escolhida é do outro tipo, limpa a seleção.
        if (select.selectedOptions[0]?.parentElement.disabled) {
            select.value = '';
        }
    };

    // Roda ao trocar o tipo e uma vez ao abrir a página (para o estado inicial).
    toggle.addEventListener('change', apply);
    apply();
}

// Gráfico dos últimos meses: tooltip ao passar o mouse ou focar uma coluna.
document.querySelectorAll('[data-chart]').forEach((chart) => {
    const tooltip = chart.querySelector('[data-tooltip]');

    const show = (bar) => {
        // data-tip vem da view como "Novembro de 2026|R$ 4.200,00|R$ 2.100,00".
        const [month, income, expense] = bar.dataset.tip.split('|');
        tooltip.innerHTML = '';

        const title = document.createElement('p');
        title.className = 'mb-1 font-medium';
        title.textContent = month;
        tooltip.append(title);

        [['Entradas', income, 'bg-chart-income'], ['Saídas', expense, 'bg-chart-expense']].forEach(([label, value, color]) => {
            const row = document.createElement('p');
            row.className = 'flex items-center justify-between gap-4 text-ink-soft';
            row.innerHTML = `<span class="flex items-center gap-1.5"><span class="size-2 rounded-[2px] ${color}"></span>${label}</span><span class="num text-ink"></span>`;
            // O valor entra por textContent, nunca por innerHTML, para não interpretar HTML.
            row.lastElementChild.textContent = value;
            tooltip.append(row);
        });

        tooltip.classList.remove('hidden');
        const chartBox = chart.getBoundingClientRect();
        const barBox = bar.getBoundingClientRect();
        // Centraliza o tooltip sobre a barra sem deixar sair pelas bordas do gráfico.
        const left = Math.min(
            Math.max(0, barBox.left - chartBox.left + barBox.width / 2 - tooltip.offsetWidth / 2),
            chartBox.width - tooltip.offsetWidth,
        );
        tooltip.style.left = `${left}px`;
        tooltip.style.top = '0px';
    };

    const hide = () => tooltip.classList.add('hidden');

    chart.querySelectorAll('[data-tip]').forEach((bar) => {
        bar.addEventListener('mouseenter', () => show(bar));
        // focus e blur fazem o tooltip funcionar também navegando com Tab.
        bar.addEventListener('focus', () => show(bar));
        bar.addEventListener('mouseleave', hide);
        bar.addEventListener('blur', hide);
    });
});
