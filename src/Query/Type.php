<?php
namespace Isb\DbCommon\Query;

class Type
{
    const STR = 'string';
    const NUMERIC = 'numeric';
    const INT = 'int';
    const DECIMAL = 'decimal';
    const DATE = 'date';
    const DATE_TIME = 'datetime';
    const TIME = 'time';

    protected $value;

    /**
     * Type constructor.
     * @param $value
     */
    public function __construct($value)
    {
        if (!in_array($value, [
            self::STR,
            self::NUMERIC,
            self::INT,
            self::DECIMAL,
            self::DATE,
            self::DATE_TIME,
            self::TIME]
        )) {
            throw new \InvalidArgumentException('Invalid type: ' . $value);
        }
        $this->value = $value;
    }

    public function toString()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
