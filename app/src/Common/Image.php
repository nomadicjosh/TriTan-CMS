<?php
namespace TriTan\Common;

use TriTan\Interfaces\ImageInterface;
use TriTan\Interfaces\UtilsInterface;
use TriTan\Container as c;

class Image implements ImageInterface
{
    public $util;

    public function __construct(UtilsInterface $util)
    {
        $this->util = $util;
    }

    /**
    * Resize image function.
    *
    * Example Usage:
    *
    *      $size = getimagesize('static/assets/img/avatar.png');
    *      $this->resize($size[0], $size[1], 80);
    *
    * @since 0.9.9
    * @param int $width Width of the image.
    * @param int $height Height of the image.
    * @param string $target Size of image.
    */
    public function resize($width, $height, $target)
    {
        // takes the larger size of the width and height and applies the formula.
        // Your function is designed to work with any image in any size.
        if ($width > $height) {
            $percentage = ($target / $width);
        } else {
            $percentage = ($target / $height);
        }

        // gets the new value and applies the percentage, then rounds the value
        $new_width = round($width * $percentage);
        $new_height = round($height * $percentage);
        // returns the new sizes in html image tag format...this is so you can plug
        // this function inside an image tag so that it will set the image to the
        // correct size, without putting a whole script into the tag.
        return 'width="' . $new_width . '" height="' . $new_height . '"';
    }

    /**
     * Manipulate image(s) with an expressive API
     *
     * @since 0.9.9
     * @param array $params An array of parameters for image manipulation.
     *      @type string    $image_source      The source of the original image.
     *      @type string    $image_destination The destination of the new image.
     *      @type bool      $image_sepia       Whether to add sepia filter.
     *      @type int       $image_blur        Blue the image.
     *      @type bool      $image_original    Whether to return/save new image to original size.
     *      @type int       $image_width       Width of new image in pixels.
     *      @type int       $image_height      Height of new image in pixels
     *      @type string    $image_format      Whether to show post in search.
     *      @type int       $image_quality     Quality percent. Only applies for jpeg.
     *      @type int       $image_brightness  The post's featured image.
     *      @type int       $image_contrast    Adjusts the contrast of image.
     *      @type int       $image_gamma       Adjusts the gamma of image.
     *      @type string    $image_background  Sets the background of transparent images.
     *      @type string    $image_fit         Fits the image with the given width and height.
     *      @type string    $image_crop        Cropped to the given width and height.
     *      @type array     $image_border      Adds border to image.
     *      @type string    $image_orientation Rotate the image.
     *      @type int       $image_pixelate    Pixelates the image.
     *      @type bool      $image_greyscale   Converts image to greyscale.
     *      @type int       $image_sharpen     Sharpens the image.
     *      @type array     $image_watermark   Adds a watermark to image.
     * @return boolean
     */
    public function manipulate(array $params = [])
    {
        $defaults = [
            'image_source' => null,
            'image_destination' => c::getInstance()->get('site_path') . 'uploads' . DS . 'new_image.png',
            'image_sepia' => (bool) false,
            'image_blur' => 0,
            'image_original' => (bool) false,
            'image_width' => 200,
            'image_height' => 200,
            'image_format' => null,
            'image_quality' => 0,
            'image_brightness' => 0,
            'image_contrast' => 0,
            'image_gamma' => 0,
            'image_background' => null,
            'image_fit' => null,
            'image_crop' => null,
            'image_border' => [],
            'image_orientation' => null,
            'image_pixelate' => 0,
            'image_greyscale' => (bool) false,
            'image_sharpen' => 0,
            'image_watermark' => []
        ];

        $var = $this->util->{'parseArgs'}($params, $defaults);

        if (null === $var['image_source']) {
            return false;
        }

        $image = \Spatie\Image\Image::load($var['image_source']);

        if (false !== $var['image_sepia']) {
            $image->sepia();
        }

        if (null !== $var['image_blur'] && is_numeric($var['image_blur']) && (int) $var['image_blur'] > 0) {
            $image->blur((int) $var['image_blur']);
        }

        if (false === $var['image_original']) {
            if ((int) $var['image_width'] > 0) {
                $image->width((int) $var['image_width']);
            }

            if ((int) $var['image_height'] > 0) {
                $image->height((int) $var['image_height']);
            }
        }

        if ((int) $var['image_quality'] > 0) {
            $image->quality((int) $var['image_quality']);
        }

        if (is_numeric($var['image_brightness']) && (int) $var['image_brightness'] > 0) {
            $image->brightness((int) $var['image_brightness']);
        }

        if (is_numeric($var['image_contrast']) && (int) $var['image_contrast'] > 0) {
            $image->contrast((int) $var['image_contrast']);
        }

        if (is_float($var['image_gamma']) && (float) $var['image_gamma'] > 0) {
            $image->gamma((float) $var['image_gamma']);
        }

        if (null !== $var['image_background']) {
            $image->background($var['image_background']);
        }

        if (null !== $var['image_fit']) {
            $width = $var['image_width'] > 0 ? $var['image_width'] : 200;
            $height = $var['image_height'] > 0 ? $var['image_height'] : 200;
            $image->fit(\Spatie\Image\Manipulations::$var['image_fit'], $width, $height);
        }

        if (null !== $var['image_crop']) {
            $width = $var['image_width'] > 0 ? $var['image_width'] : 200;
            $height = $var['image_height'] > 0 ? $var['image_height'] : 200;
            $image->crop(\Spatie\Image\Manipulations::$var['image_crop'], $width, $height);
        }

        if (!empty(array_filter($var['image_border']))) {
              $image->border(
                  $var['image_border']['width'],
                  $var['image_border']['color'],
                  \Spatie\Image\Manipulations::$var['image_border']['type']
              );
        }

        if (null !== $var['image_orientation']) {
            $image->orientation(\Spatie\Image\Manipulations::$var['image_orientation']);
        }

        if (null !== $var['image_format']) {
            $image->format(\Spatie\Image\Manipulations::$var['image_format']);
        }

        if ((int) $var['image_pixelate'] > 0) {
            $image->pixelate($var['image_pixelate']);
        }

        if (false !== $var['image_greyscale']) {
            $image->greyscale($var['image_greyscale']);
        }

        if ((int) $var['image_sharpen'] > 0) {
            $image->sharpen($var['image_sharpen']);
        }

        if (!empty(array_filter($var['image_watermark']))) {
            $image->watermark()
                ->watermarkOpacity($var['image_watermark']['opacity'])
                ->watermarkPosition(Manipulations::$var['image_watermark']['position'])
                ->watermarkPadding($var['image_watermark']['padding'])
                ->watermarkHeight(
                    $var['image_watermark']['height'],
                    Manipulations::$var['image_watermark']['height_unit']
                )
                ->watermarkWidth(
                    $var['image_watermark']['width'],
                    Manipulations::$var['image_watermark']['width_unit']
                )
                ->watermarkFit(Manipulations::$var['image_watermark']['fit']);
        }

        if (null !== $var['image_destination']) {
            $image->save($var['image_destination']);
        } else {
            $image->save();
        }
    }
}
