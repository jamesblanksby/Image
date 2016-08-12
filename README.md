# Image

#### A compact, single file PHP image manipulation library

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

	// import it's namespace
	use Image\Image;

	// create new instance of the class
	$img = new Image();
```

Or without, download [Image.php](https://raw.githubusercontent.com/jamesblanksby/Image/master/src/Image/Image.php) and [ImageException.php](https://raw.githubusercontent.com/jamesblanksby/Image/master/src/Image/ImageException.php) from the repo and save it in your project directory.

```php
<?php

	require_once 'path/to/Image.php';

	// import it's namespace
	use Image\Image;

	// create new instance of the class
	$img = new Image();
```

## How to use

```php
<?php

	// start by specifying a source image
	$img->make('lib/demo.jpg');

	// additional properties that can be set
	// file permission level to be used when creating new images
	$img->chmod_value = 0755;
	// if false, image with both height and width smaller than the specified values will not be resized
	$img->enlarge_smaller_images = true;
	// a larger value indicated a better quality image but quality will drastically increase file size
	$img->jpg_quality = 85;
	// flag to specify if image should be sharpened after manipulation
	// this should only be used when creating smaller images such as thumbnails
	$img->sharpen_images = false;
	// flag to specify whether or not an image's aspect ratio shout be kept
	$img->preserve_ratio = true;

	// resize image to 1000 wide
	// the height value has been omitted and `preserve_ratio` is set to true the height will be calculated using the image's aspect ratio
	$img->resize(1000);

	// crop image from the top left to 1000x750
	$img->crop(0, 0, 1000, 750);

	// save image
	// if no save path is specified the original image will be overwritten
	$img->save('lib/final.jpg');
```