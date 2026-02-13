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
import tabbedNav from './modules/tabbed-nav';
import browserDetector from './modules/browser-detector';
import collapsableBlock from './modules/collapsable-block';
import addFlickityToTabs from './modules/tabbed-content-flickity';
import initCarousels from './modules/carousel';
import tableOfContents from './modules/table-of-contents';
import readMoreBlock from './modules/read-more';
import initAZFilter from './modules/az-filter';
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
import changezLeurHistoireSlider from './modules/changez-leur-histoire-slider';
import changeTheirHistoryToc from './modules/change-their-history-toc';
import BackToTop from './modules/back-to-top';
import urgentRegister from './modules/Form/urgent-register-form';
import { emptyInputNewsletterLead, handleNewsletterSubmission } from './modules/newsletter';

const App = () => {
  // Always-on essentials (header, navigation, browser detection)
  browserDetector();
  header();
  mobileMenu();
  Overlays();

  // Conditional module loading with DOM guards
  if (document.querySelector('.pop-in')) popIn();
  if (document.querySelector('.language-selector')) languageSelector();
  if (document.querySelector('.subcat-list')) subcatDrops();
  if (document.querySelector('.checkboxGroup')) checkboxGroup();
  if (document.querySelector('.filters-latest')) latestFilters();
  if (document.querySelector('.search-filters')) searchFilters();
  if (document.querySelector('.search-filters-trainings')) trainingFilters();
  if (document.querySelector('[data-filter-posts]')) filterPosts();
  if (document.querySelector('iframe[src*="youtube"], iframe[src*="vimeo"]')) fluidIframe();
  if (document.querySelector('.tweet-action')) tweetAction();
  if (document.querySelector('.category-slider')) categorySlider();
  if (document.querySelector('.article-shareCopy')) copyShare();
  if (document.querySelector('.tabbed-nav')) tabbedNav();
  if (document.querySelector('[data-load-video]')) loadVideos();
  if (document.querySelector('.collapsable-block')) collapsableBlock();
  if (document.querySelector('.tabbed-content-flickity')) addFlickityToTabs();
  if (document.querySelector('.carousel-block')) initCarousels();
  if (document.querySelector('.table-of-contents')) tableOfContents();
  if (document.querySelector('.read-more-block')) readMoreBlock();
  if (document.querySelector('.az-filter')) initAZFilter();
  if (document.querySelector('[data-get-location-button]')) getUserLocationFromButton();
  if (document.querySelector('[data-get-location-form]')) getUserLocationFromForm();
  if (document.querySelector('.legs-form')) initLegsForm();
  if (document.querySelector('.jetpack-form')) enhanceJetpackFormPlaceholders();
  if (document.querySelector('.donation-calculator')) calculator();
  if (document.querySelector('.petition-share')) petitionShareFeedback();
  if (document.querySelector('.petition-donate')) petitionDonateFeedback();
  if (document.querySelector('.petition-form')) toggleFullFormPetition();
  if (document.querySelector('[data-code-origine]')) submitCodeOrigine();
  if (document.querySelector('.urgent-banner')) closeUrgentBanner();
  if (document.querySelector('.foundation-form')) initFoundationForm();
  if (document.querySelector('.edh-filters')) edhFilters();
  if (document.querySelector('.donation-menu')) hoverDonationMenu();
  if (document.querySelector('.my-space-menu')) mySpaceMenu();
  if (document.querySelector('.my-space-mobile-menu')) mySpaceMobileMenu();
  if (document.querySelector('.page-menu')) pageMenu();
  if (document.querySelector('.sticky-menu')) stickyMenu();
  if (document.querySelector('.slider-block')) sliderBlock();
  if (document.querySelector('.changez-leur-histoire-slider')) changezLeurHistoireSlider();
  if (document.querySelector('.change-their-history-toc')) changeTheirHistoryToc();
  if (document.querySelector('.back-to-top')) BackToTop();
  if (document.querySelector('#urgent-register')) urgentRegister();
  if (document.querySelector('.newsletter-lead')) emptyInputNewsletterLead();
  if (document.querySelector('.newsletter-form')) handleNewsletterSubmission();

  const shareTitle = document.getElementsByClassName('article-shareTitle');
  if (shareTitle.length > 0) {
    fluidText(shareTitle, 0.9);
  }

  return {
    using: Expose(),
  };
};

/**
 * Export to `window.App.default()`.
 */
export default App;
