import { hasInvalidTestimonials } from '../../clh/testimonial-validation';

const { useDispatch, useSelect } = wp.data;
const { useEffect } = wp.element;
const { __ } = wp.i18n;
const { registerPlugin } = wp.plugins;

const LOCK_NAME = 'clh-testimonials';
const NOTICE_ID = 'clh-testimonials-invalid';

// Watches the testimonials blocks from outside them: the code editor unmounts the block
// components, so a lock held by a block would be released while its invalid data stays.
const ClhTestimonialsValidation = () => {
  const { isInvalid, isCodeEditor } = useSelect((select) => {
    const editor = select('core/editor');
    const isText = editor.getEditorMode() === 'text';
    // The code editor drops the parsed blocks on each change; getEditorBlocks() parses the
    // content again. In the visual editor, the block editor store holds the current blocks.
    const blocks = isText ? editor.getEditorBlocks() : select('core/block-editor').getBlocks();

    return {
      isInvalid: hasInvalidTestimonials(blocks, new Date().getFullYear()),
      isCodeEditor: isText,
    };
  }, []);
  const { lockPostSaving, unlockPostSaving, lockPostAutosaving, unlockPostAutosaving } =
    useDispatch('core/editor');
  const { createErrorNotice, removeNotice } = useDispatch('core/notices');

  // The saving lock does not stop autosaves, which overwrite a draft for its author.
  useEffect(() => {
    const unlock = () => {
      unlockPostSaving(LOCK_NAME);
      unlockPostAutosaving(LOCK_NAME);
    };

    if (isInvalid) {
      lockPostSaving(LOCK_NAME);
      lockPostAutosaving(LOCK_NAME);
    } else {
      unlock();
    }

    return unlock;
  }, [isInvalid, lockPostSaving, unlockPostSaving, lockPostAutosaving, unlockPostAutosaving]);

  // The code editor hides the block and its error messages.
  useEffect(() => {
    if (isInvalid && isCodeEditor) {
      createErrorNotice(
        __(
          'Corrigez les témoignages du bloc « Ils vous disent merci » dans l’éditeur visuel pour pouvoir enregistrer la page.',
          'amnesty',
        ),
        { id: NOTICE_ID, isDismissible: false },
      );
    } else {
      removeNotice(NOTICE_ID);
    }
  }, [isInvalid, isCodeEditor, createErrorNotice, removeNotice]);

  return null;
};

registerPlugin('amnesty-clh-testimonials-validation', { render: ClhTestimonialsValidation });
