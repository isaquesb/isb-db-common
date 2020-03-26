<?php
namespace Isb\DbCommon\Query;

class ClosedOptions implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var array
     */
    protected $list = [];

    /**
     * @var array
     */
    protected $urlInstruction = null;

    /**
     * ClosedOptions constructor.
     * @param array $list
     * @param callable $callback
     * @param string|null $url
     * @param string|null $urlInstructions
     */
    public function __construct(
        array $list = [],
        callable $callback = null,
        string $url = null,
        string $urlInstructions = null)
    {
        $this->list = $list;
        $this->callback = $callback;
        $this->url = $url;
        $this->urlInstruction = $urlInstructions;
    }

    public function toArray(ValueBag $valueBag = null)
    {
        if (!count($this->list) && null !== $this->callback) {
            $this->list = call_user_func_array($this->callback, [$valueBag]);
        }
        if ($this->url && empty($this->list)) {
            return [
                'type' => 'url',
                'url' => $this->url,
                'instructions' => $this->urlInstruction
            ];
        }
        return [
            'type' => 'list',
            'list' => $this->list
        ];
    }

    function jsonSerialize()
    {
        return $this->toArray();
    }
}
