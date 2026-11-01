<?php
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

namespace filter_simulic;

// Moodle 4.5 renamed the base class of filters.
if (class_exists(\core_filters\text_filter::class)) {
    class_alias(\core_filters\text_filter::class, base_text_filter::class);
} else {
    class_alias(\moodle_text_filter::class, base_text_filter::class);
}

/**
 * Turns Simulic class links into an embedded lab.
 *
 * Teachers paste a link such as https://simulic.com/p/abc234defg into any text, and students see the lab in the page.
 * A link with its own wording ("Open the lab") stays a link, so teachers can choose between the two.
 *
 * @package    filter_simulic
 * @copyright  2026 TEXVN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends base_text_filter {
    /** @var string Where every lab is served from, whichever form of the address the teacher pasted. */
    private const ORIGIN = 'https://simulic.com';

    /** @var string A Simulic class link, or its embed address. The first group is the link code. */
    private const LINK = 'https?://(?:www\.)?simulic\.com/[pe]/([0-9A-Za-z]{6,16})'
        . '(?![0-9A-Za-z_-])/?(?![0-9A-Za-z_/-])(?:\?[^\s<>"\']*)?';

    /** @var string[] Text inside these elements stays as written. */
    private const KEEP_TEXT_IN = ['a', 'button', 'code', 'iframe', 'pre', 'script', 'select', 'style', 'textarea'];

    /**
     * Loads the script that fits each frame to its lab.
     *
     * This runs here and not in filter() because Moodle can cache the filtered text and skip filter() altogether.
     *
     * @param \moodle_page $page the page being shown
     * @param \context $context the context of the page
     */
    public function setup($page, $context) {
        static $done = false;
        if (!$done) {
            $page->requires->js_call_amd('filter_simulic/resize', 'init');
            $done = true;
        }
    }

    /**
     * Replaces Simulic links in the text with embedded labs.
     *
     * @param string $text the text to filter
     * @param array $options filter options
     * @return string the filtered text
     */
    public function filter($text, array $options = []) {
        if (!is_string($text) || stripos($text, 'simulic.com') === false) {
            return $text;
        }
        return $this->replace_bare_links($this->replace_anchors($text));
    }

    /**
     * Replaces anchors whose visible text is the link itself, as editors produce when a URL is pasted.
     *
     * @param string $text the text to filter
     * @return string the text with those anchors replaced
     */
    private function replace_anchors(string $text): string {
        $pattern = '~<a\b[^>]*\bhref\s*=\s*(["\'])(' . self::LINK . ')\1[^>]*>(.*?)</a>~is';
        return preg_replace_callback($pattern, function (array $match): string {
            $visible = trim(html_entity_decode(strip_tags($match[4]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            return preg_match('~^' . self::LINK . '$~i', $visible) ? $this->frame($match[3]) : $match[0];
        }, $text) ?? $text;
    }

    /**
     * Replaces links typed as plain text, leaving tags and protected elements alone.
     *
     * @param string $text the text to filter
     * @return string the text with those links replaced
     */
    private function replace_bare_links(string $text): string {
        $parts = preg_split('~(<[^>]*>)~', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $text;
        }
        $kept = 0;
        foreach ($parts as $index => $part) {
            if ($index % 2 === 1) {
                if (preg_match('~^<(/?)([a-z0-9]+)\b~i', $part, $tag) && in_array(strtolower($tag[2]), self::KEEP_TEXT_IN)) {
                    $kept = $tag[1] === '' ? $kept + 1 : max(0, $kept - 1);
                }
            } else if ($kept === 0) {
                $parts[$index] = preg_replace_callback('~' . self::LINK . '~i', function (array $match): string {
                    return $this->frame($match[1]);
                }, $part) ?? $part;
            }
        }
        return implode('', $parts);
    }

    /**
     * Builds the frame for one lab.
     *
     * @param string $code the link code
     * @return string the HTML of the frame
     */
    private function frame(string $code): string {
        return \html_writer::tag('iframe', '', [
            'class' => 'filter-simulic-frame',
            'src' => self::ORIGIN . '/e/' . $code,
            'title' => get_string('frametitle', 'filter_simulic'),
            'width' => '100%',
            'height' => '640',
            'loading' => 'lazy',
            'allow' => 'fullscreen',
            'style' => 'border:0',
        ]);
    }
}
