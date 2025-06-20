import ChipCategory from './ChipCategory.jsx';

export default function EventCard({ event }) {
  const { id, title, link, meta, _embedded } = event;

  const featuredMedia = _embedded?.['wp:featuredmedia']?.[0];
  const featuredMediaUrl = featuredMedia?.source_url || null;

  const startDate = new Date(meta?._EventStartDate);
  const endDate = new Date(meta?._EventEndDate);
  const sameDay = startDate.toDateString() === endDate.toDateString();

  const formatDate = (date) =>
    date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });

  const formatTime = (date) =>
    date.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit',
    });

  const city = meta?._VenueCity || null;
  const organizerEmail = meta?._OrganizerEmail || null;
  const time = startDate && endDate ? `${formatTime(startDate)} - ${formatTime(endDate)}` : null;

  return (
    <article key={id} className="event-card card-landscape">
      {featuredMediaUrl ? (
        <a href={link} className="event-thumbnail">
          <img src={featuredMediaUrl} alt={title.rendered} />
        </a>
      ) : (
        <div className="event-thumbnail"></div>
      )}

      <ChipCategory item={event} />

      <div className="event-content">
        <p className="event-date">
          {sameDay
            ? `Le ${formatDate(startDate)}`
            : `Du ${formatDate(startDate)} au ${formatDate(endDate)}`}
        </p>

        <div className="event-title">
          <a className="as-h5" href={link}>
            {title.rendered}
          </a>
        </div>

        <div className={`event-terms ${!city && !time && !organizerEmail ? 'is-empty' : ''}`}>
          <div className="event-info">
            {city && (
              <div className="event-info-icon">
                <LocationIcon />
                <p>{city}</p>
              </div>
            )}
            {time && (
              <div className="event-info-icon">
                <ClockIcon />
                <p>{time}</p>
              </div>
            )}
            {organizerEmail && (
              <div className="event-info-icon">
                <EmailIcon />
                <p>{organizerEmail}</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </article>
  );
}

// Icônes SVG extraites du code PHP
function LocationIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path
        d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z"
        fill="#575756"
      />
    </svg>
  );
}

function ClockIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path
        fillRule="evenodd"
        clipRule="evenodd"
        d="M8.00016 1.33337C11.6822 1.33337 14.6668 4.31804 14.6668 8.00004C14.6668 11.682 11.6822 14.6667 8.00016 14.6667C4.31816 14.6667 1.3335 11.682 1.3335 8.00004C1.3335 4.31804 4.31816 1.33337 8.00016 1.33337ZM8.66683 4.66671H7.3335V9.33337H11.3335V8.00004H8.66683V4.66671Z"
        fill="#575756"
      />
    </svg>
  );
}

function EmailIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path
        fillRule="evenodd"
        clipRule="evenodd"
        d="M2.00016 2H14.0002C14.3684 2 14.6668 2.29848 14.6668 2.66667V13.3333C14.6668 13.7015 14.3684 14 14.0002 14H2.00016C1.63197 14 1.3335 13.7015 1.3335 13.3333V2.66667C1.3335 2.29848 1.63197 2 2.00016 2ZM8.04016 7.78867L3.7655 4.15867L2.90216 5.17467L8.04883 9.54467L13.1028 5.17133L12.2308 4.16267L8.04016 7.78867Z"
        fill="#575756"
      />
    </svg>
  );
}
