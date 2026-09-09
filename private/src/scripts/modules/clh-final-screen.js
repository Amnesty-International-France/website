const clearStoredPetition = (container) => {
  const { storageKey } = container.dataset;

  if (!storageKey) {
    return;
  }

  try {
    window.localStorage.removeItem(storageKey);
  } catch (error) {
    // Local storage can be unavailable in private browsing contexts.
  }
};

const setActiveTab = (container, nextTab) => {
  const tabs = Array.from(container.querySelectorAll('[data-clh-final-tab]'));
  const panels = Array.from(container.querySelectorAll('[role="tabpanel"]'));

  tabs.forEach((tab) => {
    const isActive = tab === nextTab;
    const panelId = tab.getAttribute('aria-controls');

    tab.classList.toggle('is-active', isActive);
    tab.setAttribute('aria-selected', String(isActive));
    tab.setAttribute('tabindex', isActive ? '0' : '-1');

    panels.forEach((panel) => {
      if (panel.id !== panelId) {
        return;
      }

      if (isActive) {
        panel.removeAttribute('hidden');
      } else {
        panel.setAttribute('hidden', '');
      }

      panel.classList.toggle('is-active', isActive);
    });
  });
};

const initTabsKeyboard = (container) => {
  const tabs = Array.from(container.querySelectorAll('[data-clh-final-tab]'));

  tabs.forEach((tab, index) => {
    tab.addEventListener('keydown', (event) => {
      if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
        return;
      }

      event.preventDefault();

      let nextIndex = index;

      if (event.key === 'ArrowLeft') {
        nextIndex = index === 0 ? tabs.length - 1 : index - 1;
      }

      if (event.key === 'ArrowRight') {
        nextIndex = index === tabs.length - 1 ? 0 : index + 1;
      }

      if (event.key === 'Home') {
        nextIndex = 0;
      }

      if (event.key === 'End') {
        nextIndex = tabs.length - 1;
      }

      setActiveTab(container, tabs[nextIndex]);
      tabs[nextIndex].focus();
    });
  });
};

const clhFinalScreen = () => {
  document.querySelectorAll('[data-clh-final-tabs]').forEach((container) => {
    clearStoredPetition(container);

    container.querySelectorAll('[data-clh-final-tab]').forEach((tab) => {
      tab.addEventListener('click', () => setActiveTab(container, tab));
    });

    container.querySelectorAll('[data-clh-final-tab-trigger]').forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const targetTab = container.querySelector(`#${trigger.dataset.clhFinalTabTrigger}`);

        if (!targetTab) {
          return;
        }

        setActiveTab(container, targetTab);
        targetTab.focus();
      });
    });

    initTabsKeyboard(container);
  });
};

export default clhFinalScreen;
