const StickyBar = () => {
  const bar = document.querySelector('[data-clh-sticky-bar]');
  // La barre ne sert que dans l'intervalle : elle prend le relais du bandeau de la
  // bannière, et se retire dès qu'un bandeau, le plateau de la frise ou le bloc de don
  // entre à l'écran. La frise se parcourt année par année, la barre recouvrirait le
  // panneau ouvert. Sur un téléphone le bloc de don remplit l'écran à lui seul, sans que
  // le bandeau au-dessus ne soit encore visible.
  const anchors = document.querySelectorAll('.clh-band, .clh-timeline-dial, .clh-donation');
  if (!bar || !anchors.length) return;

  // L'attribut couvre le cas sans JS. La transition d'entrée demande que l'élément soit
  // rendu, c'est la classe qui le montre ensuite.
  bar.hidden = false;

  const visible = new Set();

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          visible.add(entry.target);
        } else {
          visible.delete(entry.target);
        }
      });

      const shown = visible.size === 0;

      bar.classList.toggle('is-in', shown);
      document.body.classList.toggle('has-clh-sticky-bar', shown);
    },
    { threshold: 0 },
  );

  anchors.forEach((anchor) => observer.observe(anchor));
};

export default StickyBar;
