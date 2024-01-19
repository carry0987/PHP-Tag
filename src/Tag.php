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

    public function setString(string $value)
    {
        $this->tag[self::TAG_STRING] = $value;
    }

    public function setArray(array $value)
    {
        $this->tag[self::TAG_ARRAY] = array_unique($value);
    }

    public function getString()
    {
        return $this->tag[self::TAG_STRING];
    }

    public function getArray()
    {
        return $this->tag[self::TAG_ARRAY] ?? null;
    }

    public function getClassified()
    {
        return $this->tag[self::TAG_CLASSIFIED] ?? null;
    }

    public function checkClear()
    {
        return isset($this->tag[self::TAG_STRING]) && !preg_match('/[^\p{Han}a-zA-Z0-9\:\-,_\ ]/u', $this->tag[self::TAG_STRING]);
    }

    public function classify()
    {
        if (!isset($this->tag[self::TAG_ARRAY])) return null;

        $regex = '/([a-z]+)\:([\p{Han}a-zA-Z0-9_\ ]+)/u';
        $this->tag[self::TAG_CLASSIFIED] = array();
        foreach ($this->tag[self::TAG_ARRAY] as $key => $value) {
            if (strpos($value, ':') !== false) {
                preg_match($regex, $value, $matches);
                if (isset($matches[2])) {
                    unset($this->tag[self::TAG_ARRAY][$key]);
                    $matches[2] = trim(strtolower($matches[2]));
                    $matches[2] = str_replace(' ', '_', $matches[2]);
                    $this->tag[self::TAG_CLASSIFIED][$matches[1]][] = $matches[2];
                }
            }
        }

        return $this->tag[self::TAG_CLASSIFIED];
    }

    public function getList()
    {
        if (!isset($this->tag[self::TAG_STRING])) return null;
        $this->tag[self::TAG_ARRAY] = array_filter(explode(',', $this->tag[self::TAG_STRING]), 'strlen');

        return $this->tag[self::TAG_ARRAY];
    }

    public function getName()
    {
        if (!isset($this->tag[self::TAG_ARRAY]) || !is_array($this->tag[self::TAG_ARRAY])) return null;

        $this->tag[self::TAG_ARRAY] = array_unique(array_map(function($value) {
            return preg_replace('/\s+/', '_', trim(strtolower(preg_replace('/\s*([\/:])\s*/', ':', $value))));
        }, $this->tag[self::TAG_ARRAY]));

        return $this->tag[self::TAG_ARRAY];
    }

    public static function mergeTagID(array $arr, string $column = null)
    {
        if ($column === null) {
            foreach ($arr as $value) {
                if (!is_scalar($value)) {
                    throw new TagException('Elements must be scalar values when no column is specified.');
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

    public static function sliceTagID(string $str)
    {
        return array_filter(array_map('intval', explode(',', $str)), function($value) {
            return $value > 0;
        });
    }

    public function checkTagExist(string $str, int $tag_id)
    {
        return in_array($tag_id, self::sliceTagID($str), true);
    }

    public function removeTag(string $str, int $tag_id)
    {
        $tags = self::sliceTagID($str);
        $key = array_search($tag_id, $tags, true);
        if ($key !== false) {
            unset($tags[$key]);
        }

        return implode(',', $tags);
    }

    public function clearTagName(string $str)
    {
        return preg_replace('/\s+/', '_', trim(strtolower($str)));
    }
}
