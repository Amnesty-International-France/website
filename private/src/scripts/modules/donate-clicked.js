const petitionDonateFeedback = () => {
  const donateBlock = document.querySelector('.petition-donate');
  if (!donateBlock) return;

  const donateButton = donateBlock.querySelector('.custom-button');
  if (!donateButton) return;

  const iconStepOne = document.querySelector('.icon-step-one-container svg');
  const iconStepThree = document.querySelector('.icon-step-three-container');

  donateButton.addEventListener('click', () => {
    donateBlock.classList.add('donate-activated');

    if (iconStepOne && iconStepThree) {
      iconStepThree.innerHTML = iconStepOne.outerHTML;
    }
  });
};

export default petitionDonateFeedback;
