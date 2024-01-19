# PHP-Tag
[![Packgist](https://img.shields.io/packagist/v/carry0987/tag.svg?style=flat-square)](https://packagist.org/packages/carry0987/tag)  
PHP Tag Management Library: A Comprehensive Toolkit for Tag Handling

## Introduction
PHP-Tag is a dynamic and robust library designed for developers who need to manage and manipulate tags within their PHP applications. Whether you're working on content management systems, music libraries, photo galleries, or any platform that utilizes tagging, this library provides an extensive suite of functionalities to streamline tag management.

## Features
- **String Tag Conversion**: Convert a comma-separated string of tags into an array and vice versa.
- **Tag Classification**: Group tags based on a custom pattern and distinguish between classified and unclassified tags.
- **Tag ID Management**: Merge, slice, check, and remove tag IDs with ease, supporting both single-dimensional and multi-dimensional arrays.
- **Normalization**: Sanitize and normalize tag names by replacing special characters, converting to lowercase, and trimming excess whitespace.
- **Regular Expression Patterns**: Set and use custom regular expression patterns for validating tag formats and classifications.
- **Exception Handling**: Built-in exception handling for robust and error-free tag manipulation.

## Installation
To install PHP-Tag, run the following command using Composer:

```bash
composer require carry0987/tag
```

## Usage
Below is a quick example of how to use the PHP-Tag library:

```php
require 'vendor/autoload.php';

use carry0987\Tag\Tag;

$tag = new Tag();

// Set and get string tags
$tag->setString('php,library,tag');
echo 'String Tags: ', $tag->getString(), PHP_EOL;

// Convert string tags to array
print_r($tag->getList());

// Classify tags and differentiate into groups
$tag->classifyTagGroup();
print_r($tag->getClassified());
print_r($tag->getUnclassified());

// Normalize a tag name for consistent formatting
echo Tag::clearTagName('  Normalize: This_Tag!  '), PHP_EOL;

// Merge and manipulate tag IDs
$tagsArray = [['id' => 1, 'name' => 'php'], ['id' => 2, 'name' => 'library']];
$mergedTagIds = Tag::mergeTagID($tagsArray, 'id');
echo 'Merged Tag IDs: ', $mergedTagIds, PHP_EOL;
```

For a more thorough understanding of PHP-Tag library functionalities, please refer to the **[example.php](./example/index.php)** file.

## Contribution
Contributions are welcome! Feel free to issue pull requests or submit issues if you'd like to improve the PHP-Tag library or add new features.
