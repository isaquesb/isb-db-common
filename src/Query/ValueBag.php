<?php
namespace Isb\DbCommon\Query;

class ValueBag implements \ArrayAccess
{
    /**
     * @var ValueItem[]
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $original = [];

    /**
     * ValueBag constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->original = $data;
        $this->convert();
    }

    /**
     * @param string $alias
     * @return ValueItem|null
     */
    public function get($alias)
    {
        return $this->data[$alias] ?? null;
    }

    protected function convert()
    {
        foreach ($this->original as $key => $value) {
            $this->data[$key] = $this->valueToItem($value);
        }
    }

    /**
     * @param mixed $param
     * @return ValueItem
     */
    protected function valueToItem($param)
    {
        $item = new ValueItem();
        if (is_array($param)) {
            $item->operator = Operator::IN;
            $item->values = $param;
            return $item;
        }
        if (in_array($param, [Operator::EMPTY, Operator::NOT_EMPTY])) {
            $item->operator = $param;
            return $item;
        }
        $matches = null;
        if (preg_match('/^([0-9:-]{8,10})[^0-9]+([0-9:-]{8,10})$/', $param, $matches)) {
            $item->operator = Operator::BETWEEN;
            $item->values = [$matches[1], $matches[2]];
            return $item;
        }
        if (false == strpos($param, '::')) {
            $item->operator = Operator::EQUAL;
            $item->values = $param;
            return $item;
        }
        $split = explode('::', $param, 2);
        $item->operator = $split[0];
        $item->values = $split[1];
        $splitOperators = [Operator::IN, Operator::NOT_IN, Operator::BETWEEN];
        if (false !== strpos($item->values, ',') && in_array($item->operator, $splitOperators)) {
            $item->values = explode(',', $item->values);
        }
        return $item;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->original[$offset] = $value;
        $this->data[$offset] = $this->valueToItem($value);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
