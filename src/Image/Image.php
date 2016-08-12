<?php

namespace Image;

class Image
{
    /**
     * File style permission level to be used when creating new images.
     *
     * Default is 0775
     * 
     * @var int
     */
    public $chmod_value = 0755;

    /**
     * If false, image with both height and width smaller than the specified values 
     * will not be resized.
     *
     * Only available when used in conjunction with the resize() method.
     *
     * Default is true
     * 
     * @var bool
     */
    public $enlarge_smaller_images = true;

    /**
     * Compression level of saved JPG images.
     * 
     * A larger value indicated a better quality image but quality will drastically
     * increase file size.
     *
     * Value may range from 0 - 100.
     * 
     * Default is 85.
     * 
     * @var int
     */
    public $jpg_quality = 85;

    /**
     * Flag to specify if image should be sharpened after manipulation.
     *
     * Should only be used when creating smaller images such as thumbnails.
     *
     * Default is false
     * 
     * @var bool
     */
    public $sharpen_images = false;

    /**
     * Flag to specify whether or not an image's aspect ratio shout be kept.
     *
     * Default is true.
     * 
     * @var bool
     */
    public $preserve_ratio = true;

    /**
     * Path to image file.
     * 
     * @var string
     */
    public $source_path;

    /**
     * Path, including file name, to source image's intended save directory.
     * 
     * @var string
     */
    public $target_path;

    /**
     * The master image resource.
     *
     * @var resource
     */
    public $image;

    /**
     * Sets the source path and validates the file.
     * 
     * @param string $path Source image path.
     */
    public function make($path)
    {
        $this->source_path = $path;

        $this->_validate_file();
    }

    /**
     * Resizes an image and stores the resultant image as the master resource.
     * 
     * @param int $width  Width to resize image to.
     * @param int $height Height to resize image to.
     * 
     * @return object Returns the current instance of the class.
     */
    public function resize($width = 0, $height = 0)
    {
        if ($this->_create_from_source()) {

            // if either height or width are to be resized automatically
            // set a flag to override $preserve_ratio, even if it is set to false
            if ($width == 0 || $height == 0) {
                $override_preserve_ratio = true;
            }

            // if aspect ratio must be preserved
            if ($this->preserve_ratio || isset($override_preserve_ratio)) {

                // if only width value is provided
                if ($width > 0 && $height == 0) {

                    // get original image's aspect ratio
                    $aspect_ratio = $this->source_height / $this->source_width;

                    // set target image's width to provided width
                    $target_width = $width;

                    // calculate target image's height while preserving aspect ratio
                    $target_height = round($width * $aspect_ratio);
                }
                // if only height value is provided
                elseif ($width == 0 && $height > 0) {

                    // get original image's aspect ratio
                    $aspect_ratio = $this->source_width / $this->source_height;

                    // set target image's height to provided height
                    $target_height = $height;

                    // calculate target image's width while preserving aspect ratio
                    $target_width = round($height * $aspect_ratio);
                }
                // if both width & height values are provided
                elseif ($width > 0 && $height > 0) {

                    // calculate proportional width & height
                    $proportional_width = $this->source_width / $width;
                    $proportional_height = $this->source_height / $height;

                    $aspect_ratio =
                        $proportional_height < $proportional_width ?
                        $proportional_height :
                        $proportional_width;

                    // calculate target image's width while preserving aspect ratio
                    $target_width = round($this->source_width / $aspect_ratio);

                    // calculate target image's height while preserving aspect ratio
                    $target_height = round($this->source_height / $aspect_ratio);
                }
                // for any other outcome
                else {

                    // create an exact replica of the source image
                    $target_width = $this->source_width;
                    $target_height = $this->source_height;
                }

            // if aspect ratio does not need to be preserved
            } else {

                // calculate target image's width
                $target_width = ($width > 0 ? $width : $this->source_width);

                // calculate target image's height
                $target_height = ($height > 0 ? $height : $this->source_height);
            }

            if (

                // all images are to be resized
                $this->enlarge_smaller_images ||

                // smaller images than the provided width / height are to be skipped BUT
                // current image has at least one size larger than the required width / height
                (
                    $width > 0 && $height > 0 ?

                    ($this->source_width > $width || $this->source_height > $height) :
                    ($this->source_width > $target_width || $this->source_height > $target_height)

                )

            ) {
                if (

                    // aspect ratio must be preserved AND 
                    ($this->preserve_ratio || isset($override_preserve_ratio)) &&

                    // both width & height values are provided
                    ($width > 0 && $height > 0)

                ) {
                    // prepare target image
                    $this->target_image = $this->_prepare_image($target_width, $target_height);

                    imagecopyresampled(

                        $this->target_image,
                        $this->source_image,
                        0,
                        0,
                        0,
                        0,
                        $target_width,
                        $target_height,
                        $this->source_width,
                        $this->source_height

                    );

                    // crop to center of image
                    return $this->crop(

                        floor(($target_width - $width) / 2),
                        floor(($target_height - $height) / 2),
                        floor(($target_width - $width) / 2) + $width,
                        floor(($target_height - $height) / 2) + $height,
                        $this->target_image

                    );
                }
                // aspect ratio does not need to be preserved
                else {

                    // prepare target image
                    $this->target_image = $this->_prepare_image(
                        ($width > 0 && $height > 0 ? $width : $target_width),
                        ($width > 0 && $height > 0 ? $height : $target_height)
                    );

                    imagecopyresampled(

                        $this->target_image,
                        $this->source_image,
                        ($width > 0 && $height > 0 ? ($width - $target_width) / 2 : 0),
                        ($width > 0 && $height > 0 ? ($height - $target_height) / 2 : 0),
                        0,
                        0,
                        $target_width,
                        $target_height,
                        $this->source_width,
                        $this->source_height

                    );
                }
            }
        }

        // set master image
        $this->image = $this->target_image;

        return $this;
    }

    public function crop($start_x, $start_y, $end_x, $end_y, $target_image = null)
    {

        // target element exists and method is being called internally
        if ($target_image !== null && is_resource($target_image)) {
            $this->source_image = $target_image;

            $result = true;
        }
        // method is called naturally
        else {
            // try to create image resource from source path
            $result = $this->_create_from_source();
        }

        // the resource was created successfully
        if ($result !== false) {
            $this->target_image = $this->_prepare_image($end_x - $start_x, $end_y - $start_y, -1);

            // crop the image
            imagecopyresampled(

                $this->target_image,
                $this->source_image,
                0,
                0,
                $start_x,
                $start_y,
                $end_x - $start_x,
                $end_y - $start_y,
                $end_x - $start_x,
                $end_y - $start_y

            );
        }

        // set master image
        $this->image = $this->target_image;

        return $this;
    }

    public function save($target_path = null)
    {

        // set target path
        $this->target_path = $target_path;

        // if no target path is passed to method
        if ($this->target_path == null) {
            // set target path to original source path
            $this->target_path = $this->source_path;
        }

        // sharpen image if required
        $this->_sharpen_image($this->target_image);

        // save according to image extension
        switch ($this->target_type) {

            // is GIF
            case 'gif' :

                // save as gif
                imagegif($this->target_image, $this->target_path);

                break;

            // is JPG
            case 'jpg' :
            case 'jpeg' :

                // save as jpg
                imagejpeg($this->target_image, $this->target_path, $this->jpg_quality);

                break;

            case 'png' :

                // save full alpha channel
                imagesavealpha($this->target_image, true);

                // save as png
                imagepng($this->target_image, $this->target_path);

                break;
        }

        chmod($this->target_path, intval($this->chmod_value, 8));
    }

    /**
     * Creates an empty image with a specified width, height and an optional
     * background color.
     *  
     * @param int    $width            Width of new image.
     * @param int    $height           Height of new image.
     * @param string $background_color (Optional) Six digit hexadecimal color value.
     * 
     * @return resource True color image identifier.
     */
    private function _prepare_image($width, $height, $background_color = '#FFFFFF')
    {
        // create target image
        $target_image = imagecreatetruecolor($width, $height);

        // is transparent PNG
        if ($target_type == 'png' && $background_color == -1) {

            // disable blending - <http://php.net/function.imagealphablending>
            imagealphablending($target_image, false);

            // allocate a transparent color
            $transparent_color = imagecolorallocatealpha($this->target_image, 0, 0, 0, 127);

            // fill image with the transparent color
            imagefill($target_image, 0, 0, $transparent_color);

            // save full alpha channel
            imagesavealpha($target_image, true);
        }
        // is transparent GIF
        elseif ($target_type == 'gif' && $background_color == -1 && $this->source_transparent_color_index >= 0) {

            // allocate the source image's transparent color also to the new image resource
            $transparent_color = imagecolorallocate(
                $target_image,
                $this->source_transparent_color['red'],
                $this->source_transparent_color['green'],
                $this->source_transparent_color['blue']
            );

            // fill the background of the new image with transparent color
            imagefill($target_image, 0, 0, $transparent_color);

            // every pixel with the same RGB as the transparent color will be transparent
            imagecolortransparent($target_image, $transparent_color);
        }
        // for all other image types 
        else {
            // set background color to white if it does not exist
            if ($background_color == -1) {
                $background_color = '#FFFFFF';
            }

            // convert hex color to rgb
            $background_color = $this->_hex2rgb($background_color);

            // prepare the background color
            $background_color = imagecolorallocate($target_image, $background_color['r'], $background_color['g'], $background_color['b']);

            // fill target image with specified background color
            imagefill($target_image, 0, 0, $background_color);
        }

        return $target_image;
    }

    // Code taken from the comments at {@link http://docs.php.net/imageconvolution}.
    private function _sharpen_image($image)
    {
        // if the "sharpen_images" is set to true and we're running an appropriate version of PHP
        // (the "imageconvolution" is available only for PHP 5.1.0+)
        if ($this->sharpen_images && version_compare(PHP_VERSION, '5.1.0') >= 0) {
            // the convolution matrix as an array of three arrays of three floats
            $matrix = [
                [-1.2, -1, -1.2],
                [-1, 20, -1],
                [-1.2, -1, -1.2],
            ];

            // the divisor of the matrix
            $divisor = array_sum(array_map('array_sum', $matrix));

            // color offset
            $offset = 0;

            // sharpen image
            imageconvolution($image, $matrix, $divisor, $offset);
        }

        return $image;
    }

    private function _create_from_source()
    {
        // if image is already set
        if (isset($this->image)) {
            $this->source_image = $this->image;

            $this->source_width = imagesx($this->source_image);
            $this->source_height = imagesy($this->source_image);

            return true;
        }

        $this->target_type = $this->get_extension($this->source_path);

        switch ($this->source_type) {

            // is GIF
            case IMAGETYPE_GIF :

                // create a new image from source path
                $image = imagecreatefromgif($this->source_path);

                // get index of transparent color
                if (($this->source_transparent_color_index = imagecolortransparent($image)) >= 0) {

                    // get the transparent color's RGB values
                    $this->source_transparent_color = @imagecolorsforindex($image, $this->source_transparent_color_index);
                }

                break;

            // is JPG
            case IMAGETYPE_JPEG :

                // create a new image from source path
                $image = imagecreatefromjpeg($this->source_path);

                break;

            // is PNG
            case IMAGETYPE_PNG :

                // create a new image from source path
                $image = imagecreatefrompng($this->source_path);

                // disable blending - <http://php.net/function.imagealphablending>
                imagealphablending($image, false);

                break;

            // unsupported type
            default :

                throw new TinyImageException('Image source is an unsupported media type');

                return false;

                break;
        }

        $this->source_image = $image;

        return true;
    }

    /**
     * Converts a hexadecimal representation of a color (i.e. #123456 or #AAA) to a 
     * RGB representation.
     * 
     * @param string $color Hexadecimal representation of a color
     * 
     * @return array Associative array with the values of (R)ed, (G)reen & (B)lue
     */
    private function _hex2rgb($color)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = [$r, $g, $b];

        return $rgb;
    }

    private function _validate_file()
    {
        // if source file does not exist
        if (!file_exists($this->source_path)) {
            throw new TinyImageException('Image source path: "'.$this->source_path.'" does not exist');
        }
        // if source file is not readable
        elseif (!is_readable($this->source_path)) {
            throw new TinyImageException('Image source path: "'.$this->source_path.'" is not readable');
        }
        // if target file is the same as source file and source file is not writable
        elseif ($this->source_path == $this->target_path && !is_writable($this->source_path)) {
            throw new TinyImageException('Image target path: "'.$this->source_path.'" must be writable');
        }
        // try to get source file width, height and type
        // check if it finds an unsupported file type
        elseif (!list($this->source_width, $this->source_height, $this->source_type) = @getimagesize($this->source_path)) {
            throw new TinyImageException('Source image is an unsupported file type');
        }
    }
}
