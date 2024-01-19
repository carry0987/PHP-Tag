<?php
require dirname(__DIR__).'/vendor/autoload.php';

use carry0987\Tag\Tag;

$tag = new Tag();

echo '<pre>';
// Set string tags
$tag->setString('human:music,human:art,other:travel,just_dance,ka ka');

// Get string tags
$string = $tag->getString();
echo 'String Tags: ', $string, PHP_EOL;

// Use getList method to convert string tags to array and remove empty values
$arrayFromString = $tag->getNormalizedList();
echo 'Array from String Tags: ';
print_r($arrayFromString);

// Get classified tags
$classifiedTags = $tag->classifyTagGroup()->getClassified();
echo 'Classified Tags: ';
print_r($classifiedTags);

echo '</pre>';
echo '<hr>';

echo '<pre>';
// Clear special characters in tag name
$tag_name = '  Example Tag Name 2023  ';
$clearTagName = Tag::clearTagName($tag_name);
echo 'Original Tag Name: ', $tag_name, PHP_EOL;
echo 'Cleared Tag Name: ', $clearTagName, PHP_EOL;

echo '</pre>';
echo '<hr>';

echo '<pre>';
// Let's say we have an array of tag IDs
$tagsArray = [
    ['id' => 5, 'name' => 'music'],
    ['id' => 7, 'name' => 'art'],
    ['id' => 9, 'name' => 'travel']
];

echo 'Original Tag IDs: ';
print_r($tagsArray);

// Merge tag IDs to a string
$mergedTagIds = Tag::mergeTagID($tagsArray, 'id');
echo 'Merged Tag IDs: ', $mergedTagIds, PHP_EOL;

// Split tag IDs to array based on string
$slicedTagIds = Tag::sliceTagID($mergedTagIds);
echo 'Sliced Tag IDs: ';
print_r($slicedTagIds);

// Check if specific tag ID exists in string
$doesExist = Tag::checkTagExist($mergedTagIds, 7);
echo 'Does tag ID 7 exist in the list? ', ($doesExist ? 'Yes' : 'No'), PHP_EOL;

// Remove a tag ID from string
$removedTag = Tag::removeTag($mergedTagIds, 7);
echo 'Tags after removing ID 2: ', $removedTag, PHP_EOL;

echo '</pre>';
