import CustomButton from '../button/Button.jsx';
import EventCard from '../../components/EventCard.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, SelectControl, Spinner, Button } = wp.components;
const { useSelect } = wp.data;
const { useState, useEffect } = wp.element;
const apiFetch = wp.apiFetch;

const displayComponentPreview = (blockProps, attributes) => {
  const mockEvents = [];

  return (
    <section {...blockProps} className="agenda-chronicle-homepage">
      <div className="agenda-homepage">
        <h2 className="agenda-homepage-title">Agenda</h2>
        <div className="agenda-homepage-events">
          {mockEvents.map((event) => (
            <EventCard key={event?.id} event={event} />
          ))}
        </div>
        <CustomButton label="Voir les événements" link="#" />
      </div>
      <div className="chronicle-homepage">
        <h2 className="chronicle-homepage-title">A découvrir</h2>
        <div className="chronicle-card">
          <div className="chronicle-card-image-container">
            <img
              src={
                attributes.chronicleImageUrl ||
                'https://placehold.co/600x400/555/FFF/png?text=La+Chronique'
              }
              className="chronicle-card-image"
              alt=""
            />
          </div>
          <div className="chronicle-homepage">
            <h2 className="chronicle-homepage-title">A découvrir</h2>
            <div className="chronicle-card">
              <div className="chronicle-card-image-container">
                <img
                  src={
                    attributes.chronicleImageUrl ||
                    'https://placehold.co/600x400/555/FFF/png?text=La+Chronique'
                  }
                  className="chronicle-card-image"
                  alt=""
                />
              </div>
              <div className="chronicle-card-content">
                <h3 className="chronicle-card-title">La chronique</h3>
                <p className="chronicle-card-chapo">Le magazine des droits humains</p>
                <CustomButton
                  label="Abonnez-vous pour 3€/mois"
                  size="medium"
                  icon="arrow-right"
                  link="#" // On met un lien factice pour l'aperçu
                  alignment="center"
                  style="bg-yellow"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

const EditComponent = ({ attributes, setAttributes, isPreview }) => {
  const { selectionMode, selectedEventIds = [], chronicleImageUrl, chronicleImageId } = attributes;
  const blockProps = useBlockProps();

  if (isPreview) displayComponentPreview(blockProps, attributes);

  const [autoEvents, setAutoEvents] = useState([]);
  const [isLoadingAuto, setIsLoadingAuto] = useState(true);

  const [manualEventsState, setManualEventsState] = useState([]);
  const [isLoadingManualState, setIsLoadingManualState] = useState(false);

  useEffect(() => {
    setIsLoadingAuto(true);
    apiFetch({ path: '/wp/v2/tribe_events?per_page=50&_embed=true&_locale=user' }).then(
      (allEvents) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const upcomingEvents = allEvents
          .filter((event) => {
            const startDate = new Date(event.meta?._EventStartDate);
            startDate.setHours(0, 0, 0, 0);
            return startDate >= today;
          })
          .sort((a, b) => new Date(a.meta._EventStartDate) - new Date(b.meta._EventStartDate));

        setAutoEvents(upcomingEvents.slice(0, 2));
        setIsLoadingAuto(false);
      },
    );
  }, []);

  const { allEventsForSelector, isLoadingSelector } = useSelect(
    (select) => {
      if (selectionMode !== 'manual') {
        return { allEventsForSelector: [], isLoadingSelector: false };
      }

      const { getEntityRecords, isResolving } = select('core');

      const query = {
        per_page: 50,
        orderby: 'title',
        order: 'asc',
        _embed: true,
      };

      const records = getEntityRecords('postType', 'tribe_events', query);

      return {
        allEventsForSelector: records || [],
        isLoadingSelector: isResolving('getEntityRecords', ['postType', 'tribe_events', query]),
      };
    },
    [selectionMode],
  );

  useEffect(() => {
    if (selectionMode !== 'manual' || selectedEventIds.length === 0) {
      setManualEventsState([]);
      setIsLoadingManualState(false);
      return;
    }

    setIsLoadingManualState(true);

    const sanitizedSelectedIds = selectedEventIds
      .map((id) => parseInt(id, 10))
      .filter((id) => !Number.isNaN(id) && id > 0);

    apiFetch({
      path: `/wp/v2/tribe_events?include=${sanitizedSelectedIds.join(',')}&per_page=${sanitizedSelectedIds.length}&_embed=true`,
    })
      .then((events) => {
        const sortedEvents = sanitizedSelectedIds
          .map((id) => events.find((event) => event.id === id))
          .filter(Boolean);

        setManualEventsState(sortedEvents);
        setIsLoadingManualState(false);
      })
      .catch(() => {
        setManualEventsState([]);
        setIsLoadingManualState(false);
      });
  }, [selectionMode, selectedEventIds]);

  const updateSelectedEvent = (eventId, position) => {
    const updatedIds = [...selectedEventIds];
    updatedIds[position] = parseInt(eventId, 10) || 0;
    setAttributes({ selectedEventIds: updatedIds.filter((id) => id) });
  };

  const eventsToDisplay = selectionMode === 'latest' ? autoEvents : manualEventsState;
  const showSpinner =
    selectionMode === 'latest' ? isLoadingAuto : isLoadingSelector || isLoadingManualState;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Paramètres du Bloc Agenda', 'amnesty')}>
          <SelectControl
            label={__("Mode d'affichage des événements", 'amnesty')}
            value={selectionMode}
            options={[
              { label: __('Événements à venir (auto)', 'amnesty'), value: 'latest' },
              { label: __('Sélection manuelle', 'amnesty'), value: 'manual' },
            ]}
            onChange={(newMode) => setAttributes({ selectionMode: newMode })}
          />
          {selectionMode === 'manual' && (
            <>
              <p style={{ marginTop: '1rem' }}>
                <strong>{__('Choisir les événements :', 'amnesty')}</strong>
              </p>
              {isLoadingSelector || isLoadingManualState ? (
                <Spinner />
              ) : (
                <>
                  <SelectControl
                    label={__('Événement 1', 'amnesty')}
                    value={selectedEventIds[0] || ''}
                    options={[
                      { label: __('Choisir un événement', 'amnesty'), value: '' },
                      ...allEventsForSelector.map((event) => ({
                        label: event.title.rendered || __('(Pas de titre)', 'amnesty'),
                        value: event.id,
                      })),
                    ]}
                    onChange={(eventId) => updateSelectedEvent(eventId, 0)}
                  />
                  <SelectControl
                    label={__('Événement 2', 'amnesty')}
                    value={selectedEventIds[1] || ''}
                    options={[
                      { label: __('Choisir un événement', 'amnesty'), value: '' },
                      ...allEventsForSelector.map((event) => ({
                        label: event.title.rendered || __('(Pas de titre)', 'amnesty'),
                        value: event.id,
                      })),
                    ]}
                    onChange={(eventId) => updateSelectedEvent(eventId, 1)}
                  />
                </>
              )}
            </>
          )}
        </PanelBody>

        <PanelBody title={__('Image La Chronique', 'amnesty')}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(media) =>
                setAttributes({
                  chronicleImageId: media.id,
                  chronicleImageUrl: media.url,
                })
              }
              allowedTypes={['image']}
              value={chronicleImageId}
              render={({ open }) => (
                <Button onClick={open} isSecondary>
                  {chronicleImageUrl
                    ? __('Changer l’image', 'amnesty')
                    : __('Sélectionner une image', 'amnesty')}
                </Button>
              )}
            />
          </MediaUploadCheck>

          {chronicleImageUrl && (
            <div style={{ marginTop: '10px' }}>
              <img src={chronicleImageUrl} alt="" style={{ maxWidth: '100%', height: 'auto' }} />
            </div>
          )}
        </PanelBody>
      </InspectorControls>

      <section {...blockProps} className="agenda-chronicle-homepage">
        <div className="agenda-homepage">
          <h2 className="agenda-homepage-title">Agenda</h2>
          {showSpinner ? (
            <Spinner />
          ) : (
            <div className="agenda-homepage-events">
              {eventsToDisplay && eventsToDisplay.length > 0 ? (
                eventsToDisplay.map((event) => <EventCard key={event.id} event={event} />)
              ) : (
                <p>{__('Aucun événement à venir trouvé.', 'amnesty')}</p>
              )}
            </div>
          )}
          <CustomButton
            label="Voir les événements près de chez moi"
            size="medium"
            icon="arrow-right"
            link="/evenements"
            alignment="left"
            style="outline-black"
          />
        </div>

        <div className="chronicle-homepage">
          <h2 className="chronicle-homepage-title">A découvrir</h2>
          <div className="chronicle-card">
            <div className="chronicle-card-image-container">
              <img src={chronicleImageUrl} className="chronicle-card-image" alt="" />
            </div>
            <div className="chronicle-card-content">
              <h3 className="chronicle-card-title">La chronique</h3>
              <p className="chronicle-card-chapo">Le magazine des droits humains</p>
              <CustomButton
                label="Abonnez-vous pour 3€/mois"
                size="medium"
                icon="arrow-right"
                link="/magazine-la-chronique"
                alignment="center"
                style="bg-yellow"
              />
            </div>
          </div>
        </div>
      </section>
    </>
  );
};

export default EditComponent;
