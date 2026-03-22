/* global mbpdfViewer */
(function () {
    'use strict';

    var cfg = typeof mbpdfViewer === 'object' && mbpdfViewer !== null ? mbpdfViewer : {};
    var closeLabel = cfg.closeLabel || 'Cerrar';
    var openNewTabLabel = cfg.openNewTabLabel || 'Abrir en pestaña nueva';

    function closeModal(root) {
        if (!root || !root.parentNode) {
            return;
        }
        document.body.style.overflow = '';
        if (root._mbpdfOnKey) {
            document.removeEventListener('keydown', root._mbpdfOnKey);
        }
        root.parentNode.removeChild(root);
    }

    function openModal(url, title) {
        var prev = document.getElementById('mbpdf-viewer-modal');
        if (prev) {
            closeModal(prev);
        }

        var root = document.createElement('div');
        root.id = 'mbpdf-viewer-modal';
        root.className = 'mbpdf-viewer-modal';
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-modal', 'true');
        root.setAttribute('aria-label', title || 'PDF');

        var backdrop = document.createElement('button');
        backdrop.type = 'button';
        backdrop.className = 'mbpdf-viewer-modal__backdrop';
        backdrop.setAttribute('aria-label', closeLabel);
        backdrop.addEventListener('click', function () {
            closeModal(root);
        });

        var panel = document.createElement('div');
        panel.className = 'mbpdf-viewer-modal__panel';

        var header = document.createElement('div');
        header.className = 'mbpdf-viewer-modal__header';

        var titleEl = document.createElement('span');
        titleEl.className = 'mbpdf-viewer-modal__title';
        titleEl.textContent = title || '';

        var actions = document.createElement('div');
        actions.className = 'mbpdf-viewer-modal__actions';

        var tabLink = document.createElement('a');
        tabLink.className = 'mbpdf-viewer-modal__tablink';
        tabLink.href = url;
        tabLink.target = '_blank';
        tabLink.rel = 'noopener noreferrer';
        tabLink.textContent = openNewTabLabel;

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'mbpdf-viewer-modal__close';
        closeBtn.setAttribute('aria-label', closeLabel);
        closeBtn.textContent = '\u00D7';

        closeBtn.addEventListener('click', function () {
            closeModal(root);
        });

        actions.appendChild(tabLink);
        actions.appendChild(closeBtn);
        header.appendChild(titleEl);
        header.appendChild(actions);

        var frameWrap = document.createElement('div');
        frameWrap.className = 'mbpdf-viewer-modal__frame-wrap';

        var iframe = document.createElement('iframe');
        iframe.className = 'mbpdf-viewer-modal__frame';
        iframe.setAttribute('title', title || 'PDF');
        iframe.src = url;

        frameWrap.appendChild(iframe);
        panel.appendChild(header);
        panel.appendChild(frameWrap);
        root.appendChild(backdrop);
        root.appendChild(panel);
        document.body.appendChild(root);
        document.body.style.overflow = 'hidden';

        root._mbpdfOnKey = function (ev) {
            if (ev.key === 'Escape') {
                closeModal(root);
            }
        };
        document.addEventListener('keydown', root._mbpdfOnKey);

        closeBtn.focus();
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('a.mbpdf-viewer-trigger');
        if (!trigger || !trigger.hasAttribute('data-mbpdf-src')) {
            return;
        }
        e.preventDefault();
        var raw = trigger.getAttribute('data-mbpdf-src');
        if (!raw) {
            return;
        }
        openModal(raw, trigger.getAttribute('title') || '');
    });
})();
