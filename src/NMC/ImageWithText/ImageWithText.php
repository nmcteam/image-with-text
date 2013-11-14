<?php
/**
 * Image with Text
 *
 * @author      Josh Lockhart <josh@newmediacampaigns.com>
 * @copyright   2013 Josh Lockhart
 * @link        https://github.com/nmcteam/image-with-text
 * @license     MIT
 * @version     1.0.0
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
 * Image with Text
 *
 * This class makes it super easy to render images with custom overlayed text.
 * You can control the text color, alignment, font size, font family, and line height.
 * Adjust the `startX` and `startY` coordinates to position the rendered text.
 *
 * [Lines]
 *
 * You must define available lines and the maximum characters allowed on each line.
 * Use the `addLine()` or `addLines()` methods to do so.
 *
 * [Alignment]
 *
 * You can align text left, center, or right. Use the `startX` and `startY` variables
 * to position the text. If you use left or center alignment, these coordinates
 * represent the top-left position of the text bonding box. If you use right
 * alignment, these coordinates represent the top-right position of the text bounding box.
 *
 * [Style]
 *
 * You can use any of these variables to format your embedded text. If you specify
 * a custom font, you must specify the path to a TTF or OTF font.
 *
 * - textColor
 * - textSize
 * - textFont
 * - textAlign
 * - textLineHeight
 *
 * @author Josh Lockhart
 * @since  1.0.0
 */
class ImageWithText
{
    /**
     * Image
     * @var \Intervention\Image\Image
     * @api
     */
    public $image;

    /**
     * Text
     * @var string
     * @api
     */
    public $text = 'Hello world';

    /**
     * X coordinate offset from which text will be positioned
     * @var int
     * @api
     */
    public $startX = 0;

    /**
     * Y coordinate offset from which text will be positioned
     * @var int
     * @api
     */
    public $startY = 0;

    /**
     * Text color (Hexadecimal, without "#" prefix... "000000")
     * @var string
     * @api
     */
    public $textColor = '000000';

    /**
     * Text size (measured in pts)
     * @var int
     * @api
     */
    public $textSize = 16;

    /**
     * Text font (path to TTF or OTF file)
     * @var string
     * @api
     */
    public $textFont = 'arial.ttf';

    /**
     * Text alignment (one of "left", "center", or "right")
     * @var string
     * @api
     */
    public $textAlign = 'left';

    /**
     * Text line height (measured in pts)
     * @var int
     * @api
     */
    public $textLineHeight = 24;

    /**
     * Array of available lines, with character counts and allocated words
     * @var array
     */
    protected $lines;

    /**
     * Construct from image with text
     * @param string $sourceImage Path to source image
     * @param string $text        The text to write on the image
     * @api
     */
    public function __construct($sourceImage, $text)
    {
        $this->image = \Intervention\Image\Image::make($sourceImage);
        $this->text = $text;
    }

    /**
     * Add available line of text
     * @param  int  Number of characters available on this line
     * @return void
     * @api
     */
    public function addLine($maxCharacters = 80)
    {
        $this->lines[] = array(
            'charsMax' => $maxCharacters,
            'chars' => 0,
            'words' => array(),
            'full' => false
        );
    }

    /**
     * Batch add avaialble lines of text
     *
     * This method accepts unlimited arguments. Each argument MUST be an integer
     * representing the maximum number of characters allowed on a line.
     *
     * @return void
     * @api
     */
    public function addLines()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $this->addLine($arg);
        }
    }

    /**
     * Render image
     * @param  string $outputImagePath The path to which the image (with text) will be saved
     * @return void
     * @api
     */
    public function render($outputImagePath)
    {
        $this->renderText();
        $this->image->save($outputImagePath);
    }

    /**
     * Render text onto image
     * @return void
     */
    protected function renderText()
    {
        // Distribute text to available lines
        $this->parseText();

        // Max line width and height
        $maxLineWidth = 0;
        $maxLineHeight = 0;

        // Calculate line dimensions
        for ($i = 0; $i < count($this->lines); $i++) {
            // Concat words
            $line =& $this->lines[$i];
            $lineText = implode(' ', $line['words']);

            // Determine line dimensions
            $lineBoundingBox = imagettfbbox($this->textSize, 0, $this->textFont, $lineText);
            $line['height'] = abs($lineBoundingBox[7] - $lineBoundingBox[1]); // (upper left corner, Y position) - (lower left corner, Y position)
            $line['width'] = abs($lineBoundingBox[0] - $lineBoundingBox[2]); // (lower left corner, X position) - (lower right corner, X position)
            $maxLineWidth = max($maxLineWidth, $line['width']);
            $maxLineHeight = max($maxLineHeight, $line['height']);
        }

        // Append text with offsets
        for ($j = 0; $j < count($this->lines); $j++) {
            $line =& $this->lines[$j];

            if ($this->textAlign === 'left') {
                $offsetX = $this->startX;
                $offsetY = $this->startY + $this->textLineHeight + ($this->textLineHeight * $j);
            }

            if ($this->textAlign === 'center') {
                $imageWidth = imagesx($this->image->resource);
                $offsetX = (($maxLineWidth - $line['width']) / 2) + $this->startX;
                $offsetY = $this->startY + $this->textLineHeight + ($this->textLineHeight * $j);
            }

            if ($this->textAlign === 'right') {
                $imageWidth = imagesx($this->image->resource);
                $offsetX = $imageWidth - $line['width'] - $this->startX;
                $offsetY = $this->startY + $this->textLineHeight + ($this->textLineHeight * $j);
            }

            $this->image->text(implode(' ', $line['words']), $offsetX, $offsetY, $this->textSize, $this->textColor, 0, $this->textFont);
        }
    }

    /**
     * Parse text, allocate to available lines
     * @param  string     $text The custom message
     * @throws \Exception If text is too long for card
     */
    protected function parseText()
    {
        // Explode input text on word boundaries
        $words = explode(' ', $this->text);

        // Fill buckets with words, toss exception if exceed available buckets
        while ($words) {
            $tooLong = true;
            $word = array_shift($words);

            for ($i = 0; $i < count($this->lines); $i++) {
                $line =& $this->lines[$i];
                if ($line['full'] === false) {
                    $charsPotential = strlen($word) + $line['chars'];

                    if ($charsPotential <= $line['charsMax']) {
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
