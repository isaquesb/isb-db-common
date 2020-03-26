<?php
namespace Isb\DbCommon\Query;

use Illuminate\Database\Eloquent\Builder;

class ParamSet implements \JsonSerializable
{
    /**
     * @var Param[]
     */
    protected $list = [];

    /**
     * @var array
     */
    protected $sortable = [];

    /**
     * @var JoinBag
     */
    protected $joinBag;

    /**
     * ParamSet constructor.
     * @param array $params
     * @param array $sortable
     * @param JoinBag|null $joinBag
     */
    public function __construct(array $params = [], array $sortable = [], JoinBag $joinBag = null)
    {
        $this->joinBag = $joinBag ?? new JoinBag();
        foreach ($params as $alias => $param) {
            $this->addParam($alias, $param);
        }
        $this->setSortable($sortable);
    }

    /**
     * @param string $alias
     * @param Param $param
     */
    public function addParam(string $alias, Param $param)
    {
        $this->list[$alias] = $param;
    }

    /**
     * @return array
     */
    public function getSortable(): array
    {
        return array_keys($this->sortable);
    }

    /**
     * @param string $col
     * @param string|int $sort
     */
    public function addSortable($col, $sort = 0)
    {
        if (is_numeric($sort)) {
            $sort = array_key_exists($col, $this->list) ? $this->list[$col]->getField() : $col;
        }
        $this->sortable[$sort] = $col;
    }

    /**
     * @param array $sortable
     */
    public function setSortable(array $sortable)
    {
        $this->sortable = [];
        foreach ($sortable as $sort => $col) {
            $this->addSortable($col, $sort);
        }
    }

    public function toArray(array $data)
    {
        $cleared = $this->unEmptyAliased($data);
        $valueBag = new ValueBag($cleared);
        $rows = [];
        foreach ($this->list as $alias => $param) {
            $rows[] = array_merge(['name' => $alias], $param->toArray($valueBag));
        }
        return [
            'filters' => $rows,
            'sortable' => $this->getSortable(),
        ];
    }

    function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param Builder $query
     * @param array $data
     */
    public function apply($query, array $data)
    {
        $cleared = $this->unEmptyAliased($data);
        $valueBag = new ValueBag($cleared);
        foreach (array_keys($cleared) as $alias) {
            $param = $this->list[$alias];
            $param->apply($query, $alias, $valueBag, $this->joinBag);
        }
    }

    /**
     * @param Builder $query
     * @param array|string $sorts
     */
    public function sort($query, $sorts)
    {
        $joinBag = $this->joinBag;
        foreach ((array) $sorts as $alias) {
            $direction = 0 === strpos($alias, '-') ? 'desc' : 'asc';
            $alias = 'desc' == $direction ? substr($alias, 1) : $alias;
            if (!array_key_exists($alias, $this->sortable)) {
                continue;
            }
            $sortCol = $this->sortable[$alias];
            $param = array_key_exists($alias, $this->list) ? $this->list[$alias] : null;
            $join = $param ? $param->getNeedJoin() : ($joinBag->hasJoin($alias) ? $alias : null);
            $join && $joinBag->doJoin($join, $query);
            $joinBag->hasJoin($alias) && $joinBag->doJoin($alias, $query);
            $query->orderBy($sortCol, $direction);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function options(array $data)
    {
        $opts = [];
        $cleared = $this->unEmptyAliased($data);
        $valueBag = new ValueBag($cleared);
        foreach ($this->list as $alias => $param) {
            if (!($options = $param->getClosedOptions())) {
                continue;
            }
            $opts[$alias] = $param->toArray($valueBag);
        }
        return $opts;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function unEmptyAliased(array $data)
    {
        $all = [];
        foreach ($data as $alias => $rawValue) {
            if (empty($rawValue) || !array_key_exists($alias, $this->list)) {
                continue;
            }
            $all[$alias] = $rawValue;
        }
        return $all;
    }
}
