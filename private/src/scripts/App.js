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
import trainingFilters from './modules/search-filters-trainings';
import filterPosts from './modules/filter-posts';
import loadVideos from './modules/video-loaded';
import fluidIframe from './modules/fluid-iframe';
import tweetAction from './modules/tweet-action';
import categorySlider from './modules/category-slider';
import copyShare from './modules/copy-share';
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
import initAZFilter from './modules/az-filter';
import capitalizeHeadings from './modules/capitalize-headings';
import { getUserLocationFromButton, getUserLocationFromForm } from './modules/localisation';
import initLegsForm from './modules/legs-form';
import enhanceJetpackFormPlaceholders from './modules/jetpack-form-fix';
import { calculator, hoverDonationMenu } from './modules/donation-calculator';
import petitionShareFeedback from './modules/social-network-clicked';
import petitionDonateFeedback from './modules/donate-clicked';
import { toggleFullFormPetition, submitCodeOrigine } from './modules/petition-form';
import { closeUrgentBanner } from './modules/urgent-banner';
import initFoundationForm from './modules/Form/foundation-form';
import edhFilters from './modules/search-filters-edh';
import mySpaceMenu from './modules/my-space-menu';
import mySpaceMobileMenu from './modules/my-space-mobile-menu';
import { pageMenu, stickyMenu } from './modules/page-menu';
import sliderBlock from './modules/slider';
import BackToTop from './modules/back-to-top';
import { urgentRegister } from './modules/Form/urgent-register-form';

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
  trainingFilters();
  filterPosts();
  fluidIframe();
  tweetAction();
  categorySlider();
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
  initAZFilter();
  capitalizeHeadings();
  getUserLocationFromButton();
  getUserLocationFromForm();
  initLegsForm();
  enhanceJetpackFormPlaceholders();
  calculator();
  petitionShareFeedback();
  petitionDonateFeedback();
  toggleFullFormPetition();
  submitCodeOrigine();
  closeUrgentBanner();
  initFoundationForm();
  edhFilters();
  hoverDonationMenu();
  mySpaceMenu();
  mySpaceMobileMenu();
  pageMenu();
  stickyMenu();
  sliderBlock();
  BackToTop();
  urgentRegister();

  fluidText(document.getElementsByClassName('article-shareTitle'), 0.9);

  return {
    using: Expose(),
  };
};

/**
 * Export to `window.App.default()`.
 */
export default App;
