import Swiper from 'swiper';
import 'swiper/swiper-bundle.css';

import { Navigation } from 'swiper/modules';

const swiperInstances = new WeakMap();
const tunnelInstances = new WeakMap();
const DEFAULT_TUNNEL_STORAGE_KEY = 'amnesty.clh.currentPetitionSlug';

const setAccordionState = (content, expanded) => {
  if (!expanded) {
    content.hidden = false;
    content.style.maxHeight = `${content.scrollHeight}px`;

    requestAnimationFrame(() => {
      content.style.maxHeight = '0px';
    });

    return;
  }

  content.hidden = false;
  content.style.maxHeight = `${content.scrollHeight}px`;
};

const bindTunnelAccordion = (container, refreshLayout = () => {}) => {
  if (!container.classList.contains('page-tunnel-clh-carousel')) {
    return;
  }

  container.querySelectorAll('.tunnel-clh-petition-accordion-toggle').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const accordion = toggle.closest('.tunnel-clh-petition-accordion-container');
      const content = accordion?.querySelector('.tunnel-clh-petition-accordion-content');
      const petitionCard = toggle.closest('.page-tunnel-clh-petition-card');
      const pageContainer = toggle.closest('.page-container');

      if (!accordion || !content) {
        return;
      }

      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      const nextExpanded = !expanded;

      accordion.classList.toggle('is-expanded', nextExpanded);
      const hasExpandedAccordion = Boolean(
        container.querySelector('.tunnel-clh-petition-accordion-container.is-expanded'),
      );

      container.classList.toggle('is-petition-accordion-expanded', hasExpandedAccordion);
      petitionCard?.classList.toggle('is-accordion-expanded', nextExpanded);
      pageContainer?.classList.toggle('is-petition-accordion-expanded', hasExpandedAccordion);
      toggle.setAttribute('aria-expanded', String(nextExpanded));
      setAccordionState(content, nextExpanded);

      if (nextExpanded) {
        const handleOpenTransitionEnd = (event) => {
          if (event.target !== content || event.propertyName !== 'max-height') {
            return;
          }

          content.removeEventListener('transitionend', handleOpenTransitionEnd);
          content.style.maxHeight = 'none';
          requestAnimationFrame(() => refreshLayout());
        };

        content.addEventListener('transitionend', handleOpenTransitionEnd);
        requestAnimationFrame(() => refreshLayout());
        return;
      }

      const handleTransitionEnd = (event) => {
        if (event.target !== content || event.propertyName !== 'max-height') {
          return;
        }

        content.hidden = true;
        content.removeEventListener('transitionend', handleTransitionEnd);
        refreshLayout();
      };

      content.addEventListener('transitionend', handleTransitionEnd);
    });
  });
};

const getStoredPetitionSlug = (storageKey) => {
  try {
    return window.localStorage.getItem(storageKey);
  } catch (error) {
    return null;
  }
};

const storePetitionSlug = (storageKey, slug) => {
  try {
    window.localStorage.setItem(storageKey, slug);
  } catch (error) {
    // Local storage can be unavailable in private browsing contexts.
  }
};

const updatePetitionUrl = (slug) => {
  const url = new URL(window.location.href);
  url.searchParams.set('petition', slug);
  window.history.replaceState({}, '', url.toString());
};

const getRandomCard = (cards, excludedSlug = '') => {
  const candidates = cards.filter((card) => card.dataset.petitionSlug !== excludedSlug);

  if (!candidates.length) {
    return null;
  }

  return candidates[Math.floor(Math.random() * candidates.length)];
};

const closeCardAccordions = (card) => {
  card.querySelectorAll('.tunnel-clh-petition-accordion-toggle[aria-expanded="true"]').forEach((toggle) => {
    const accordion = toggle.closest('.tunnel-clh-petition-accordion-container');
    const content = accordion?.querySelector('.tunnel-clh-petition-accordion-content');

    toggle.setAttribute('aria-expanded', 'false');
    accordion?.classList.remove('is-expanded');
    card.classList.remove('is-accordion-expanded');

    if (content) {
      content.hidden = true;
      content.style.maxHeight = '0px';
    }
  });
};

const focusSignatureForm = (signatureCard) => {
  signatureCard?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  });

  const firstField = signatureCard?.querySelector(
    'input:not([type="hidden"]), textarea, select, button',
  );

  firstField?.focus({ preventScroll: true });
};

const initTunnelPetitionCards = (container) => {
  if (tunnelInstances.has(container)) {
    return;
  }

  const cards = Array.from(
    container.querySelectorAll('.page-tunnel-clh-petition-card[data-petition-slug]'),
  );
  let signableCards = [...cards];

  if (!cards.length) {
    bindTunnelAccordion(container);
    tunnelInstances.set(container, true);
    return;
  }

  const storageKey = container.dataset.storageKey || DEFAULT_TUNNEL_STORAGE_KEY;
  const url = new URL(window.location.href);
  const urlSlug = url.searchParams.get('petition');
  const storedSlug = getStoredPetitionSlug(storageKey);
  const initialSlug = urlSlug || storedSlug || container.dataset.currentPetitionSlug;
  const initialCard = cards.find((card) => card.dataset.petitionSlug === initialSlug)
    || cards.find((card) => card.classList.contains('is-active'))
    || getRandomCard(cards)
    || cards[0];

  const setActiveCard = (nextCard, updateUrl = true) => {
    if (!nextCard) {
      return;
    }

    const nextSlug = nextCard.dataset.petitionSlug;

    cards.forEach((card) => {
      const isActive = card === nextCard;
      card.hidden = !isActive;
      card.classList.toggle('is-active', isActive);

      if (!isActive) {
        closeCardAccordions(card);
      }
    });

    container.dataset.currentPetitionSlug = nextSlug;
    storePetitionSlug(storageKey, nextSlug);

    if (updateUrl) {
      updatePetitionUrl(nextSlug);
    }
  };

  const getNextCardFor = (currentCard) => getRandomCard(
    signableCards,
    currentCard?.dataset.petitionSlug,
  );

  const setNextPetitionSlug = (form, nextCard) => {
    const currentCard = form.closest('.page-tunnel-clh-petition-card');
    const selectedNextCard = nextCard || getNextCardFor(currentCard);
    const input = form.querySelector('input[name="next_petition_slug"]');

    if (!input) {
      return;
    }

    input.value = selectedNextCard?.dataset.petitionSlug || '';

    if (selectedNextCard) {
      storePetitionSlug(storageKey, selectedNextCard.dataset.petitionSlug);
    }
  };

  const submitSignature = async (form) => {
    const currentCard = form.closest('.page-tunnel-clh-petition-card');
    const nextCard = getNextCardFor(currentCard);
    const submitButton = form.querySelector('button[type="submit"]');

    setNextPetitionSlug(form, nextCard);
    submitButton?.setAttribute('disabled', 'disabled');

    const response = await fetch(container.dataset.signatureEndpoint || '/wp-json/humanity/v1/clh/sign-petition', {
      method: 'POST',
      credentials: 'same-origin',
      body: new FormData(form),
    });

    if (!response.ok) {
      throw new Error('CLH petition signature failed.');
    }

    signableCards = signableCards.filter((card) => card !== currentCard);
    currentCard?.setAttribute('data-signed', 'true');

    if (nextCard && signableCards.includes(nextCard)) {
      setActiveCard(nextCard);
      return;
    }

    if (signableCards.length) {
      setActiveCard(getRandomCard(signableCards));
      return;
    }

    window.location.reload();
  };

  container.querySelectorAll('[data-tunnel-clh-skip]').forEach((button) => {
    button.addEventListener('click', () => {
      const currentCard = button.closest('.page-tunnel-clh-petition-card');
      const nextCard = getNextCardFor(currentCard);

      if (nextCard) {
        setActiveCard(nextCard);
      }
    });
  });

  container.querySelectorAll('[data-tunnel-clh-show-signature]').forEach((button) => {
    button.addEventListener('click', () => {
      const currentCard = button.closest('.page-tunnel-clh-petition-card');
      const toggle = currentCard?.querySelector('.tunnel-clh-petition-accordion-toggle');
      const signatureCard = currentCard?.querySelector('.tunnel-clh-petition-signature-card');
      const accordionContent = currentCard?.querySelector('.tunnel-clh-petition-accordion-content');

      if (!toggle || !signatureCard || !accordionContent) {
        return;
      }

      if (toggle.getAttribute('aria-expanded') === 'true') {
        focusSignatureForm(signatureCard);
        return;
      }

      let hasRevealedSignature = false;
      const revealSignature = () => {
        if (hasRevealedSignature) {
          return;
        }

        hasRevealedSignature = true;
        accordionContent.removeEventListener('transitionend', handleTransitionEnd);
        focusSignatureForm(signatureCard);
      };
      const handleTransitionEnd = (event) => {
        if (event.target !== accordionContent || event.propertyName !== 'max-height') {
          return;
        }

        revealSignature();
      };

      accordionContent.addEventListener('transitionend', handleTransitionEnd);
      toggle.click();
      window.setTimeout(revealSignature, 400);
    });
  });

  container.querySelectorAll('.page-tunnel-clh-petition-actions').forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    form.addEventListener('submit', (event) => {
      event.preventDefault();

      submitSignature(form).catch(() => {
        form.submit();
      });
    });
  });

  bindTunnelAccordion(container);
  setActiveCard(initialCard, true);
  tunnelInstances.set(container, true);
};

const changezLeurHistoireSlider = () => {
  document.querySelectorAll('.changez-leur-histoire-slider-block').forEach((container) => {
    if (swiperInstances.has(container)) {
      return;
    }

    if (container.classList.contains('page-tunnel-clh-carousel')) {
      initTunnelPetitionCards(container);
      return;
    }

    const swiperElement = container.querySelector('.swiper');
    if (!swiperElement) {
      return;
    }

    const centeredSlides = container.dataset.centeredSlides === 'true';
    const slidesPerView = container.dataset.slidesPerView
      ? Number(container.dataset.slidesPerView)
      : 'auto';
    const useAutoHeight = container.classList.contains('page-tunnel-clh-carousel');
    const swiper = new Swiper(swiperElement, {
      modules: [Navigation],
      centeredSlides,
      slidesOffsetBefore: 0,
      slidesPerView,
      spaceBetween: 24,
      autoHeight: useAutoHeight,
      loop: false,
      initialSlide: 0,
      navigation: {
        nextEl: container.querySelector('.slider-nav.next'),
        prevEl: container.querySelector('.slider-nav.prev'),
      },
    });

    swiperInstances.set(container, swiper);

    if (container.classList.contains('page-tunnel-clh-carousel')) {
      const forceActiveSlideHeight = () => {
        const activeSlide = container.querySelector('.swiper-slide.swiper-slide-active')
          || container.querySelector('.swiper-slide');

        if (!activeSlide) {
          return;
        }

        const nextHeight = activeSlide.scrollHeight;

        if (!nextHeight) {
          return;
        }

        swiper.el.style.height = `${nextHeight}px`;
        swiper.wrapperEl.style.height = `${nextHeight}px`;
      };

      const refreshSwiperHeight = () => {
        swiper.updateAutoHeight();
        swiper.update();
        requestAnimationFrame(() => forceActiveSlideHeight());
      };

      swiper.on('slideChangeTransitionEnd', refreshSwiperHeight);
      bindTunnelAccordion(container, refreshSwiperHeight);
    }
  });
};

export default changezLeurHistoireSlider;
