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

      navigation: {
        nextEl: container.querySelector('.carousel-nav.next'),
        prevEl: container.querySelector('.carousel-nav.prev'),
      },
    });

    container.setAttribute('data-swiper-instance', swiper);
  });
};

export default initCarousels;
