<?php
namespace Isb\DbCommon\Query;

use Illuminate\Database\Eloquent\Builder;

class Param
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $needJoin;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var array
     */
    protected $allowedOperators = [];

    /**
     * @var \Closure
     */
    protected $performer;

    /**
     * @var ClosedOptions
     */
    protected $closedOptions;

    /**
     * @var
     */
    public $format;

    /**
     * @var
     */
    public $formatOut;

    /**
     * Param constructor.
     * @param string $field
     * @param string $type
     * @param array $allowedOperators
     * @param string|null $needJoin
     */
    public function __construct(string $field, string $type, array $allowedOperators = [], string $needJoin = null)
    {
        $this->field = $field;
        $this->type = new Type($type);
        $this->needJoin = $needJoin;
        if (!count($allowedOperators)) {
            $method = 'for' . ucfirst($this->getType()->toString());
            $allowedOperators = (new Operator())->$method();
        }
        foreach ($allowedOperators as $operator) {
            $this->allowOperation($operator);
        }
    }

    /**
     * @param string $operator
     */
    public function allowOperation(string $operator)
    {
        if (empty($operator)) {
            throw new \InvalidArgumentException('Empty operation name');
        }
        $this->allowedOperators[$operator] = new Operator($operator);
    }

    /**
     * @return array
     */
    public function getAllowedOperators(): array
    {
        return $this->allowedOperators;
    }

    /**
     * @return ClosedOptions
     */
    public function getClosedOptions(): ?ClosedOptions
    {
        return $this->closedOptions;
    }

    /**
     * @param \Closure $callback
     * @return $this
     */
    public function setPerformer(\Closure $callback)
    {
        $this->performer = $callback;
        return $this;
    }

    /**
     * @param ClosedOptions $closedOptions
     */
    public function setClosedOptions(ClosedOptions $closedOptions)
    {
        $this->closedOptions = $closedOptions;
    }

    /**
     * @param array $values
     * @param ValueBag $valueBag
     * @return bool
     */
    public function verifyOptions(array $values, ValueBag $valueBag)
    {
        $options = $this->closedOptions->toArray($valueBag);
        $list = $options['list'] ?? [];
        if (!$list) {
            return false;
        }
        $option = reset($list);
        if (is_array($option)) {
            $list = array_map(function ($v) {
                return $v['id'] ?? reset($v);
            }, $list);
        }
        foreach ($values as $value) {
            if (!in_array($value, $list)) {
                throw new \InvalidArgumentException('Value not allowed: ' . print_r($value, 1));
            }
        }
        return true;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getNeedJoin(): ?string
    {
        return $this->needJoin;
    }

    /**
     * @param Builder $query
     * @param string $alias
     * @param ValueBag $valueBag
     * @param JoinBag $joinBag
     */
    public function apply($query, $alias, ValueBag $valueBag, JoinBag $joinBag)
    {
        $value = $valueBag[$alias];
        $field = $this->getField();
        $operator = new Operator($value->operator);
        $operatorName = $operator->toString();
        if (!array_key_exists($operatorName, $this->getAllowedOperators())) {
            throw new \InvalidArgumentException('Operator not allowed: ' . $operatorName . ' for ' . $field);
        }
        $verified = true;
        if (!in_array($operatorName, [Operator::EMPTY, Operator::NOT_EMPTY]) && $this->closedOptions) {
            $verified = $this->verifyOptions((array) $value->values, $valueBag);
        }
        if (!$verified) {
            return;
        }
        $this->needJoin && $joinBag->doJoin($this->needJoin, $query);
        $typeName = $this->getType()->toString();
        $isDate = false !== stripos($typeName, 'date');
        $methodName = false !== strpos($field, '(') ? 'raw' : ($operatorName . ($isDate ? 'Date' : ''));
        if ('raw' === $methodName && $this->getType()->toString() === Type::STR && false !== stripos($field, 'like')) {
            $value->values = "%{$value->values}%";
        }
        if ($this->performer) {
            call_user_func_array($this->performer, [$query, $field, $value->values, $valueBag]);
            return;
        }
        $performer = new Performer();
        $performer->dateInFormat = $this->format ?? $performer->dateInFormat;
        $performer->dateOutFormat = $this->formatOut ?? $performer->dateOutFormat;
        $callback = [$performer, $methodName];
        call_user_func_array($callback, [$query, $field, $value->values]);
    }

    /**
     * @param ValueBag $valueBag
     * @return array
     */
    public function toArray(ValueBag $valueBag)
    {
        $line = [
            'type' => $this->getType()->toString(),
            'allows' => array_values(array_map(function ($v) {
                return $v->toString();
            }, $this->getAllowedOperators())),
        ];
        if ($this->closedOptions) {
            $line['options'] = $this->closedOptions->toArray($valueBag);
        }
        return $line;
    }
}
