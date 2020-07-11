<?php
namespace Isb\DbCommon\Query;

class Operator
{
    const EQUAL = 'equal';
    const START_WITH = 'startWith';
    const CONTAINS = 'contains';
    const DIFFERENT = 'diff';
    const GREATER = 'great';
    const GREATER_EQUAL = 'greatEqual';
    const LESS = 'less';
    const LESS_EQUAL = 'lessEqual';
    const BETWEEN = 'between';
    const IN = 'in';
    const NOT_IN = 'notIn';
    const EMPTY = 'isEmpty';
    const NOT_EMPTY = 'isNotEmpty';

    protected $value;

    /**
     * Type constructor.
     * @param string $value
     */
    public function __construct(string $value = null)
    {
        if (is_null($value)) {
            return;
        }
        if (!in_array($value, [
            self::EQUAL,
            self::START_WITH,
            self::CONTAINS,
            self::DIFFERENT,
            self::GREATER,
            self::GREATER_EQUAL,
            self::LESS,
            self::LESS_EQUAL,
            self::BETWEEN,
            self::IN,
            self::NOT_IN,
            self::EMPTY,
            self::NOT_EMPTY,
        ])) {
            throw new \InvalidArgumentException('Invalid operator: ' . $value);
        }
        $this->value = $value;
    }

    public function toString()
    {
        return $this->value ?? '';
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function forString()
    {
        return [
            self::EQUAL,
            self::START_WITH,
            self::CONTAINS,
        ];
    }

    public function forNumeric()
    {
        return [
            self::EQUAL,
            self::DIFFERENT,
            self::GREATER,
            self::GREATER_EQUAL,
            self::LESS,
            self::LESS_EQUAL,
            self::IN,
            self::NOT_IN,
        ];
    }

    public function forInt()
    {
        return $this->forNumeric();
    }

    public function forDecimal()
    {
        return $this->forNumeric();
    }

    public function forDate()
    {
        return [
            self::EQUAL,
            self::BETWEEN,
        ];
    }

    public function forDatetime()
    {
        return [
            self::BETWEEN,
        ];
    }

    public function forTime()
    {
        return [
            self::EQUAL,
            self::BETWEEN,
            self::GREATER,
            self::GREATER_EQUAL,
            self::LESS,
            self::LESS_EQUAL,
        ];
    }
}
