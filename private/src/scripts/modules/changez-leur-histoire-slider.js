import Swiper from 'swiper';
import 'swiper/swiper-bundle.css';

import { Navigation } from 'swiper/modules';

const swiperInstances = new WeakMap();

const changezLeurHistoireSlider = () => {
  document.querySelectorAll('.changez-leur-histoire-slider-block').forEach((container) => {
    if (swiperInstances.has(container)) {
      return;
    }

    const swiperElement = container.querySelector('.swiper');
    if (!swiperElement) {
      return;
    }

    const swiper = new Swiper(swiperElement, {
      modules: [Navigation],
      centeredSlides: true,
      slidesPerView: 'auto',
      spaceBetween: 24,
      loop: false,
      initialSlide: 0,
      navigation: {
        nextEl: container.querySelector('.slider-nav.next'),
        prevEl: container.querySelector('.slider-nav.prev'),
      },
    });

    swiperInstances.set(container, swiper);
  });
};

export default changezLeurHistoireSlider;
