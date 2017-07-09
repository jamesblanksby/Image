# Image

> A compact, single file PHP image manipulation library

## Requirements
- PHP >= 7
- GD >= 2.0.28

## Installation
Using [Composer](https://getcomposer.org/):

```sh
$ composer require jamesblanksby/image
```

```php
<?php

// load the image class
require_once __DIR__ '/vendor/autoload.php';

// import namespace
use Image\Image;
```

Or without, download [Image.php](https://raw.githubusercontent.com/jamesblanksby/Image/master/src/Image/Image.php) from the repo and save it in your project directory.

```php
<?php

require_once 'path/to/Image.php';

// import namespace
use Image\Image;
```

## Simple example
```php
<?php

// start by specifying a source image
$img = new Image('lib/demo.jpg');

// resize image to 1000 wide
// the height value has been omitted and `preserve_ratio` is set to true by default so the height will be calculated using the image's aspect ratio
$img->resize(1000);

// crop image from the top left to 1000x750
$img->crop(0, 0, 1000, 750);

// save image
// if no save path is specified the original image will be overwritten
$img->save('lib/final.jpg');
```

## API

> `Image::resize(int $width, int $height)`

Resizes an image and stores the resultant image as the master resource.

- `$width`: Width to resize image to
- `$height`: Height to resize image to

<hr>

> `Image::crop(int $start_x, int $start_y, int $end_x, int $end_y)`

Crops a portion of the image.

- `$start_x`: X coordinate to start cropping from
- `$start_y`: Y coordinate to start cropping from
- `$end_x`: X coordinate where to end the cropping
- `$end_y`: Y coordinate where to end the cropping

<hr>

> `Image::save([string $target_path])`

Saves modified image.

- `$target_path`: Target save path - if not set original image will be overwritten
