<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Image\Image;

class ImageResizeTest extends TestCase
{
    private $output_directory = __DIR__ . '/output';

    private $image = [
        '10x10' => __DIR__ . '/lib/10x10.jpg',
        '100x100' => __DIR__ . '/lib/100x100.jpg'
    ];

    public function __construct()
    {
        array_map('unlink', glob($this->output_directory . '/*'));

        @chmod($this->output_directory, 0777);
    }

    public function test_resize_width()
    {
        $file_path = $this->image['100x100'];
        $output_path = $this->output_directory . '/100x100-' . __FUNCTION__ . '.jpg';

        $img = new Image($file_path);
        $img->preserve_ratio = false;
        $img->resize(50);
        $img->save($output_path);

        $this->assertEquals([50, 100], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_resize_height()
    {
        $file_path = $this->image['100x100'];
        $output_path = $this->output_directory . '/100x100-' . __FUNCTION__ . '.jpg';

        $img = new Image($file_path);
        $img->preserve_ratio = false;
        $img->resize(null, 50);
        $img->save($output_path);

        $this->assertEquals([100, 50], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_resize_width_and_height()
    {
        $file_path = $this->image['100x100'];
        $output_path = $this->output_directory . '/100x100-' . __FUNCTION__ . '.jpg';

        $img = new Image($file_path);
        $img->preserve_ratio = false;
        $img->resize(50, 25);
        $img->save($output_path);

        $this->assertEquals([50, 25], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_resize_enlarge_smaller_images()
    {
        $file_path = $this->image['10x10'];
        $output_path = $this->output_directory . '/10x10-' . __FUNCTION__ . '.jpg';

        $img = new Image($file_path);
        $img->preserve_ratio = true;
        $img->enlarge_smaller_images = true;
        $img->resize(100);
        $img->save($output_path);

        $this->assertEquals([100, 100], array_slice(getimagesize($output_path), 0, 2));
    }

    public function test_resize_preserve_ratio()
    {
        $file_path = $this->image['100x100'];
        $output_path = $this->output_directory . '/100x100-' . __FUNCTION__ . '.jpg';

        $img = new Image($file_path);
        $img->preserve_ratio = true;
        $img->resize(10);
        $img->save($output_path);

        $this->assertEquals([10, 10], array_slice(getimagesize($output_path), 0, 2));
    }
}
