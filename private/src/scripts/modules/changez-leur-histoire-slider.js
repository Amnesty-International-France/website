import Swiper from 'swiper';
import 'swiper/swiper-bundle.css';

import { Navigation } from 'swiper/modules';

const swiperInstances = new WeakMap();

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

const bindTunnelAccordion = (container, swiper) => {
  if (!container.classList.contains('page-tunnel-clh-carousel')) {
    return;
  }

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
          requestAnimationFrame(() => refreshSwiperHeight());
        };

        content.addEventListener('transitionend', handleOpenTransitionEnd);
        requestAnimationFrame(() => refreshSwiperHeight());
        return;
      }

      const handleTransitionEnd = (event) => {
        if (event.target !== content || event.propertyName !== 'max-height') {
          return;
        }

        content.hidden = true;
        content.removeEventListener('transitionend', handleTransitionEnd);
        refreshSwiperHeight();
      };

      content.addEventListener('transitionend', handleTransitionEnd);
    });
  });
};

const changezLeurHistoireSlider = () => {
  document.querySelectorAll('.changez-leur-histoire-slider-block').forEach((container) => {
    if (swiperInstances.has(container)) {
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
    bindTunnelAccordion(container, swiper);
  });
};

export default changezLeurHistoireSlider;
