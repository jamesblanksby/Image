<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Image\Image;

class ImageCropTest extends TestCase
{
    private $output_directory = __DIR__ . '/output';

    private $image = [
        '100x100_top-left' => __DIR__ . '/lib/100x100_top-left.png',
        '100x100_top-right' => __DIR__ . '/lib/100x100_top-right.png',
        '100x100_bottom-right' => __DIR__ . '/lib/100x100_bottom-right.png',
        '100x100_bottom-left' => __DIR__ . '/lib/100x100_bottom-left.png'
    ];

    private $rgba = ['red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0];

    public function __construct()
    {
        array_map('unlink', glob($this->output_directory . '/*'));

        @chmod($this->output_directory, 0777);
    }

    public function test_crop_top_left()
    {
        $file_path = $this->image['100x100_top-left'];
        $output_path = $this->output_directory . '/100x100_top-left-' . __FUNCTION__ . '.png';

        $img = new Image($file_path);
        $img->crop(10, 10, 100, 100);
        $img->save($output_path);

        $rgba = $this->_image_color_at($output_path, [0, 0]);

        $this->assertEquals($this->rgba, $rgba);
        $this->assertEquals([90, 90], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_crop_top_right()
    {
        $file_path = $this->image['100x100_top-right'];
        $output_path = $this->output_directory . '/100x100_top-right-' . __FUNCTION__ . '.png';

        $img = new Image($file_path);
        $img->crop(0, 10, 90, 100);
        $img->save($output_path);

        $rgba = $this->_image_color_at($output_path, [0, 90]);

        $this->assertEquals($this->rgba, $rgba);
        $this->assertEquals([90, 90], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_crop_bottom_right()
    {
        $file_path = $this->image['100x100_bottom-right'];
        $output_path = $this->output_directory . '/100x100_bottom-right-' . __FUNCTION__ . '.png';

        $img = new Image($file_path);
        $img->crop(0, 0, 90, 90);
        $img->save($output_path);

        $rgba = $this->_image_color_at($output_path, [90, 90]);

        $this->assertEquals($this->rgba, $rgba);
        $this->assertEquals([90, 90], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_crop_bottom_left()
    {
        $file_path = $this->image['100x100_bottom-left'];
        $output_path = $this->output_directory . '/100x100_bottom-left-' . __FUNCTION__ . '.png';

        $img = new Image($file_path);
        $img->crop(10, 0, 100, 90);
        $img->save($output_path);

        $rgba = $this->_image_color_at($output_path, [90, 0]);

        $this->assertEquals($this->rgba, $rgba);
        $this->assertEquals([90, 90], array_slice(getimagesize($output_path), 0, 2));
    }

    private function _image_color_at($path, $coord)
    {
        $resource = imagecreatefrompng($path);

        $rgb = imagecolorat($resource, $coord[0], $coord[1]);
        $rgba = imagecolorsforindex($resource, $rgb);

        return $rgba;
    }
}
