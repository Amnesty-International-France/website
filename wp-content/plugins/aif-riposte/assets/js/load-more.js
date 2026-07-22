(function () {
	'use strict';

	const button = document.querySelector('[data-aif-riposte-load-more]');
	const grid = document.querySelector('.aif-riposte-grid');

	if (!button || !grid || typeof aifRiposteLoadMore === 'undefined') {
		return;
	}

	let loading = false;

    function getItemsToLoad(offset) {
        if (window.innerWidth >= 1024) {
            return getPatternSlice(offset, [3, 2, 3, 2], 2);
        }
    
        if (window.innerWidth >= 768) {
            return getPatternSlice(offset, [2, 1, 2, 2, 1], 2);
        }
    
        return 2;
    }
    
    function getPatternSlice(offset, rowsPattern, numberOfRows) {
        const patternLength = rowsPattern.reduce((total, count) => total + count, 0);
        let position = offset % patternLength;
        let itemsToLoad = 0;
        let rowsLoaded = 0;
    
        while (rowsLoaded < numberOfRows) {
            for (const rowCount of rowsPattern) {
                if (position >= rowCount) {
                    position -= rowCount;
                    continue;
                }
    
                itemsToLoad += rowCount - position;
                position = 0;
                rowsLoaded++;
    
                if (rowsLoaded >= numberOfRows) {
                    break;
                }
            }
        }
    
        return itemsToLoad;
    }

	button.addEventListener('click', async function () {
		if (loading) {
			return;
		}

        const originalText = button.textContent;

		loading = true;
		button.disabled = true;
        button.setAttribute('aria-busy', 'true');
		button.classList.add('is-loading');

        button.innerHTML = '<span class="aif-riposte-loader" aria-hidden="true"></span><span>'+ aifRiposteLoadMore.i18n.loading +'</span>';
        
		const params = new URLSearchParams(window.location.search);
		const body = new FormData();

		const offset = grid.children.length;

		body.append('action', 'aif_riposte_load_more');
		body.append('nonce', aifRiposteLoadMore.nonce);
        body.append('offset', String(offset));
        body.append('per_page', String(getItemsToLoad(offset)));

		if (params.has('qlocation')) {
			body.append('qlocation', params.get('qlocation'));
		}

		if (params.has('qriposte_theme')) {
			body.append('qriposte_theme', params.get('qriposte_theme'));
		}

		try {
			const response = await fetch(aifRiposteLoadMore.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body,
			});

			const payload = await response.json();

			if (!payload.success || !payload.data.html) {
				button.remove();
				return;
			}

			grid.insertAdjacentHTML('beforeend', payload.data.html);

			if (!payload.data.hasMore) {
				button.remove();
			    return;
			}

            button.textContent = originalText;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.classList.remove('is-loading');

		} catch (error) {
            button.textContent = originalText;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.classList.remove('is-loading');
		} finally {
			loading = false;
		}
	});
})();