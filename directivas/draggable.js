/**
 * directivas/draggable.js
 * Directiva Vue personalizada para hacer elementos arrastrables (drag & drop)
 */

'use strict';

const vDraggable = {
    mounted(el) {
        el.style.position = 'absolute';
        el.style.cursor   = 'move';
        el.style.zIndex   = '1000';

        let startX = 0, startY = 0, origLeft = 0, origTop = 0;
        let dragging = false;

        // Buscar la barra de título del panel para usarla como handle
        const handle = el.querySelector('.card-header') || el;
        handle.style.cursor = 'move';
        handle.style.userSelect = 'none';

        const onMouseMove = (e) => {
            if (!dragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            el.style.left = (origLeft + dx) + 'px';
            el.style.top  = (origTop  + dy) + 'px';
        };

        const onMouseUp = () => {
            dragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup',   onMouseUp);
        };

        handle.addEventListener('mousedown', (e) => {
            // Solo botón izquierdo
            if (e.button !== 0) return;
            dragging = true;
            startX   = e.clientX;
            startY   = e.clientY;
            origLeft = el.offsetLeft;
            origTop  = el.offsetTop;
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup',   onMouseUp);
            e.preventDefault();
        });
    }
};
