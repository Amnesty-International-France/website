const petitionShareFeedback = () => {
  const petitionShareBlock = document.querySelector('.petition-share');
  if (!petitionShareBlock) return;

  const socialShareTriggers = document.querySelectorAll(
    '.petition-share .social-networks a, .petition-share .social-networks .article-shareCopy',
  );

  if (!socialShareTriggers.length) {
    console.warn('Aucun bouton de partage trouvé');
    return;
  }

  const iconStepOne = document.querySelector('.icon-step-one-container svg');
  const iconStepTwo = document.querySelector('.icon-step-two-container');

  socialShareTriggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      petitionShareBlock.classList.add('share-activated');

      if (iconStepOne && iconStepTwo) {
        iconStepTwo.innerHTML = iconStepOne.outerHTML;
      }
    });
  });
};

export default petitionShareFeedback;
