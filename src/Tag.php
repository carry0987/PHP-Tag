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

    public function __construct(string $value = '')
    {
        $this->tag[self::TAG_STRING] = $value;
    }

    public function setString(string $string): void
    {
        $this->tag[self::TAG_STRING] = $string;
    }

    public function setArray(array $array, bool $preserveOrder = true): void
    {
        if ($preserveOrder === false) {
            $this->tag[self::TAG_ARRAY] = array_unique($array);
            return;
        }

        $uniqueArray = [];
        foreach ($array as $item) {
            if (!in_array($item, $uniqueArray, true)) {
                $uniqueArray[] = $item;
            }
        }

        $this->tag[self::TAG_ARRAY] = $uniqueArray;
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

    public function isValidTagString(): bool
    {
        return isset($this->tag[self::TAG_STRING]) && !preg_match('/[^\p{Han}a-zA-Z0-9\:\-,_\ ]/u', $this->tag[self::TAG_STRING]);
    }

    public function classify(): array
    {
        if (!isset($this->tag[self::TAG_ARRAY])) return [];

        $regex = '/([a-z]+)\:([\p{Han}a-zA-Z0-9_\ ]+)/u';
        $this->tag[self::TAG_CLASSIFIED] = array();
        foreach ($this->tag[self::TAG_ARRAY] as $key => $value) {
            if (strpos($value, ':') !== false) {
                preg_match($regex, $value, $matches);
                if (isset($matches[2])) {
                    unset($this->tag[self::TAG_ARRAY][$key]);
                    $this->tag[self::TAG_CLASSIFIED][$matches[1]][] = self::normalizeString($matches[2]);
                }
            }
        }

        return $this->tag[self::TAG_CLASSIFIED];
    }

    public function getList(): array
    {
        if (!isset($this->tag[self::TAG_STRING])) return [];
        $this->tag[self::TAG_ARRAY] = array_filter(explode(',', $this->tag[self::TAG_STRING]), 'strlen');

        return $this->tag[self::TAG_ARRAY];
    }

    public function getName(): array
    {
        if (!is_array($this->tag[self::TAG_ARRAY] ?? null)) return [];

        $this->tag[self::TAG_ARRAY] = array_unique(array_map(function($value) {
            return self::normalizeString(preg_replace('/\s*([\/:])\s*/', ':', $value));
        }, $this->tag[self::TAG_ARRAY]));

        return $this->tag[self::TAG_ARRAY];
    }

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
            $values[] = $item[$column];
        }

        return implode(',', $values);
    }

    public static function sliceTagID(string $str): array
    {
        return array_filter(array_map('intval', explode(',', $str)), function($value) {
            return $value > 0;
        });
    }

    public static function checkTagExist(string $str, int $tag_id): bool
    {
        return in_array($tag_id, self::sliceTagID($str), true);
    }

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
     * Normalize string, convert to lowercase and replace spaces with underscores
     *
     * @param string $str String to normalize
     * @return string Normalized string
     */
    protected static function normalizeString($str): string
    {
        return str_replace(' ', '_', strtolower(trim($str)));
    }
}
