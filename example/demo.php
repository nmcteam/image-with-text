<?php
require '../vendor/autoload.php';

// Create image
$image = new \NMC\ImageWithText\Image(dirname(__FILE__) . '/source.jpg');

// Add styled text to image
$text1 = new \NMC\ImageWithText\Text('Thanks for using our image text PHP library!', 3, 25);
$text1->align = 'left';
$text1->color = 'FFFFFF';
$text1->font = dirname(__FILE__) . '/Ubuntu-Medium.ttf';
$text1->lineHeight = 36;
$text1->size = 24;
$text1->startX = 40;
$text1->startY = 40;
$image->addText($text1);

// Add another styled text to image
$text2 = new \NMC\ImageWithText\Text('No, really, thanks!', 1, 30);
$text2->align = 'left';
$text2->color = '000000';
$text2->font = dirname(__FILE__) . '/Ubuntu-Medium.ttf';
$text2->lineHeight = 20;
$text2->size = 14;
$text2->startX = 40;
$text2->startY = 140;
$image->addText($text2);

// Render image
$image->render(dirname(__FILE__) . '/destination.jpg');
