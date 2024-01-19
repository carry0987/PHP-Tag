<?php
require dirname(__DIR__).'/vendor/autoload.php';

use carry0987\Tag\Tag;

$tag = new Tag();

echo '<pre>';

// Set string tags
$tag->setString('music,art,travel');

// Get string tags
$string = $tag->getString();
echo 'String Tags: ' . $string . PHP_EOL;

// Use getList method to convert string tags to array and remove empty values
$arrayFromString = $tag->getList();
echo 'Array from String Tags: ';
print_r($arrayFromString);

// Clear special characters in tag name
$clearTagName = $tag->clearTagName('  Example Tag Name 2023  ');
echo 'Cleared Tag Name: ' . $clearTagName . PHP_EOL;

// Let's say we have an array of tag IDs
$tagsArray = [
    ['id' => 1, 'name' => 'music'],
    ['id' => 2, 'name' => 'art'],
    ['id' => 3, 'name' => 'travel']
];

// Merge tag IDs to a string
$mergedTagIds = $tag->mergeTagID($tagsArray, 'id');
echo 'Merged Tag IDs: ' . $mergedTagIds . PHP_EOL;

// Split tag IDs to array based on string
$slicedTagIds = $tag->sliceTagID('1,2,3,,');
echo 'Sliced Tag IDs: ';
print_r($slicedTagIds);

// Check if specific tag ID exists in string
$doesExist = $tag->checkTagExist('1,2,3', 2);
echo 'Does tag ID 2 exist in the list? ' . ($doesExist ? 'Yes' : 'No') . PHP_EOL;

// Remove a tag ID from string
$removedTag = $tag->removeTag('1,2,3', 2);
echo 'Tags after removing ID 2: ' . $removedTag . PHP_EOL;

echo '</pre>';
