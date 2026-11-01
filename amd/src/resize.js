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
 * Fits each Simulic frame to the height its lab reports.
 *
 * Only messages from simulic.com are read, and only the height of the frame that sent the message changes.
 *
 * @module     filter_simulic/resize
 * @copyright  2026 TEXVN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const ORIGIN = 'https://simulic.com';
const MAX_HEIGHT = 20000;

let listening = false;

/**
 * Start listening for height messages from the labs on this page.
 */
export const init = () => {
    if (listening) {
        return;
    }
    listening = true;

    window.addEventListener('message', (event) => {
        if (event.origin !== ORIGIN || !event.data || event.data.type !== 'simulic:resize') {
            return;
        }
        const height = Number(event.data.height);
        if (!(height > 0 && height < MAX_HEIGHT)) {
            return;
        }
        const frame = Array.from(document.querySelectorAll('iframe.filter-simulic-frame'))
            .find((candidate) => candidate.contentWindow === event.source);
        if (frame) {
            frame.style.height = `${Math.ceil(height)}px`;
        }
    });
};
