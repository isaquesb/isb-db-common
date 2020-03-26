<?php
namespace Isb\DbCommon\Query;

use Illuminate\Database\Eloquent\Builder;

class Performer
{
    /**
     * @var string
     */
    public $dateInFormat = 'Y-m-d';

    /**
     * @var string
     */
    public $dateOutFormat = 'Y-m-d';

    /**
     * @param Builder $query
     * @param string $field
     * @param string|int $value
     */
    public function raw($query, $field, $value)
    {
        $query->whereRaw($field, [$value]);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param string|int $value
     */
    public function equal($query, $field, $value)
    {
        $query->where($field, $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param \DateTime $value
     */
    public function equalDate($query, $field, $value)
    {
        $query->whereDate($field, $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param string $value
     */
    public function startWith($query, $field, $value)
    {
        $query->where($field, 'like', $value .  '%');
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param string $value
     */
    public function contains($query, $field, $value)
    {
        $query->where($field, 'like', '%' . $value .  '%');
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     */
    public function diff($query, $field, $value)
    {
        $query->where($field, '!=', $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     */
    public function great($query, $field, $value)
    {
        $query->where($field, '>', $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     */
    public function greatEqual($query, $field, $value)
    {
        $query->where($field, '>=', $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     */
    public function less($query, $field, $value)
    {
        $query->where($field, '<', $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     */
    public function lessEqual($query, $field, $value)
    {
        $query->where($field, '<=', $value);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param array $values
     */
    public function between($query, $field, array $values)
    {
        $query->whereBetween($field, $values);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param array $values
     */
    public function betweenDate($query, $field, array $values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = (\DateTime::createFromFormat($this->dateInFormat, $value))->format($this->dateOutFormat);
        }
        $query->whereBetween($field, $values);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param array $values
     */
    public function in($query, $field, array $values)
    {
        $query->whereIn($field, $values);
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param array $values
     */
    public function notIn($query, $field, array $values)
    {
        $query->whereNotIn($field, $values);
    }

    /**
     * @param Builder $query
     * @param string $field
     */
    public function isEmpty($query, $field)
    {
        $query->whereNull($field);
    }

    /**
     * @param Builder $query
     * @param string $field
     */
    public function isNotEmpty($query, $field)
    {
        $query->whereNotNull($field);
    }
}
