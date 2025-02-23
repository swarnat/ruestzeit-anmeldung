import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    static targets = ['list'];

    connect() {
        this.initDragAndDrop();
    }

    initDragAndDrop() {
        const sortableList = this.listTarget;
        let draggingElement = null;

        // Add drag and drop functionality to list items
        const listItems = sortableList.querySelectorAll('.list-group-item');
        listItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                draggingElement = item;
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                draggingElement = null;
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                if (draggingElement !== item) {
                    const rect = item.getBoundingClientRect();
                    const midY = (rect.top + rect.bottom) / 2;

                    if (e.clientY < midY) {
                        item.parentNode.insertBefore(draggingElement, item);
                    } else {
                        item.parentNode.insertBefore(draggingElement, item.nextSibling);
                    }
                }
            });
        });
    }
}
