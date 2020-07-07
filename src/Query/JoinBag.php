<?php
namespace Isb\DbCommon\Query;

use Illuminate\Database\Eloquent\Builder;

class JoinBag
{
    /**
     * @var callable[]
     */
    protected $joins = [];

    /**
     * @var string[]
     */
    protected $added = [];

    /**
     * JoinBag constructor.
     * @param array $joins
     */
    public function __construct(array $joins = [])
    {
        foreach ($joins as $joinName => $join) {
            if (!is_callable($join)) {
                throw new \InvalidArgumentException('Join not a callable: ' . $joinName);
            }
            $this->joins[$joinName] = $join;
        }
    }

    /**
     * @param string $name
     * @param Builder $query
     */
    public function doJoin(string $name, $query)
    {
        if (!array_key_exists($name, $this->joins)) {
            throw new \InvalidArgumentException('JOIN named ' . $name . ' not mapped');
        }
        if ($this->hasAdded($name)) {
            return;
        }
        $this->added[] = $name;
        call_user_func_array($this->joins[$name], [$query, $this]);
    }

    public function hasAdded(string $name)
    {
        return in_array($name, $this->added);
    }

    public function hasJoin(string $name)
    {
        return array_key_exists($name, $this->joins);
    }
}
