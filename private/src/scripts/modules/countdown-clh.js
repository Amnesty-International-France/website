export const Countdown = () => {
  const container = document.getElementById('countdown');
  if (!container) return;

  const seconds = parseInt(container.getAttribute('data-countdown'), 10);
  if (!seconds || seconds <= 0) return;

  const endTime = Date.now() + seconds * 1000;
  const span = container.querySelector('span');
  if (!span) return;

  const pad = (n) => String(n).padStart(2, '0');

  const tick = () => {
    const remaining = Math.max(0, endTime - Date.now());
    const total = Math.floor(remaining / 1000);

    const days = Math.floor(total / 86400);
    const hours = Math.floor((total % 86400) / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const secs = total % 60;

    span.textContent = `${days}j ${pad(hours)}h ${pad(minutes)}m ${pad(secs)}s`;

    if (remaining > 0) {
      setTimeout(tick, 1000);
    }
  };

  tick();
};

