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

use Intervention\Image\ImageManager;

/**
 * Image
 *
 * This class makes it super easy to render an image with multiple, independently
 * styled and positioned text blocks. You can control these properties for each text block:
 *
 * - alignment
 * - color
 * - font
 * - line height
 * - size
 *
 * You can also position each text block using specific X and Y coordinates relative
 * to the source image.
 *
 * Currently this has a hard dependency on the `\Intervention\Image` class and the
 * GD2 image library. We will be abstracting away these dependencies soon.
 *
 * @author Josh Lockhart
 * @since  1.0.0
 */
class Image
{
    /**
     * Image
     * @var \Intervention\Image\Image
     * @api
     */
    public $image;

    /**
     * Text objects
     * @var array[\NMC\ImageWithText\Text]
     */
    public $textObjects = array();

    /**
     * Construct from image with text
     * @param string $sourceImage Path to source image
     * @api
     */
    public function __construct($sourceImage)
    {
        $manager = new ImageManager(array('driver' => 'gd'));
        $this->image = $manager->make($sourceImage);
    }

    /**
     * Add text
     * @param \NMC\ImageWithText\Text $text
     */
    public function addText(\NMC\ImageWithText\Text $text)
    {
        $this->textObjects[] = $text;
    }

    /**
     * Draw text onto image
     * @api
     */
    public function drawText()
    {
        foreach ($this->textObjects as $text) {
            $text->renderToImage($this);
        }
    }

    /**
     * Save rendered image to output file
     * @param string $outputImagePath The path to which the image (with text) will be saved
     * @api
     */
    public function render($outputImagePath)
    {
        $this->drawText();
        $this->image->save($outputImagePath);
    }

    /**
     * Get image width
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image->getCore());
    }

    /**
     * Get image
     * @return \Intervention\Image\Image
     */
    public function getImage()
    {
        return $this->image;
    }
}
