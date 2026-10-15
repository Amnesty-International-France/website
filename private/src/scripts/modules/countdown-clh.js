const DAY = 86400000;
const MINUTE = 60000;

const Countdown = () => {
  document.querySelectorAll('[data-clh-countdown]').forEach((container) => {
    const seconds = parseInt(container.getAttribute('data-countdown'), 10);
    const output = container.querySelector('[data-clh-countdown-days]');
    if (!seconds || seconds <= 0 || !output) return;

    const endTime = Date.now() + seconds * 1000;
    let shown = Math.max(0, Math.ceil((seconds * 1000) / DAY));

    const tick = () => {
      const days = Math.max(0, Math.ceil((endTime - Date.now()) / DAY));

      if (days !== shown) {
        shown = days;
        output.textContent = String(days);
      }

      if (days > 0) setTimeout(tick, MINUTE);
    };

    // La valeur rendue par PHP est déjà la bonne, et le chiffre du bandeau est monté en
    // roue : on n'écrit que quand le jour change vraiment, une fois par jour au plus.
    setTimeout(tick, MINUTE);
  });
};

export default Countdown;
