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

/**
 * Tests for the Simulic filter.
 *
 * @package    filter_simulic
 * @category   test
 * @copyright  2026 TEXVN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_simulic\text_filter
 */
final class text_filter_test extends \advanced_testcase {
    /**
     * Runs the filter over a text.
     *
     * @param string $text the text
     * @return string the filtered text
     */
    private function filtered(string $text): string {
        return (new text_filter(\context_system::instance(), []))->filter($text);
    }

    /**
     * Counts the labs embedded in a text.
     *
     * @param string $html the filtered text
     * @return int the number of frames
     */
    private function frames(string $html): int {
        return substr_count($html, '<iframe');
    }

    /**
     * Forms of a link that become a lab, with the frame address each should produce.
     *
     * @return array the test cases
     */
    public static function embedded_provider(): array {
        $link = 'https://simulic.com/p/abc234defg';
        $frame = 'https://simulic.com/e/abc234defg';
        return [
            'plain text' => ['See https://simulic.com/p/abc234defg now', $frame],
            'in a paragraph' => ['<p>https://simulic.com/p/abc234defg</p>', $frame],
            'embed address' => ['https://simulic.com/e/abc234defg', $frame],
            'www address' => ['https://www.simulic.com/p/abc234defg', $frame],
            'http address' => ['http://simulic.com/p/abc234defg', $frame],
            'trailing slash' => ['https://simulic.com/p/abc234defg/', $frame],
            'with a query' => ['https://simulic.com/p/abc234defg?present=1', $frame],
            'followed by a full stop' => ['Open https://simulic.com/p/abc234defg.', $frame],
            'link made by the editor' => ['<a href="' . $link . '">' . $link . '</a>', $frame],
            'link with attributes' => ['<a class="x" href="' . $link . '" target="_blank">' . $link . '</a>', $frame],
            'link with single quotes' => ["<a href='" . $link . "'>" . $link . '</a>', $frame],
            'link with inner markup' => ['<a href="' . $link . '"><span>' . $link . '</span></a>', $frame],
            'older link code with capitals' => ['https://simulic.com/p/AbC234', 'https://simulic.com/e/AbC234'],
        ];
    }

    /**
     * Each form of a link becomes exactly one lab.
     *
     * @dataProvider embedded_provider
     * @param string $text the text
     * @param string $src the expected frame address
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('embedded_provider')]
    public function test_link_becomes_lab(string $text, string $src): void {
        $html = $this->filtered($text);
        $this->assertSame(1, $this->frames($html), $html);
        $this->assertStringContainsString('src="' . $src . '"', $html);
    }

    /**
     * Texts that must come back unchanged.
     *
     * @return array the test cases
     */
    public static function unchanged_provider(): array {
        return [
            'no link' => ['<p>Ohm\'s law</p>'],
            'home page' => ['https://simulic.com/'],
            'lab page' => ['https://simulic.com/en/physics/ohm/'],
            'link with its own wording' => ['<a href="https://simulic.com/p/abc234defg">Open the lab</a>'],
            'another site' => ['https://notsimulic.com/p/abc234defg'],
            'another site with the name in its path' => ['https://example.com/simulic.com/p/abc234defg'],
            'subdomain of another site' => ['https://simulic.com.example.org/p/abc234defg'],
            'deeper path' => ['https://simulic.com/p/abc234defg/edit'],
            'code too short' => ['https://simulic.com/p/abc'],
            'code too long' => ['https://simulic.com/p/abc234defgabc234defg'],
            'code with other characters' => ['https://simulic.com/p/abc234-defg'],
            'in preformatted text' => ['<pre>https://simulic.com/p/abc234defg</pre>'],
            'in code' => ['<code>https://simulic.com/p/abc234defg</code>'],
            'in a text box' => ['<textarea>https://simulic.com/p/abc234defg</textarea>'],
            'in an attribute' => ['<img src="x.png" alt="https://simulic.com/p/abc234defg">'],
            'already an iframe' => ['<iframe src="https://simulic.com/e/abc234defg"></iframe>'],
        ];
    }

    /**
     * Anything that is not a Simulic class link stays as written.
     *
     * @dataProvider unchanged_provider
     * @param string $text the text
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('unchanged_provider')]
    public function test_other_text_is_unchanged(string $text): void {
        $this->assertSame($text, $this->filtered($text));
    }

    /**
     * Several links in one text each become a lab, and the words around them stay.
     */
    public function test_several_links(): void {
        $html = $this->filtered('<p>First https://simulic.com/p/abc234defg</p><pre>x</pre>'
            . '<p>then https://simulic.com/p/xyz234defg</p>');
        $this->assertSame(2, $this->frames($html));
        $this->assertStringContainsString('First ', $html);
        $this->assertStringContainsString('then ', $html);
        $this->assertStringContainsString('https://simulic.com/e/xyz234defg', $html);
    }

    /**
     * The frame is labelled for screen readers and loads only when it is reached.
     */
    public function test_frame_attributes(): void {
        $html = $this->filtered('https://simulic.com/p/abc234defg');
        $this->assertStringContainsString('title="' . get_string('frametitle', 'filter_simulic') . '"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('allow="fullscreen"', $html);
    }

    /**
     * A query cannot add attributes to the frame.
     */
    public function test_query_is_dropped(): void {
        $html = $this->filtered('https://simulic.com/p/abc234defg?x="onload="alert(1)');
        $this->assertStringNotContainsString('onload="alert', substr($html, 0, strpos($html, '</iframe>')));
        $this->assertStringContainsString('src="https://simulic.com/e/abc234defg"', $html);
    }
}
