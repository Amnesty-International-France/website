wp.domReady(() => {
    // Unregister styles of humanity theme & branding plugin
    wp.blocks.unregisterBlockStyle('core/button', 'dark');
    wp.blocks.unregisterBlockStyle('core/button', 'link');
    wp.blocks.unregisterBlockStyle('core/button', 'search');
    wp.blocks.unregisterBlockStyle('core/button', 'light');

    // Hide width settings of button wordpress (we can't configure it)
    wp.data.subscribe(() => {
        const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();

        if (selectedBlock && selectedBlock.name === 'core/button') {
            const targetNode = document.querySelector('.editor-sidebar');
            if(targetNode) {
                const callback = function(mutationsList, observer) {
                    for(const mutation of mutationsList) {
                        if (mutation.type === 'childList') {
                            const btnSettingsPanel = document.querySelector('.btn-settings-panel');
                            if (btnSettingsPanel) {
                                const parentElement = btnSettingsPanel.parentElement;
                                if (parentElement) {
                                    parentElement.classList.add('btn-settings-panel-parent');
                                }
                            }
                        }
                    }
                };

                const observer = new MutationObserver(callback);
                observer.observe(targetNode, { childList: true, subtree: true });
            }
        }
    });
});