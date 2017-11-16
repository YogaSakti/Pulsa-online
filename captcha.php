<?php

/**
 * @package Script Pulsa Online
 * @version 1
 * @author Engky Datz
 * @link http://okepulsa.id
 * @link http://facebook.com/Engky09
 * @link http://okepulsa.id
 * @link https://www.bukalapak.com/engky09
 * @copyright 2015 -2016
 */

session_name('sess');
session_start();
function hexrgb($hexstr)
{
    $int = hexdec($hexstr);

    return array(
        "red" => 0xff & ($int >> 0x10),
        "green" => 0xff & ($int >> 0x8),
        "blue" => 0xff & $int);
}

$image_width = 110;
$image_height = 40;
$characters_on_image = 5;
$font = 'assets/fonts/monofont.ttf';

$possible_letters = '23456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
$random_dots = 30;
$random_lines = 20;
$captcha_text_color = "0x142864";
$captcha_noice_color = "0x142864";

$code = '';

$i = 0;
while ($i < $characters_on_image)
{
    $code .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
    $i++;
}

$font_size = $image_height * .75;
$image = @imagecreate($image_width, $image_height);

imagealphablending($image, false);
$transparency = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparency);
imagesavealpha($image, true);

$arr_text_color = hexrgb($captcha_text_color);
$text_color = imagecolorallocate($image, $arr_text_color['red'], $arr_text_color['green'],
    $arr_text_color['blue']);

$arr_noice_color = hexrgb($captcha_noice_color);
$image_noise_color = imagecolorallocate($image, $arr_noice_color['red'], $arr_noice_color['green'],
    $arr_noice_color['blue']);


for ($i = 0; $i < $random_dots; $i++)
{
    imagefilledellipse($image, mt_rand(0, $image_width), mt_rand(0, $image_height),
        2, 3, $image_noise_color);
}

for ($i = 0; $i < $random_lines; $i++)
{
    imageline($image, mt_rand(0, $image_width), mt_rand(0, $image_height), mt_rand(0,
        $image_width), mt_rand(0, $image_height), $image_noise_color);
}

$textbox = imagettfbbox($font_size, 0, $font, $code);
$x = ($image_width - $textbox[4]) / 2;
$y = ($image_height - $textbox[5]) / 2;
imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $code);
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
$_SESSION['code'] = $code;
exit();
