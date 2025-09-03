import Swiper from 'swiper';
import 'swiper/swiper-bundle.css';

import { Navigation, Pagination } from 'swiper/modules';

const sliderBlock = () => {
  document.querySelectorAll('.slider-block').forEach((container) => {
    if (container.dataset.swiperInstance) {
      return;
    }

    const swiper = new Swiper(container.querySelector('.swiper'), {
      modules: [Navigation, Pagination],
      slidesPerView: 1,
      slidesPerGroup: 1,
      spaceBetween: 24,
      loop: false,
      navigation: {
        nextEl: container.querySelector('.slider-nav.next'),
        prevEl: container.querySelector('.slider-nav.prev'),
      },
      pagination: {
        el: container.querySelector('.swiper-pagination'),
        clickable: true,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          slidesPerGroup: 2,
          spaceBetween: 24,
        },
        1024: {
          slidesPerView: 3,
          slidesPerGroup: 3,
          spaceBetween: 24,
        },
      },
    });

    container.setAttribute('data-swiper-instance', swiper);
  });
};

export default sliderBlock;
