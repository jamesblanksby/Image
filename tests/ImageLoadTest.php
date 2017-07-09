<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Image\Image;

class ImageLoadTest extends TestCase
{
    private $image_supported = [
        'jpg' => __DIR__ . '/lib/1x1.jpg',
        'png' => __DIR__ . '/lib/1x1.png'
    ];

    private $image_unsupported = __DIR__ . '/lib/1x1.pdf';

    private $image_nonexistent = __DIR__ . '/path/to/nonexistent/file';

    public function test_load_supported_jpg_file()
    {
        $file_path = $this->image_supported['jpg'];

        $img = new Image($file_path);

        $this->assertEquals(IMAGETYPE_JPEG, $img->source_type);
    }

    public function test_load_supported_png_file()
    {
        $file_path = $this->image_supported['png'];

        $img = new Image($file_path);

        $this->assertEquals(IMAGETYPE_PNG, $img->source_type);
    }

    public function test_load_unsupported_file()
    {
        $file_path = $this->image_unsupported;

        try {
            $img = new Image($file_path);
        } catch (\Exception $e) {
            $this->assertEquals('Source image is an unsupported file type', $e->getMessage());

            return;
        }
        
        $this->fail();
    }

    public function test_load_nonexistent_file()
    {
        $file_path = $this->image_nonexistent;

        try {
            $img = new Image($file_path);
        } catch (\Exception $e) {
            $this->assertEquals('Image source path: "'.$file_path.'" does not exist', $e->getMessage());

            return;
        }
        
        $this->fail();
    }
}
