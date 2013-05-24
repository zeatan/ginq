<?php
/**
 * Ginq: `LINQ to Object` inspired DSL for PHP
 * Copyright 2013, Atsushi Kanehara <akanehara@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5.3 or later
 *
 * @author     Atsushi Kanehara <akanehara@gmail.com>
 * @copyright  Copyright 2013, Atsushi Kanehara <akanehara@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Ginq
 */

namespace Ginq\Iterator;

use Ginq\Core\EqualityComparer;
use Ginq\Core\Lookup;
use Ginq\Core\Selector;
use Ginq\Selector\ProjectionSelector;
use Ginq\Core\JoinSelector;
use Ginq\Util\IteratorUtil;

class JoinIterator implements \Iterator
{
    /**
     * @var SelectManyWithJoinIterator
     */
    protected $it;

    /**
     * @var \Iterator
     */
    protected $outer;

    /**
     * @var \Iterator
     */
    protected $inner;

    /**
     * @var Selector
     */
    protected $outerKeySelector;

    /**
     * @var Selector
     */
    protected $innerKeySelector;

    /**
     * @var JoinSelector
     */
    protected $valueJoinSelector;

    /**
     * @var JoinSelector
     */
    protected $keyJoinSelector;

    /**
     * @var EqualityComparer
     */
    protected $eqComparer;

    /**
     * @param array|\Traversable $outer
     * @param array|\Traversable $inner
     * @param Selector $outerKeySelector
     * @param Selector $innerKeySelector
     * @param JoinSelector $valueJoinSelector
     * @param JoinSelector $keyJoinSelector
     * @param EqualityComparer $eqComparer
     */
    public function __construct(
        $outer, $inner,
        $outerKeySelector, $innerKeySelector,
        $valueJoinSelector, $keyJoinSelector,
        $eqComparer)
    {
        $this->outer = IteratorUtil::iterator($outer);
        $this->inner = IteratorUtil::iterator($inner);
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->valueJoinSelector = $valueJoinSelector;
        $this->keyJoinSelector = $keyJoinSelector;
        $this->eqComparer = $eqComparer;
    }

    public function current()
    {
        return $this->it->current();
    }

    public function next()
    {
        $this->it->next();
    }

    public function key()
    {
        return $this->it->key();
    }

    public function valid()
    {
        return $this->it->valid();
    }

    public function rewind()
    {
        $outerKeySelector = $this->outerKeySelector;
        $lookup = Lookup::from($this->inner, $this->innerKeySelector, $this->eqComparer);
        $this->it = new SelectManyWithJoinIterator(
            $this->outer,
            new ProjectionSelector(
                function ($v, $k) use (&$lookup, $outerKeySelector) {
                    return $lookup->get($outerKeySelector->select($v, $k));
                }
            ),
            $this->valueJoinSelector,
            $this->keyJoinSelector
        );
        $this->it->rewind();
    }
}

