const { RichText, URLInputButton, MediaUpload } = wp.blockEditor;
const { Button, DatePicker, IconButton } = wp.components;
const { useState } = wp.element;
const { __ } = wp.i18n;
const { get } = lodash;

const LinkItem = (props) => {
  const [datePickerIsVisible, showDatePicker] = useState(false);
  const setDate = props.createUpdate('date');

  const chooseDate = (value) => {
    setDate(value);
    showDatePicker(false);
  };

  const itemStyles = {};

  if (props.featured_image) {
    itemStyles.backgroundImage = `url('${props.featured_image}')`;
  }

  return (
    <li>
      <article className="linkList-item" style={itemStyles}>
        <span className="linkList-itemMeta">
          <RichText
            tagName="a"
            onChange={props.createUpdate('tagText')}
            value={props.tagText}
            // translators: [admin]
            placeholder={__('Tag Name', 'amnesty')}
            allowedFormats={[]}
            format="string"
          />
          <URLInputButton
            url={props.tagLink}
            onChange={props.createUpdate('tagLink')}
            isPressed={false}
          />
        </span>
        <h3 className="linkList-itemTitle">
          <RichText
            tagName="a"
            onChange={props.createUpdate('title')}
            value={props.title}
            // translators: [admin]
            placeholder={__('Insert Title', 'amnesty')}
            allowedFormats={[]}
            format="string"
          />
          <URLInputButton
            url={props.titleLink}
            onChange={props.createUpdate('titleLink')}
            isPressed={false}
          />
        </h3>

        <div className="postInfo-container">
          {props.showPostDate && (
            <div className="linkList-itemDate">
              {/* translators: [admin/front] */}
              <span className="dateTerm">{__('Date:', 'amnesty')}</span>
              {props.date && (
                <span className="dateDescription">{new Date(props.date).toLocaleDateString()}</span>
              )}
              <Button
                icon="calendar-alt"
                isPressed={false}
                onClick={() => showDatePicker(!datePickerIsVisible)}
              />
              <br />
              {/* translators: [admin] */}
              <small className="linkList-itemDateData">
                <em>{__('Date may render differently on the site frontend', 'amnesty')}</em>
              </small>
              {datePickerIsVisible && (
                <div className="linkList-datePicker">
                  <DatePicker
                    className="dateDescription"
                    currentDate={props.date}
                    onChange={chooseDate}
                  />
                </div>
              )}
            </div>
          )}
          {props.showAuthor && (
            <p className="linkList-itemAuthor">
              {/* translators: [admin/front] */}
              <span className="authorTerm">{__('Author:', 'amnesty')}</span>
              <RichText
                tagName="span"
                className="authorDescription"
                value={props.authorName}
                onChange={props.createUpdate('authorName')}
                multiline={false}
                allowedFormats={[]}
                format="string"
                // translators: [admin]
                placeholder={__('Author Name', 'amnesty')}
              />
            </p>
          )}
        </div>
        <div className="linkList-options">
          {props.featured_image_id && props.featured_image_id !== -1 && (
            <IconButton
              icon="no-alt"
              onClick={() =>
                props.updateMedia({
                  featured_image_id: '',
                  featured_image: '',
                })
              }
            >
              {/* translators: [admin] */ __('Remove Image', 'amnesty')}
            </IconButton>
          )}
          <MediaUpload
            onSelect={({ id, sizes, url }) =>
              props.updateMedia({
                featured_image_id: id,
                featured_image: get(sizes, "['post-half@2x'].url", url),
              })
            }
            value={props.featured_image_id}
            allowedTypes={['image']}
            render={({ open }) => <IconButton icon="format-image" onClick={open} />}
          />
          <Button onClick={props.createRemove} icon="trash" />
        </div>
      </article>
    </li>
  );
};

export default LinkItem;
