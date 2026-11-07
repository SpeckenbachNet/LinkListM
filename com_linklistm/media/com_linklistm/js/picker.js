/**
 * LinkListM editor button picker: builds the {linklistm ...} tag from the form and hands it
 * to the editor via postMessage (Joomla's editor "modal" action inserts event.data.html).
 */
(() => {
  'use strict';

  const form = document.getElementById('linklistm-picker');

  if (!form) {
    return;
  }

  const preview = document.getElementById('linklistm-picker-tag');
  const colsRow = document.getElementById('linklistm-picker-cols');
  const value = (name) => (form.querySelector(`input[name="${name}"]:checked`) || {}).value;

  // Only values that differ from the plugin defaults end up in the tag
  const buildTag = () => {
    const parts = ['linklistm'];
    const cats = [...form.querySelectorAll('input[name="cat"]:checked')].map((box) => box.value);
    const style = value('style');

    if (cats.length) parts.push(`cat=${cats.join(',')}`);
    if (value('sub') === '0') parts.push('sub=0');
    if (style === 'tiles') parts.push('style=tiles');
    if (style === 'tiles' && value('cols') === '3') parts.push('cols=3');
    if (value('desc') === '0') parts.push('desc=0');
    if (value('domain') === '0') parts.push('domain=0');

    return `{${parts.join(' ')}}`;
  };

  const update = () => {
    preview.textContent = buildTag();
    colsRow.hidden = value('style') !== 'tiles';
  };

  const send = (data) => window.parent.postMessage(data, window.location.origin);

  form.addEventListener('change', update);
  document.getElementById('linklistm-picker-insert').addEventListener('click', () => {
    send({ messageType: 'joomla:content-select', html: buildTag() });
  });
  document.getElementById('linklistm-picker-cancel').addEventListener('click', () => {
    send({ messageType: 'joomla:cancel' });
  });

  update();
})();
