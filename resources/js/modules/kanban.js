import { apiRequest } from './http.js';

export function initKanbanBoard() {
    const board = document.querySelector('[data-module="kanban"]');
    if (!board) return;

    let draggedCard = null;
    let sourceColumn = null;

    board.querySelectorAll('[data-deal-card]').forEach((card) => {
        card.addEventListener('dragstart', () => {
            draggedCard = card;
            sourceColumn = card.closest('[data-stage-column]');
            card.classList.add('opacity-50');
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-50');
            draggedCard = null;
            sourceColumn = null;
        });
    });

    board.querySelectorAll('[data-stage-column]').forEach((column) => {
        column.addEventListener('dragover', (event) => {
            event.preventDefault();
            column.classList.add('ring-2', 'ring-brand-600/40');
        });

        column.addEventListener('dragleave', () => {
            column.classList.remove('ring-2', 'ring-brand-600/40');
        });

        column.addEventListener('drop', async (event) => {
            event.preventDefault();
            column.classList.remove('ring-2', 'ring-brand-600/40');

            if (!draggedCard || !sourceColumn || column === sourceColumn) return;

            const isLostStage = column.dataset.isLostStage === '1';
            let lostReason = null;
            if (isLostStage) {
                lostReason = window.prompt('Enter lost reason (required for Closed Lost):');
                if (!lostReason || !lostReason.trim()) return;
            }

            const card = draggedCard;
            const previousColumn = sourceColumn;
            const list = column.querySelector('[data-stage-cards]');
            list.prepend(card);

            try {
                await apiRequest(card.dataset.moveUrl, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        pipeline_stage_id: Number(column.dataset.stageId),
                        ...(lostReason ? { lost_reason: lostReason.trim() } : {}),
                    }),
                });
            } catch (error) {
                previousColumn.querySelector('[data-stage-cards]').prepend(card);
                window.alert(error.message || 'Unable to move deal stage.');
            }
        });
    });
}
