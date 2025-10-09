import CustomButton from '../button/Button.jsx';
import EventCard from '../../components/EventCard.jsx';
import PostSearchControl from '../../components/PostSearchControl.jsx';

const { __ } = wp.i18n;
const { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, SelectControl, Spinner, Button, PanelRow } = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

const EditComponent = ({ attributes, setAttributes }) => {
  const { selectionMode, selectedEventIds = [], chronicleImageUrl, chronicleImageId } = attributes;

  const blockProps = useBlockProps();

  const [autoEvents, setAutoEvents] = useState([]);
  const [isLoadingAuto, setIsLoadingAuto] = useState(true);

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

  const { manualEvents, isLoadingManual } = useSelect(
    (select) => {
      if (selectionMode !== 'manual' || !selectedEventIds || selectedEventIds.length === 0) {
        return { manualEvents: [], isLoadingManual: false };
      }

      const { getEntityRecords, isResolving } = select('core');

      const query = { include: selectedEventIds, per_page: selectedEventIds.length, _embed: true };
      const records = getEntityRecords('postType', 'tribe_events', query);

      return {
        manualEvents: records,
        isLoadingManual: isResolving('getEntityRecords', ['postType', 'tribe_events', query]),
      };
    },
    [selectionMode, selectedEventIds],
  );

  const handleAddEvent = (event) => {
    if (event && !selectedEventIds.includes(event.id)) {
      const updatedIds = [...selectedEventIds, event.id];
      setAttributes({ selectedEventIds: updatedIds });
    }
  };

  const handleRemoveEvent = (eventIdToRemove) => {
    const updatedIds = selectedEventIds.filter((id) => id !== eventIdToRemove);
    setAttributes({ selectedEventIds: updatedIds });
  };

  const sortedManualEvents = selectedEventIds
    .map((id) => manualEvents?.find((event) => event.id === id))
    .filter(Boolean);

  const eventsToDisplay = selectionMode === 'latest' ? autoEvents : sortedManualEvents;
  const showSpinner = selectionMode === 'latest' ? isLoadingAuto : isLoadingManual;

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
            onChange={(newMode) => setAttributes({ selectionMode: newMode, selectedEventIds: [] })}
          />
          {selectionMode === 'manual' && (
            <>
              <p style={{ marginTop: '1rem', marginBottom: '0.5rem' }}>
                <strong>{__('Événements sélectionnés :', 'amnesty')}</strong>
              </p>
              {isLoadingManual && <Spinner />}
              {!isLoadingManual &&
                sortedManualEvents.length > 0 &&
                sortedManualEvents.map((event) => (
                  <PanelRow key={event.id}>
                    <div
                      style={{ flex: 1 }}
                      dangerouslySetInnerHTML={{ __html: event.title.rendered }}
                    />
                    <Button isLink isDestructive onClick={() => handleRemoveEvent(event.id)}>
                      {__('Retirer', 'amnesty')}
                    </Button>
                  </PanelRow>
                ))}
              {!isLoadingManual && sortedManualEvents.length === 0 && (
                <p style={{ fontStyle: 'italic', color: '#666' }}>
                  {__('Aucun événement sélectionné.', 'amnesty')}
                </p>
              )}
              {selectedEventIds.length < 2 && (
                <div style={{ marginTop: '1rem', paddingTop: '1rem', borderTop: '1px solid #ddd' }}>
                  <p>
                    <strong>{__('Ajouter un événement', 'amnesty')}</strong>
                  </p>
                  <PostSearchControl
                    onPostSelect={handleAddEvent}
                    allowedTypes={['tribe_events']}
                  />
                </div>
              )}
            </>
          )}
        </PanelBody>
        <PanelBody title={__('Image La Chronique', 'amnesty')}>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(media) =>
                setAttributes({ chronicleImageId: media.id, chronicleImageUrl: media.url })
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
