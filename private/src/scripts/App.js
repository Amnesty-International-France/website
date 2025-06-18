import '../styles/app.scss';
import './polyfills';

import Expose from './modules/Expose';
import Overlays from './modules/overlays';
import popIn from './modules/pop-in';
import fluidText from './modules/fluid-text';
import languageSelector from './modules/language-selector';
import header from './modules/header';
import mobileMenu from './modules/navigation';
import subcatDrops from './modules/subcategory-dropdown';
import checkboxGroup from './modules/checkbox-group';
import latestFilters from './modules/latest-filters';
import searchFilters from './modules/search-filters';
import filterPosts from './modules/filter-posts';
import loadVideos from './modules/video-loaded';
import fluidIframe from './modules/fluid-iframe';
import tweetAction from './modules/tweet-action';
import categorySlider from './modules/category-slider';
import copyShare from './modules/copy-share';
import sliderBlock from './modules/slider-block';
import counters from './modules/counter';
import termList from './modules/term-list';
import countdownTimer from './modules/countdownTimer';
import tabbedNav from './modules/tabbed-nav';
import browserDetector from './modules/browser-detector';
import iframeButton from './modules/iframe-button';
import collapsableBlock from './modules/collapsable-block';
import addFlickityToTabs from './modules/tabbed-content-flickity';
import downloadBlock from './modules/download-block';
import initCarousels from './modules/carousel';
import tableOfContents from './modules/table-of-contents';
import readMoreBlock from './modules/read-more';
import legsMenu from './modules/legs-menu';
import initAZFilter from './modules/az-filter';
import capitalizeHeadings from './modules/capitalize-headings';
import { getUserLocationFromButton, getUserLocationFromForm } from './utils/localisation';

const App = () => {
  browserDetector();
  popIn();
  languageSelector();
  header();
  mobileMenu();
  Overlays();
  subcatDrops();
  downloadBlock();
  checkboxGroup();
  latestFilters();
  searchFilters();
  filterPosts();
  fluidIframe();
  tweetAction();
  categorySlider();
  sliderBlock();
  copyShare();
  counters();
  termList();
  countdownTimer();
  tabbedNav();
  loadVideos();
  iframeButton();
  collapsableBlock();
  addFlickityToTabs();
  initCarousels();
  tableOfContents();
  readMoreBlock();
  legsMenu();
  initAZFilter();
  capitalizeHeadings();
  getUserLocationFromButton();
  getUserLocationFromForm();

  fluidText(document.getElementsByClassName('article-shareTitle'), 0.9);

  return {
    using: Expose(),
  };
};

/**
 * Export to `window.App.default()`.
 */
export default App;
