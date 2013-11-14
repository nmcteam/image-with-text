<?php
require '../vendor/autoload.php';

// Create image
$imageSource = dirname(__FILE__) . '/source.jpg';
$imageText = 'Thanks for using our image text PHP library!';
$image = new \NMC\ImageWithText\ImageWithText($imageSource, $imageText);

// Image styles
$image->textAlign = 'center';
$image->textColor = 'FFFFFF';
$image->textFont = dirname(__FILE__) . '/Ubuntu-Medium.ttf';
$image->textLineHeight = 36;
$image->textSize = 24;

// Image offset
$image->startX = 40;
$image->startY = 40;

// Add available lines
$image->addLine(25);
$image->addLine(30);
$image->addLine(23);

// Render image
$imageDestination = dirname(__FILE__) . '/destination.jpg';
$image->render($imageDestination);
