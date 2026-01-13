import Swiper from 'swiper';
import 'swiper/swiper-bundle.css';

import { Navigation } from 'swiper/modules';

const initCarousels = () => {
  document.querySelectorAll('.carousel-block').forEach((container) => {
    const swiper = new Swiper(container.querySelector('.swiper'), {
      modules: [Navigation],
      centeredSlides: true,
      slidesPerView: 'auto',
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: container.querySelector('.carousel-nav.next'),
        prevEl: container.querySelector('.carousel-nav.prev'),
      },
    });

    const clearActiveCaptions = () => {
      container.querySelectorAll('.swiper-slide').forEach((slide) => {
        slide.classList.remove('is-caption-active');
      });
    };

    const showActiveCaption = () => {
      const activeSlide = swiper.slides[swiper.activeIndex];
      if (activeSlide) {
        activeSlide.classList.add('is-caption-active');
      }
    };

    clearActiveCaptions();
    showActiveCaption();
    swiper.on('slideChangeTransitionStart', clearActiveCaptions);
    swiper.on('slideChangeTransitionEnd', showActiveCaption);

    container.setAttribute('data-swiper-instance', swiper);
  });
};

export default initCarousels;
