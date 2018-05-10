<?php declare(strict_types=1);

namespace Acme\Test\Account\UseCase;

final class ArrayRepository implements \ArrayAccess
{
    private $items = [];

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset] ?: null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
