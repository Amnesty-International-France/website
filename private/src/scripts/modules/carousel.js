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

    // Cache slide elements to avoid repeated DOM queries
    const slides = container.querySelectorAll('.swiper-slide');

    const clearActiveCaptions = () => {
      for (let i = 0; i < slides.length; i += 1) {
        slides[i].classList.remove('is-caption-active');
      }
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
