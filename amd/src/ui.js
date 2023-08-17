// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

import ModalEvents from 'core/modal_events';
import ModalFactory from 'core/modal_factory';
import Modal from "core/modal";
import Mustache from 'core/mustache';
import {get_string as getString} from 'core/str';
import {component} from './common';
import {getHtmlBlocks} from "./options";

/**
 * Javascript functions to handle the action inside the editor when clicking
 * the toolbar button or the menu item.
 *
 * @module      tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const isNull = a => a === null || a === undefined;

const TEMPLATE = {
  LIST: '<div class="tiny_htmlblock">' +
    '<p>{{intro}}</p>' +
    '<ul>' +
    '{{#list}}' +
    '<li class="hbitem" title="{{name}}">' +
    '{{{html}}}' +
    '</li>' +
    '{{/list}}' +
    '</ul>' +
    '</div>',
  FOOTER: '<button type="button" class="btn btn-secondary" data-action="cancel">{{cancel}}</button>' +
    '<button type="button" class="btn btn-primary" data-action="save">{{submit}}</button>',
};

// Language strings used in the modal dialogue.
let STR = {};
const getStr = async() => {
  const res = await Promise.all([
    getString('cancel', 'core'),
    getString('dlgintro', component),
    getString('dlginsert', component),
    getString('pluginname', component),
  ]);
  [
    'btn_cancel',
    'dlg_intro',
    'btn_insert',
    'title',
  ].map((l, i) => {
    STR[l] = res[i];
    return ''; // Make the linter happy.
  });
};

let  _modal;
let _editor;

const handleAction = async function(editor) {
  if (!_editor) {
    _editor = editor;
    await await getStr();
  }
  _modal = await ModalFactory.create({
    type: Modal.TYPE,
    title: STR.title,
    templateContext: {
      elementid: _editor.id
    },
    removeOnClose: true,
    large: true,
  });

  const footer = Mustache.render(TEMPLATE.FOOTER, {
    cancel: STR.btn_cancel,
    submit: STR.btn_insert,
  });
  const contentText = Mustache.render(TEMPLATE.LIST, {
    intro: STR.dlg_intro,
    list: getHtmlBlocks(_editor),
  });
  _modal.setBody(contentText);
  _modal.setFooter(footer);
  _modal.show();
  _modal.registerEventListeners();
  _modal.registerCloseOnSave();
  _modal.registerCloseOnCancel();
  const $root = _modal.getRoot();
  $root.on(ModalEvents.save, insertHandler);
  $root.on(ModalEvents.cancel, closeHandler);
  $root.get(0).querySelectorAll('li').forEach(el => {
    el.addEventListener('click', evt => {
      evt.preventDefault();
      const liSel = getTargetLi(evt);
      if (isNull(liSel)) {
        return;
      }
      liSel.parentNode.querySelectorAll('li').forEach(li => {
        if (li === liSel) {
          if (!isNull(li.classList)) {
            li.classList.add('selected');
          } else {
            li.setAttribute('class', 'selected');
          }
        } else if (!isNull(li.classList)) {
          li.classList.remove('selected');
        }
      });
    });
  });
};

const insertHandler = function (e) {
  const li = document.querySelector('li.hbitem.selected');
  if (!isNull(li)) {
    _editor.execCommand('mceInsertContent', false, li.innerHTML);
  }
  closeHandler(e);
};

const closeHandler = function (e) {
  if (e) {
    // noop;
  }
  _modal.destroy();
  _modal = null;
};

/**
 * From the submitted event (and target node) get the closest parent li.hbitem.
 * @param {Event} e
 * @return {Node|null}
 */
const getTargetLi = e => {
  let p = e.target;
  while (!isNull(p) && p.nodeType === 1 && p.tagName !== 'LI') {
    p = p.parentNode;
  }
  if (isNull(p.classList)) {
    return null;
  }
  if (p.classList.contains('hbitem')) {
    return p;
  }
  return null;
};

export {
  handleAction,
};