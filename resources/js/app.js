import { initKanbanBoard } from './modules/kanban.js';
import { initQuotationCalculator } from './modules/quotation-calculator.js';

document.addEventListener('DOMContentLoaded', () => {
    initKanbanBoard();
    initQuotationCalculator();
});
