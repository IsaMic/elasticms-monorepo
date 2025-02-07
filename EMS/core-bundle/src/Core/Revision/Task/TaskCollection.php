<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision\Task;

use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Entity\Task;

/**
 * @implements \IteratorAggregate<int, Task>
 */
final readonly class TaskCollection implements \IteratorAggregate
{
    /**
     * @param Task[] $tasks
     */
    public function __construct(
        private Revision $revision,
        private array $tasks = [],
    ) {
    }

    /**
     * @return \ArrayIterator<int, Task>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tasks);
    }

    public function getRevision(): Revision
    {
        return $this->revision;
    }

    public function sort(\Closure $callback): self
    {
        $sortTasks = $this->tasks;
        \usort($sortTasks, $callback);

        return new self($this->revision, $sortTasks);
    }
}
