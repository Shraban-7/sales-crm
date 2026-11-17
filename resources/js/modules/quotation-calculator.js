export function initQuotationCalculator() {
    const container = document.querySelector('[data-module="quotation-calculator"]');
    if (!container) return;

    const tbody = container.querySelector('[data-items-body]');
    const addBtn = container.querySelector('[data-add-item]');
    const template = container.querySelector('template[data-item-template]');

    const recalculate = () => {
        let subtotal = 0;
        let discountTotal = 0;
        let taxTotal = 0;

        tbody.querySelectorAll('tr[data-item-row]').forEach((row, index) => {
            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `items[${index}][${input.dataset.field}]`;
            });

            const qty = parseFloat(row.querySelector('[data-field="quantity"]')?.value || '0');
            const price = parseFloat(row.querySelector('[data-field="unit_price"]')?.value || '0');
            const discPct = parseFloat(row.querySelector('[data-field="discount_percent"]')?.value || '0');
            const taxPct = parseFloat(row.querySelector('[data-field="tax_percent"]')?.value || '0');

            const gross = qty * price;
            const disc = gross * (discPct / 100);
            const taxable = gross - disc;
            const tax = taxable * (taxPct / 100);
            const lineTotal = taxable + tax;

            subtotal += gross;
            discountTotal += disc;
            taxTotal += tax;

            const lineOutput = row.querySelector('[data-line-total]');
            if (lineOutput) lineOutput.textContent = lineTotal.toFixed(2);
        });

        const grandTotal = subtotal - discountTotal + taxTotal;
        container.querySelector('[data-subtotal]').textContent = subtotal.toFixed(2);
        container.querySelector('[data-discount-total]').textContent = discountTotal.toFixed(2);
        container.querySelector('[data-tax-total]').textContent = taxTotal.toFixed(2);
        container.querySelector('[data-grand-total]').textContent = grandTotal.toFixed(2);
    };

    tbody.addEventListener('input', recalculate);
    tbody.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('[data-remove-item]');
        if (!removeBtn) return;
        if (tbody.querySelectorAll('tr[data-item-row]').length <= 1) return;
        removeBtn.closest('tr[data-item-row]').remove();
        recalculate();
    });

    addBtn?.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        tbody.appendChild(clone);
        recalculate();
    });

    recalculate();
}
