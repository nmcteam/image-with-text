<?php
/**
 * Image with Text
 *
 * @author      Josh Lockhart <josh@newmediacampaigns.com>
 * @copyright   2013 Josh Lockhart
 * @link        https://github.com/nmcteam/image-with-text
 * @license     MIT
 * @version     2.0.2
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace NMC\ImageWithText;

/**
 * Text
 *
 * This class represents a single text block that will be drawn onto
 * an image. You may control this text block's:
 *
 * - alignment
 * - color
 * - font
 * - line height
 * - size
 *
 * You can also position this text block using specific X and Y coordinates relative
 * to its source image.
 *
 * Currently this has a hard dependency on the `\Intervention\Image` class and the
 * GD2 image library. We will be abstracting away these dependencies soon.
 *
 * @author Josh Lockhart
 * @since  2.0.0
 */
class Text
{
    /**
     * Text
     * @var string
     * @api
     */
    public $text = 'Hello world';

    /**
     * Width (max number of characters per line)
     * @var int
     */
    public $width = 80;

    /**
     * X coordinate offset from which text will be positioned relative to source image
     * @var int
     * @api
     */
    public $startX = 0;

    /**
     * Y coordinate offset from which text will be positioned relative to source image
     * @var int
     * @api
     */
    public $startY = 0;

    /**
     * Text alignment (one of "left", "center", or "right")
     * @var string
     * @api
     */
    public $align = 'left';

    /**
     * Text color (Hexadecimal, without "#" prefix... "000000")
     * @var string
     * @api
     */
    public $color = '000000';

    /**
     * Text font (path to TTF or OTF file)
     * @var string
     * @api
     */
    public $font = 'arial.ttf';

    /**
     * Text line height (measured in pts)
     * @var int
     * @api
     */
    public $lineHeight = 24;

    /**
     * Text size (measured in pts)
     * @var int
     * @api
     */
    public $size = 16;

    /**
     * Array of available lines, with character counts and allocated words
     * @var array
     */
    protected $lines;

    /**
     * Construct
     * @param string $text     The text
     * @param int    $numLines The total number of available lines
     * @param int    $width    The maximum number of characters avaiable per line
     */
    public function __construct($text, $numLines = 1, $width = 80)
    {
        $this->text = $text;
        $this->width = $width;
        $this->addLines($numLines);
    }

    /**
     * Add available lines of text
     * @param int $numLines The number of available lines to add
     * @api
     */
    public function addLines($numLines = 1)
    {
        for ($i = 0; $i < $numLines; $i++) {
            $this->lines[] = array(
                'chars' => 0,
                'words' => array(),
                'full' => false
            );
        }
    }

    /**
     * Render text on image
     * @param \Intervention\Image\Image $image The image on which the text will be rendered
     * @api
     */
    public function renderToImage(\NMC\ImageWithText\Image $image)
    {
        // Allocate words to lines
        $this->distributeText();

        // Calculate maximum potential line width (close enough) in pixels
        $maxWidthString = implode('', array_fill(0, $this->width, 'x'));
        $maxWidthBoundingBox = imagettfbbox($this->size, 0, $this->font, $maxWidthString);
        $maxLineWidth = abs($maxWidthBoundingBox[0] - $maxWidthBoundingBox[2]); // (lower left corner, X position) - (lower right corner, X position)

        // Calculate each line width in pixels for alignment purposes
        for ($j = 0; $j < count($this->lines); $j++) {
            // Fetch line
            $line =& $this->lines[$j];

            // Remove unused lines
            if (empty($line['words'])) {
                unset($this->lines[$j]);
                continue;
            }

            // Calculate width
            $lineText = implode(' ', $line['words']);
            $lineBoundingBox = imagettfbbox($this->size, 0, $this->font, $lineText);
            $line['width'] = abs($lineBoundingBox[0] - $lineBoundingBox[2]); // (lower left corner, X position) - (lower right corner, X position)
            $line['text'] = $lineText;
        }

        // Calculate line offsets
        for ($i = 0; $i < count($this->lines); $i++) {
            // Fetch line
            if (array_key_exists($i, $this->lines)) {
                $line =& $this->lines[$i];

                // Calculate line width in pixels
                $lineBoundingBox = imagettfbbox($this->size, 0, $this->font, $line['text']);
                $lineWidth = abs($lineBoundingBox[0] - $lineBoundingBox[2]); // (lower left corner, X position) - (lower right corner, X position)

                // Calculate line X,Y offsets in pixels
                switch ($this->align) {
                    case 'left':
                        $offsetX = $this->startX;
                        $offsetY = $this->startY + $this->lineHeight + ($this->lineHeight * $i);
                        break;
                    case 'center':
                        $imageWidth = $image->getWidth();
                        $offsetX = (($maxLineWidth - $lineWidth) / 2) + $this->startX;
                        $offsetY = $this->startY + $this->lineHeight + ($this->lineHeight * $i);
                        break;
                    case 'right':
                        $imageWidth = $image->getWidth();
                        $offsetX = $imageWidth - $line['width'] - $this->startX;
                        $offsetY = $this->startY + $this->lineHeight + ($this->lineHeight * $i);
                        break;
                }

                // Render text onto image
                $fontSize = $this->size;
                $fontColor = $this->color;
                $fontFile = $this->font;
                $image->getImage()->text($line['text'], $offsetX, $offsetY, function ($font) use ($fontSize, $fontColor, $fontFile) {
                    $font->size($fontSize);
                    $font->color($fontColor);
                    $font->file($fontFile);
                });
            }
        }
    }

    /**
     * Distribute text to lines
     * @throws \Exception If text is too long given available lines and max character width
     */
    protected function distributeText()
    {
        // Explode input text on word boundaries
        $words = explode(' ', $this->text);

        // Fill lines with words, toss exception if exceed available lines
        while ($words) {
            $tooLong = true;
            $word = array_shift($words);

            for ($i = 0; $i < count($this->lines); $i++) {
                $line =& $this->lines[$i];
                if ($line['full'] === false) {
                    $charsPotential = strlen($word) + $line['chars'];

                    if ($charsPotential <= $this->width) {
                        array_push($line['words'], $word);
                        $line['chars'] = $charsPotential;
                        $tooLong = false;
                        break;
                    } else {
                        $line['full'] = true;
                    }
                }
            }
        }

        // Throw if too long
        if ($tooLong === true) {
            throw new \Exception('Text is too long');
        }
    }
}
