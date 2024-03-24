<?php
namespace carry0987\Tag;

use carry0987\Tag\Exceptions\TagException;

class Tag
{
    const TAG_STRING = 'str';
    const TAG_ARRAY = 'arr';
    const TAG_CLASSIFIED = 'classified';
    const TAG_UNCLASSIFIED = 'unclassified';

    protected $tag = [
        self::TAG_STRING => null,
        self::TAG_ARRAY => [],
        self::TAG_CLASSIFIED => null,
        self::TAG_UNCLASSIFIED => null
    ];
    protected $validTagPattern = '/^(?!\d+$)[\p{Han}a-zA-Z0-9\:\-,_\ ]+$/u';
    protected $validClassifyPattern = '/([a-z]+)\:([\p{Han}a-zA-Z0-9_\ ]+)/u';

    public function __construct(string $value = '')
    {
        $this->tag[self::TAG_STRING] = $value;
    }

    /**
     * Set regular expression pattern for validating tags.
     *
     * @param string|null $pattern Regular expression pattern
     * 
     * @return $this Returns the current instance of the Tag class.
     */
    public function setValidTagFormat(?string $pattern): self
    {
        $this->validTagPattern = $pattern;

        return $this;
    }

    /**
     * Set regular expression pattern for validating classified tags.
     *
     * @param string|null $pattern Regular expression pattern
     * 
     * @return $this Returns the current instance of the Tag class.
     */
    public function setValidClassifyPattern(?string $pattern): self
    {
        $this->validClassifyPattern = $pattern;

        return $this;
    }

    public function setString(string $string): self
    {
        $this->tag[self::TAG_STRING] = $string;

        return $this;
    }

    public function setArray(array $array): self
    {
        $this->tag[self::TAG_ARRAY] = array_unique($array);

        return $this;
    }

    public function getString(): ?string
    {
        return $this->tag[self::TAG_STRING];
    }

    public function getArray(): array
    {
        return $this->tag[self::TAG_ARRAY] ?? [];
    }

    public function getClassified(): array
    {
        if (empty($this->tag[self::TAG_CLASSIFIED])) {
            $this->classifyTagGroup();
        }

        return empty($this->tag[self::TAG_CLASSIFIED]) ? [] : $this->tag[self::TAG_CLASSIFIED];
    }

    public function getUnclassified(): array
    {
        if (empty($this->tag[self::TAG_UNCLASSIFIED])) {
            $this->classifyTagGroup();
        }

        return empty($this->tag[self::TAG_UNCLASSIFIED]) ? [] : $this->tag[self::TAG_UNCLASSIFIED];
    }

    public function getValidTagPattern(): string
    {
        return $this->validTagPattern;
    }

    public function getValidClassifyPattern(): string
    {
        return $this->validClassifyPattern;
    }

    public function isValidTagString(): bool
    {
        return preg_match($this->validTagPattern, $this->tag[self::TAG_STRING]) === 1;
    }

    /**
     * Classify the tag array into a grouped structure based on the predefined pattern.
     * Each tag containing a colon ':' is parsed into a key-value pair where the key is the tag group.
     * The remainder after the colon is added to the group, optionally normalized if specified.
     * Tags without a colon are considered unclassified and are added separately,
     * also with optional normalization. The original tag array is not modified.
     *
     * @param bool $normalize Whether to normalize the tag names. When set to true,
     *                        normalization replaces spaces and some other characters
     *                        with underscores and converts to lowercase.
     * 
     * @return self Returns the current instance of the Tag class, allowing for method chaining.
     */
    public function classifyTagGroup(bool $normalize = false): self
    {
        // Check if the tag array is already set, if not, then populate it from the string.
        if (!isset($this->tag[self::TAG_ARRAY]) || empty($this->tag[self::TAG_ARRAY])) {
            $this->getList();
        }

        $this->tag[self::TAG_CLASSIFIED] = $this->tag[self::TAG_UNCLASSIFIED] = [];
        foreach ($this->tag[self::TAG_ARRAY] as $value) {
            if (strpos($value, ':') !== false) {
                preg_match($this->validClassifyPattern, $value, $matches);
                if (isset($matches[2])) {
                    $groupValue = $normalize ? self::clearTagName($matches[2]) : $matches[2];
                    $this->tag[self::TAG_CLASSIFIED][$matches[1]][] = $groupValue;
                    continue;
                }
            }
            $uncatValue = $normalize ? self::clearTagName($value) : $value;
            $this->tag[self::TAG_UNCLASSIFIED][] = $uncatValue;
        }

        return $this;
    }

    /**
     * Explode the string tag into an array, filter out any empty strings.
     * This method helps to prepare the tag string for further classification or manipulation.
     * 
     * @param string $separator Separator used in the string.
     *
     * @return array Array of non-empty tags extracted from the string.
     */
    public function getList(string $separator = ','): array
    {
        if (!isset($this->tag[self::TAG_STRING])) return [];
        $this->tag[self::TAG_ARRAY] = array_filter(explode($separator, $this->tag[self::TAG_STRING]), 'strlen');

        return $this->tag[self::TAG_ARRAY];
    }

    /**
     * Normalize each tag in the tag array by replacing spaces or colons followed by a slash with a colon
     * and applying the normalization rules (lowercase and underscores for spaces).
     * This is typically used in preparing tags for display or storage.
     *
     * @return array Array of normalized tags.
     */
    public function getNormalizedList(): array
    {
        // Check if the tag array is already set, if not, then populate it from the string.
        if (!isset($this->tag[self::TAG_ARRAY]) || empty($this->tag[self::TAG_ARRAY])) {
            $this->getList();
        }

        // Apply normalization to the array values and get unique entries.
        $array = $this->tag[self::TAG_ARRAY];
        $array = array_unique(array_map(function($value) {
            return self::clearTagName($value);
        }, $array));

        return $array;
    }

    /**
     * Merge an array of tags into a comma-separated string. If a column name is provided, only the values
     * from that column are merged. Validates the elements to ensure they are scalar and not boolean false.
     *
     * @param array $arr Array of tags or multi-dimensional array from which to extract the tags.
     * @param string|null $column Optional name of the column whose values are to be merged.
     * @param string $separator Separator used in the string.
     * 
     * @return string Resulting comma-separated string of tags.
     * 
     * @throws TagException If an element is a boolean false or if the specified column is missing or non-scalar.
     */
    public static function mergeTag(array $arr, string $column = null, string $separator = ','): string
    {
        if ($column === null) {
            $arr = array_filter($arr, 'is_scalar');
            foreach ($arr as $value) {
                if ($value === false) {
                    throw new TagException('Boolean false cannot be converted to a string');
                }
            }
            return implode($separator, $arr);
        }
        $values = [];
        foreach ($arr as $item) {
            if (!isset($item[$column])) {
                throw new TagException("Column '{$column}' missing in array element.");
            }
            if (!is_scalar($item[$column])) {
                throw new TagException("Column '{$column}' must be a scalar value.");
            }
            $values[] = $item[$column];
        }

        return implode($separator, $values);
    }

    /**
     * Merge an array of tag IDs into a comma-separated string. If a column name is provided, only the values
     * from that column are merged. Validates the elements to ensure they are positive integers.
     *
     * @param array $arr Array of tag IDs or multi-dimensional array from which to extract the tag IDs.
     * @param string|null $column Optional name of the column whose values are to be merged.
     * @param string $separator Separator used in the string.
     * 
     * @return string Resulting comma-separated string of tag IDs.
     * 
     * @throws TagException If an element is not a positive integer or if the specified column is missing or non-integer.
     */
    public static function mergeTagID(array $arr, string $column = null, string $separator = ','): string
    {
        return self::mergeTag($arr, $column, $separator);
    }

    /**
     * Slice a comma-separated string of tags into an array of strings.
     * Filters out any empty strings.
     *
     * @param string $str Comma-separated string of tags.
     * @param string $separator Separator used in the string.
     * 
     * @return array Array of tags.
     */
    public static function sliceTag(string $str, string $separator = ','): array
    {
        return array_filter(array_map('trim', explode($separator, $str)));
    }

    /**
     * Slice a comma-separated string of tag IDs into an array of integers.
     * Filters out any non-positive integers (e.g. zero or negative).
     *
     * @param string $str Comma-separated string of tag IDs.
     * @param string $separator Separator used in the string.
     * 
     * @return array Array of positive integer tags.
     */
    public static function sliceTagID(string $str, string $separator = ','): array
    {
        return array_filter(array_map('intval', explode($separator, $str)), function($value) {
            return $value > 0;
        });
    }

    /**
     * Check whether a tag exists within a comma-separated string of tags.
     *
     * @param string $str Comma-separated string of tags to search.
     * @param string $tag Tag to check for existence.
     * @param string $separator Separator used in the string.
     * 
     * @return bool True if tag exists, false otherwise.
     */
    public static function checkTagExist(string $str, string $tag, string $separator = ','): bool
    {
        return in_array($tag, self::sliceTag($str, $separator), true);
    }

    /**
     * Check whether a tag ID exists within a comma-separated string of tag IDs.
     *
     * @param string $str Comma-separated string of tag IDs to search.
     * @param int $tag_id Tag ID to check for existence.
     * @param string $separator Separator used in the string.
     * 
     * @return bool True if tag ID exists, false otherwise.
     */
    public static function checkTagIDExist(string $str, int $tag_id, string $separator = ','): bool
    {
        return in_array($tag_id, self::sliceTagID($str, $separator), true);
    }

    /**
     * Add a tag to a comma-separated string of tags and return the updated string.
     *
     * @param string $str Comma-separated string of tags.
     * @param string $tag Tag to add to the string.
     * @param string $separator Separator used in the string.
     * 
     * @return string Updated string with the tag added.
     */
    public static function removeTag(string $str, string $tag, string $separator = ','): string
    {
        $tags = self::sliceTag($str, $separator);
        $key = array_search($tag, $tags, true);
        if ($key !== false) {
            unset($tags[$key]);
        }

        return implode($separator, $tags);
    }

    /**
     * Remove a specific tag ID from a comma-separated string of tag IDs and return the updated string.
     *
     * @param string $str Comma-separated string of tag IDs.
     * @param int $tag_id Tag ID to remove from the string.
     * @param string $separator Separator used in the string.
     * 
     * @return string Updated string with the specific tag ID removed.
     */
    public static function removeTagID(string $str, int $tag_id, string $separator = ','): string
    {
        $tags = self::sliceTagID($str);
        $key = array_search($tag_id, $tags, true);
        if ($key !== false) {
            unset($tags[$key]);
        }

        return implode($separator, $tags);
    }

    /**
     * Add a tag to a comma-separated string of tags if it does not already exist,
     * and return the updated string.
     *
     * @param string $str Comma-separated string of tags.
     * @param string $tag Tag to add to the string.
     * @param string $separator Separator used in the string.
     * 
     * @return string Updated string with the new tag added, if it was not already present.
     */
    public static function addTag(string $str, string $tag, string $separator = ','): string
    {
        $tags = self::sliceTag($str, $separator);
        if (!self::checkTagExist($str, $tag, $separator)) {
            $tags[] = $tag;
        }

        return implode($separator, $tags);
    }

    /**
     * Add a tag ID to a comma-separated string of tag IDs if it does not already exist,
     * and return the updated string.
     *
     * @param string $str Comma-separated string of tag IDs.
     * @param int $tag_id Tag ID to add to the string.
     * @param string $separator Separator used in the string.
     * 
     * @return string Updated string with the new tag ID added, if it was not already present.
     */
    public static function addTagID(string $str, int $tag_id, string $separator = ','): string
    {
        $tags = self::sliceTagID($str, $separator);
        if (!self::checkTagIDExist($str, $tag_id, $separator)) {
            $tags[] = $tag_id;
        }

        return implode($separator, $tags);
    }

    /**
     * Normalizes tag names by replacing sequences of spaces or colons followed by a slash with a colon.
     * It then delegates further normalization, such as converting to lowercase and replacing spaces with
     * underscores, to the normalizeString method. This function is typically used to process tag names
     * before using them for display or storage.
     *
     * @param string $str The tag name to be cleared and normalized.
     * 
     * @return string The normalized tag name.
     */
    public static function clearTagName(string $str): string
    {
        return self::normalizeString(preg_replace('/\s*([\/:])\s*/', ':', $str));
    }

    /**
     * Takes a string and normalizes it by converting it to lowercase, trimming whitespace,
     * and replacing spaces with underscores. Typically used to process tag names.
     *
     * @param string $str String to be cleared and normalized.
     * 
     * @return string Normalized string.
     */
    protected static function normalizeString($str): string
    {
        return preg_replace('/\s+/', '_', strtolower(trim($str)));
    }
}
