# Image with Text

This class makes it super easy to render images with custom overlayed text.
You can control the text color, alignment, font size, font family, and line height.
Adjust the `startX` and `startY` coordinates to position the rendered text.

## Lines

You must define available lines and the maximum characters allowed on each line.
Use the `addLine()` or `addLines()` methods to do so.

## Alignment

You can align text left, center, or right. Use the `startX` and `startY` variables
to position the text. If you use left or center alignment, these coordinates
represent the top-left position of the text bounding box. If you use right
alignment, these coordinates represent the top-right position of the text bounding box.

## Style

You can use any of these variables to format your embedded text. If you specify
a custom font, you must specify the path to a TTF or OTF font.

* textColor
* textSize
* textFont
* textAlign
* textLineHeight

## Installation

Install this library with [Composer](http://getcomposer.org). Add this to your `composer.json` file:

    {
        "require": {
            "nmcteam/image-with-text": "~1.0.0"
        }
    }

Then run `composer install`.

## Usage

Here's a quick demonstration. You can find this full working demo in the `example/`
directory.

    <?php
    require '../vendor/autoload.php';

    // Create image
    $imageSource = dirname(__FILE__) . '/source.jpg';
    $imageText = "Thanks for using our image text PHP library!";
    $image = new \NMC\ImageWithText\ImageWithText($imageSource, $imageText);

    // Image styles
    $image->textAlign = 'center';
    $image->textColor = '000000';
    $image->textFont = dirname(__FILE__) . '/Ubuntu-Medium.ttf';
    $image->textLineHeight = 36;
    $image->textSize = 24;

    // Image offset
    $image->startX = 40;
    $image->startY = 40;

    // Add available lines with number of characters for each line
    $image->addLine(25);
    $image->addLine(30);
    $image->addLine(23);

    // Render image
    $imageDestination = dirname(__FILE__) . '/destination.jpg';
    $image->render($imageDestination);

## How to Contribute

* Fork the repo on GitHub and send a pull request
* Find a list of TODOs on the GitHub issue tracker

We have not written any unit tests just yet, but we hope to do that soon.

## Author

[Josh Lockhart](http://www.newmediacampaigns.com/about/team/josh-lockhart)

## Copyright

(c) 2013 New Media Campaigns

## License

MIT
