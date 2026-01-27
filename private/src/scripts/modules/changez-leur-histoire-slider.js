import Swiper from 'swiper';
import 'swiper/swiper-bundle.css';

import { Navigation } from 'swiper/modules';

const changezLeurHistoireSlider = () => {
  document.querySelectorAll('.changez-leur-histoire-slider-block').forEach((container) => {
    if (container.dataset.swiperInstance === 'true') {
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

    container._swiperInstance = swiper;
    container.dataset.swiperInstance = 'true';
  });
};

export default changezLeurHistoireSlider;
