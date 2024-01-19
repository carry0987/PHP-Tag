<?php
namespace carry0987\Tag;

use carry0987\Tag\Exceptions\TagException;

class Tag
{
    const TAG_STRING = 'str';
    const TAG_ARRAY = 'arr';
    const TAG_CLASSIFIED = 'classified';

    protected $tag = [
        self::TAG_STRING => null,
        self::TAG_ARRAY => [],
        self::TAG_CLASSIFIED => null,
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
     */
    public function setValidTagFormat(?string $pattern): void
    {
        $this->validTagPattern = $pattern;
    }

    /**
     * Set regular expression pattern for validating classified tags.
     *
     * @param string|null $pattern Regular expression pattern
     */
    public function setValidClassifyPattern(?string $pattern): void
    {
        $this->validClassifyPattern = $pattern;
    }

    public function setString(string $string): void
    {
        $this->tag[self::TAG_STRING] = $string;
    }

    public function setArray(array $array): void
    {
        $this->tag[self::TAG_ARRAY] = array_unique($array);
    }

    public function getString(): ?string
    {
        return $this->tag[self::TAG_STRING];
    }

    public function getArray(): ?array
    {
        return $this->tag[self::TAG_ARRAY] ?? null;
    }

    public function getClassified(): ?array
    {
        return $this->tag[self::TAG_CLASSIFIED] ?? null;
    }

    public function getValidTagPattern(): ?string
    {
        return $this->validTagPattern;
    }

    public function getValidClassifyPattern(): ?string
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
     * The remainder after the colon is normalized and added to the group. The original tag array is updated
     * by removing classified tags.
     *
     * @return array Classified tags grouped by their prefix.
     */
    public function classifyTagGroup(): array
    {
        // Check if the tag array is already set, if not, then populate it from the string.
        if (!isset($this->tag[self::TAG_ARRAY]) || empty($this->tag[self::TAG_ARRAY])) {
            $this->getList();
        }

        $this->tag[self::TAG_CLASSIFIED] = array();
        foreach ($this->tag[self::TAG_ARRAY] as $key => $value) {
            if (strpos($value, ':') !== false) {
                preg_match($this->validClassifyPattern, $value, $matches);
                if (isset($matches[2])) {
                    unset($this->tag[self::TAG_ARRAY][$key]);
                    $this->tag[self::TAG_CLASSIFIED][$matches[1]][] = self::normalizeString($matches[2]);
                }
            }
        }

        return $this->tag[self::TAG_CLASSIFIED];
    }

    /**
     * Explode the string tag into an array, filter out any empty strings.
     * This method helps to prepare the tag string for further classification or manipulation.
     *
     * @return array Array of non-empty tags extracted from the string.
     */
    public function getList(): array
    {
        if (!isset($this->tag[self::TAG_STRING])) return [];
        $this->tag[self::TAG_ARRAY] = array_filter(explode(',', $this->tag[self::TAG_STRING]), 'strlen');

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
        $this->tag[self::TAG_ARRAY] = array_unique(array_map(function($value) {
            return self::normalizeString(preg_replace('/\s*([\/:])\s*/', ':', $value));
        }, $this->tag[self::TAG_ARRAY]));

        return $this->tag[self::TAG_ARRAY];
    }

    /**
     * Merge an array of tags into a comma-separated string. If a column name is provided, only the values
     * from that column are merged. Validates the elements to ensure they are scalar and not boolean false.
     *
     * @param array $arr Array of tags or multi-dimensional array from which to extract the tags.
     * @param string|null $column Optional name of the column whose values are to be merged.
     * @return string Resulting comma-separated string of tag IDs.
     * @throws TagException If an element is a boolean false or if the specified column is missing or non-scalar.
     */
    public static function mergeTagID(array $arr, string $column = null): string
    {
        if ($column === null) {
            $arr = array_filter($arr, 'is_scalar');
            foreach ($arr as $value) {
                if ($value === false) {
                    throw new TagException('Boolean false cannot be converted to a string');
                }
            }
            return implode(',', $arr);
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

        return implode(',', $values);
    }

    /**
     * Slice a comma-separated string of tag IDs into an array of integers.
     * Filters out any non-positive integers (e.g. zero or negative).
     *
     * @param string $str Comma-separated string of tag IDs.
     * @return array Array of positive integer tags.
     */
    public static function sliceTagID(string $str): array
    {
        return array_filter(array_map('intval', explode(',', $str)), function($value) {
            return $value > 0;
        });
    }

    /**
     * Check whether a tag ID exists within a comma-separated string of tag IDs.
     *
     * @param string $str Comma-separated string of tag IDs to search.
     * @param int $tag_id Tag ID to check for existence.
     * @return bool True if tag ID exists, false otherwise.
     */
    public static function checkTagExist(string $str, int $tag_id): bool
    {
        return in_array($tag_id, self::sliceTagID($str), true);
    }

    /**
     * Remove a specific tag ID from a comma-separated string of tag IDs and return the updated string.
     *
     * @param string $str Comma-separated string of tag IDs.
     * @param int $tag_id Tag ID to remove from the string.
     * @return string Updated string with the specific tag ID removed.
     */
    public static function removeTag(string $str, int $tag_id): string
    {
        $tags = self::sliceTagID($str);
        $key = array_search($tag_id, $tags, true);
        if ($key !== false) {
            unset($tags[$key]);
        }

        return implode(',', $tags);
    }

    public static function clearTagName(string $str): string
    {
        return preg_replace('/\s+/', '_', trim(strtolower($str)));
    }

    /**
     * Takes a string and normalizes it by converting it to lowercase, trimming whitespace,
     * and replacing spaces with underscores. Typically used to process tag names.
     *
     * @param string $str String to be cleared and normalized.
     * @return string Normalized string.
     */
    protected static function normalizeString($str): string
    {
        return str_replace(' ', '_', strtolower(trim($str)));
    }
}
