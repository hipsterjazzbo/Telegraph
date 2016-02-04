<?php

namespace HipsterJazzbo\Telegraph;

use ArrayObject;
use InvalidArgumentException;

class PushableCollection extends ArrayObject
{
    public function offsetSet($index, $value)
    {
        if ($value instanceof Pushable) {
            parent::offsetSet($index, $value);
        }

        throw new InvalidArgumentException('Value must be a Foo');
    }
}
