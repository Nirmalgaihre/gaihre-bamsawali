const toggle = document.querySelector('.menu-toggle');
toggle?.addEventListener('click', () => {
    const nav = document.getElementById('main-nav');
    const open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(open));
});

document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));

document.querySelectorAll('[data-filter-select]').forEach(input => {
    const select = document.getElementById(input.dataset.filterSelect);
    const options = [...select.options].map(option => option.cloneNode(true));
    input.addEventListener('input', () => {
        const selected = select.value;
        const query = input.value.toLocaleLowerCase();
        select.replaceChildren(...options.filter(option => !option.value || option.value === selected || option.textContent.toLocaleLowerCase().includes(query)).map(option => option.cloneNode(true)));
        select.value = selected;
    });
});

document.querySelectorAll('[data-editor]').forEach(editor => {
    const surface = editor.querySelector('.editor-surface');
    const input = editor.querySelector('textarea');
    editor.classList.add('ready');
    const sync = () => { input.value = surface.innerHTML; };
    surface.addEventListener('input', sync);
    editor.closest('form').addEventListener('submit', sync);
    editor.querySelectorAll('[data-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => {
            let value = button.dataset.value || null;
            if (button.dataset.command === 'createLink') {
                value = window.prompt('Enter a link beginning with https://');
                if (!value || !/^https?:\/\//i.test(value)) return;
            }
            surface.focus();
            document.execCommand(button.dataset.command, false, value);
            sync();
        });
    });
    surface.addEventListener('paste', event => {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        sync();
    });
});

document.querySelectorAll('[data-tree]').forEach(tree => {
    const canvas = tree.querySelector('.tree-canvas');
    const viewport = tree.querySelector('.tree-viewport');
    let zoom = 1;
    const setZoom = value => {
        zoom = Math.min(2, Math.max(.25, value));
        canvas.style.zoom = zoom;
        tree.querySelector('[data-zoom]').textContent = `${Math.round(zoom * 100)}%`;
    };
    const setBranch = (button, expanded) => {
        button.closest('li').querySelector(':scope > ul').hidden = !expanded;
        button.setAttribute('aria-expanded', String(expanded));
        button.textContent = expanded ? '−' : '+';
    };
    tree.querySelectorAll('.branch-toggle').forEach(button => button.addEventListener('click', () => setBranch(button, button.getAttribute('aria-expanded') !== 'true')));
    tree.querySelectorAll('[data-tree-action]').forEach(button => button.addEventListener('click', async () => {
        const action = button.dataset.treeAction;
        if (action === 'layout') { const active = tree.classList.toggle('book-tree'); button.setAttribute('aria-pressed', String(active)); }
        if (action === 'in') setZoom(zoom + .1);
        if (action === 'out') setZoom(zoom - .1);
        if (action === 'reset') {
            setZoom(1);
            viewport.scrollTo({ left: (viewport.scrollWidth - viewport.clientWidth) / 2, top: 0 });
            tree.querySelectorAll('.highlighted').forEach(node => node.classList.remove('highlighted'));
        }
        if (action === 'expand' || action === 'collapse') tree.querySelectorAll('.branch-toggle').forEach(branch => setBranch(branch, action === 'expand'));
        if (action === 'fullscreen') {
            try {
                if (document.fullscreenElement) await document.exitFullscreen();
                else if (tree.requestFullscreen) await tree.requestFullscreen();
            } catch { button.title = 'Fullscreen is unavailable in this browser.'; }
        }
    }));
    const dialog = tree.querySelector('dialog');
    tree.querySelectorAll('[data-member-name]').forEach(button => button.addEventListener('click', () => {
        dialog.querySelector('[data-detail-name]').textContent = button.dataset.memberName;
        dialog.querySelector('[data-detail-english]').textContent = button.dataset.memberEnglish;
        dialog.querySelector('[data-detail-generation]').textContent = `${button.dataset.memberGeneration} पुस्ता`;
        dialog.querySelector('[data-detail-description]').textContent = button.dataset.memberDescription || 'थप विवरण उपलब्ध छैन।';
        dialog.querySelector('[data-detail-location]').textContent = button.dataset.memberLocation || '';
        dialog.querySelector('[data-detail-annotation]').textContent = button.dataset.memberAnnotation || '';
        dialog.showModal();
    }));
    dialog.querySelector('.dialog-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    requestAnimationFrame(() => { viewport.scrollLeft = (viewport.scrollWidth - viewport.clientWidth) / 2; });
});

document.querySelectorAll('[data-focus-member]').forEach(link => link.addEventListener('click', event => {
    const node = document.getElementById(link.dataset.focusMember);
    if (!node) return;
    event.preventDefault();
    document.querySelectorAll('.highlighted').forEach(item => item.classList.remove('highlighted'));
    let branch = node.parentElement.closest('ul');
    while (branch) {
        branch.hidden = false;
        const parent = branch.parentElement;
        const button = parent?.querySelector(':scope > .tree-node > .branch-toggle');
        if (button) { button.setAttribute('aria-expanded', 'true'); button.textContent = '−'; }
        branch = parent?.closest('ul');
    }
    node.classList.add('highlighted');
    node.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    history.replaceState(null, '', `#${node.id}`);
}));
