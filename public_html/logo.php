<?php
// Let's make a logo!

$textstring = trim(str_replace('logo/', '', $_GET['t']));
if(!isset($_GET['f'])){
    $textstring = strtolower($textstring);
    $textstring = preg_replace("/[^a-z]/", '', $textstring);
}
$filename = 'nfcore-'.preg_replace("/[^a-z]/", '', $textstring).'_logo.png';

if(strlen($textstring) == 0){
    header('HTTP/1.1 404 Not Found');
    include('404.php');
    die();
}

// Load the base image
$template_fn = "assets/img/logo/nf-core-repologo-base.png";
list($width, $height) = getimagesize($template_fn);
$image = imagecreatefrompng($template_fn);

// Create some colors
$black = imagecolorallocate($image, 0, 0, 0);
$font_size = 300;
$font_path = "../includes/Maven_Pro/MavenPro-Bold.ttf";

// Put text into image
imagettftext(
    $image,      // image
    $font_size,  // size
    0,           // angle
    110,         // x
    850,         // y
    $black,      // colour
    $font_path,  // font
    $textstring  // text
);

// Crop off the excessive whitespace
$text_bbox = imagettfbbox($font_size, 0, $font_path, $textstring);
$text_width = abs($text_bbox[4] - $text_bbox[0]) + 250;
$min_width = 2300;
$width = max($text_width, $min_width);
$image = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $height]);

// If a width is given, scale the image
$new_width = false;
if(isset($_GET['w'])){
    $new_width = $_GET['w'];
} else if(isset($_GET['s'])){
    $new_width = 400;
}
if(is_numeric($new_width)){
    #$image = imagescale($image, 400, -1, IMG_NEAREST_NEIGHBOUR);
    // Get new dimensions
    $resize_factor = $new_width / $width;
    $new_height = $height * $resize_factor;

    // Create new image, with transparency
    $image_p = imagecreatetruecolor($new_width, $new_height);
    imagesetinterpolation($image_p,IMG_BICUBIC);
    imagealphablending($image_p, false);
    imagesavealpha($image_p,true);
    $transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);

    // Resample
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Overwrite full size image with resampled
    $image = $image_p;
}

// Keep PNG transparency
imageAlphaBlending($image, true);
imageSaveAlpha($image, true);

// Make and destroy image
header("Content-type: image/png");
header('Content-Disposition: filename="'.$filename.'"');
imagepng($image);
imagedestroy($image);
imagedestroy($image);