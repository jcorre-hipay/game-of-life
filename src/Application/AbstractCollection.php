<?php

declare(strict_types=1);

namespace GameOfLife\Application;

use GameOfLife\Application\Exception\CollectionIsImmutableException;
use GameOfLife\Application\Exception\UnsupportedCollectionItemTypeException;

abstract class AbstractCollection extends \SplFixedArray implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * @param array $items
     * @throws UnsupportedCollectionItemTypeException
     */
    public function __construct(array $items)
    {
        parent::__construct(\count($items));

        $index = 0;
        foreach ($items as $item) {
            if (!$this->supports($item)) {
                throw new UnsupportedCollectionItemTypeException(
                    \sprintf('Collection "%s" does not support item "%s".', \get_class($this), \get_class($item))
                );
            }

            parent::offsetSet($index, $item);
            $index += 1;
        }
    }

    /**
     * @param int|mixed $index
     * @param mixed $value
     * @throws CollectionIsImmutableException
     */
    final public function offsetSet($index, $value)
    {
        throw new CollectionIsImmutableException('Item affectation is forbidden.');
    }

    /**
     * @param int|mixed $offset
     * @throws CollectionIsImmutableException
     */
    final public function offsetUnset($offset)
    {
        throw new CollectionIsImmutableException('Item deletion is forbidden.');
    }

    /**
     * @param mixed $item
     * @return bool
     */
    abstract protected function supports($item): bool;
}
