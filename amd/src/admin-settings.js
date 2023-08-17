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

/**
 * Javascript functions to add/remove/orders a row of one html block entry
 * that contains three fields for the name of the block, the html for the
 * block and a possible category limitation. This is suited for and works
 * with the template setting_config_htmlblock.mustache only.
 *
 * @module      tiny_htmlblock
 * @copyright   2023 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Class names for the fa icons in the action menu.
const classDown = 'fa-arrow-down';
const classUp = 'fa-arrow-up';
const classAdd = 'fa-plus';
const classDel = 'fa-trash';
// Count the rows during the init method and whenever a new row is added to avoid using the
// same number twice when one row is deleted and another one is added. This would otherwise
// cause the same name attribute for the input fields in two different rows.
let rowcount = 0;
// Selector to find all rows for each html block configuration.
const selectorRows = '.row.htmlblock';

/**
 * The add button that was clicked. This button is in the last row. Therefore, it creates
 * a clone of the entire row. In the cloned row we need to update the input elements (id and
 * name attribute -> increase the tailing sequence number by 1, empty any value attributes).
 * Also, in the cloned row the event must be attached to the icons.
 * For the existing row that was cloned, the icons must be adjusted (remove the + icon, add the
 * trash icon and the down icon).
 * @param {Node} row
 */
const insertRow = row => {
  const newRow = row.cloneNode(true);
  const parts = newRow.querySelector('input').getAttribute('name').split('_');
  rowcount++;
  const re = new RegExp('_' + parts[parts.length - 1] + '$');
  newRow.querySelectorAll('input, textarea').forEach(input => {
    ['name', 'id', 'value'].forEach(attr => {
      if (attr === 'value') {
        input.value = '';
        return;
      }
      let content = input.getAttribute(attr).replace(re, '_' + rowcount.toString());
      input.setAttribute(attr, content);
    });
  });
  row.parentNode.insertBefore(newRow, row.nextSibling);
  fixButtons(row);
  fixButtons(newRow);
  newRow.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', function(e) {
      handleRow(e);
    });
  });
};

/**
 * Remove the current row with input field for name, textarea and category selector.
 * @param {Node} row
 */
const deleteRow = row => {
  row.remove();
  // Removing a row is usually no problem, only if the first row was deleted, the
  // new first row needs to get the up button removed.
  fixButtons(document.querySelector('.row.htmlblock'));
};

/**
 * Move a row up or down, e.g. swap position with its predecessor or successor.
 *
 * @param {Node} row
 * @param {string} dir
 */
const moveRow = (row, dir) => {
  if (dir === 'up') {
    row.parentNode.insertBefore(row, row.previousElementSibling);
    fixButtons(row.nextElementSibling);
  } else {
    row.parentNode.insertBefore(row.nextElementSibling, row);
    fixButtons(row.previousElementSibling);
  }
  fixButtons(row);

};

/**
 * Handle the click on any of the icons in a row of one html block configuration.
 * @param {Event} event
 */
const handleRow = event => {
  event.preventDefault();
  const row = event.target.parentNode.parentNode.parentNode.parentNode;
  if (event.target.classList.contains(classDel)) {
    deleteRow(row);
  } else if (event.target.classList.contains(classAdd)) {
    insertRow(row);
  } else if (event.target.classList.contains(classUp)) {
    moveRow(row, 'up');
  } else if (event.target.classList.contains(classDown)) {
    moveRow(row, 'down');
  }
};

/**
 * Whenever a row has been moved added or deleted, the action icons of the
 * row itself and the adjacent row must be adjusted, e.g. if a row is moved up and
 * is on first position, the up arrow must be hidden.
 * @param {Node} row
 */
const fixButtons = row => {
  // Get a list of all existing rows.
  const rows = document.querySelectorAll(selectorRows);
  // And find out at with position the current row is located at.
  let i = 0;
  for (i; i < rows.length; i++) {
    if (rows[i] === row) {
      break;
    }
  }
  // Fix the buttons:
  // - the first row has no up button.
  // - the last row has an add button while all other rows have a delete button.
  // - the last row also has no down button.
  row.querySelectorAll('a > i.fa').forEach(e => {
    if (e.classList.contains(classDown)) {
      if (i < rows.length - 1) {
        e.parentNode.classList.remove('hidden');
      } else {
        e.parentNode.classList.add('hidden');
      }
    } else if (e.classList.contains(classUp)) {
      if (i > 0) {
        e.parentNode.classList.remove('hidden');
      } else {
        e.parentNode.classList.add('hidden');
      }
    } else if (e.classList.contains(classAdd)) {
      if (i === rows.length - 1) {
        e.parentNode.classList.remove('hidden');
      } else {
        e.parentNode.classList.add('hidden');
      }
    } else if (e.classList.contains(classDel)) {
      if (i === rows.length - 1) {
        e.parentNode.classList.add('hidden');
      } else {
        e.parentNode.classList.remove('hidden');
      }
    }
  });
};

/**
 * Initialize event handlers for all buttons inside the input fields for one item.
 * @param {string} name of settings field, for which the js handling is needed.
 */
export const init = name => {
  const root = document.querySelector('.' + name);
  if (!root) {
    return;
  }
  const buttons = root.getElementsByTagName('a');
  for (let i = 0; i < buttons.length; i++) {
    buttons.item(i).addEventListener('click', function(e) {
      handleRow(e);
    });
  }
  rowcount = document.querySelectorAll(selectorRows).length;
};