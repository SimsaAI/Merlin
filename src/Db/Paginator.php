<?php
namespace Merlin\Db;

/**
 * Paginator for the Query
 */
class Paginator
{
    protected Query $builder;
    protected int $pageSize;
    protected int $page;
    protected bool $reverse;

    protected int $totalItems = 0;
    protected int $totalPages = 1;
    protected int $firstItemPos = 0;
    protected int $lastItemPos = 0;

    public function __construct(
        Query $builder,
        int $page = 1,
        int $pageSize = 30,
        bool $reverse = false
    ) {
        $this->builder = $builder;
        $this->pageSize = max(1, $pageSize);
        $this->page = max(1, $page);
        $this->reverse = $reverse;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getCurrentPage(): int
    {
        return $this->page;
    }

    public function getFirstItemPos(): int
    {
        return $this->firstItemPos;
    }

    public function getLastItemPos(): int
    {
        return $this->lastItemPos;
    }

    public function execute($fetchMode = \PDO::FETCH_DEFAULT): array
    {
        // Count query
        $this->totalItems = $this->builder->count();
        $this->totalPages = $this->pageSize ? (int) ceil($this->totalItems / $this->pageSize) : 1;

        $offset = ($this->page - 1) * $this->pageSize;
        $queryLimit = $this->pageSize;
        $queryOffset = $offset;

        if ($this->reverse) {
            $queryOffset = $this->totalItems - $offset - $this->pageSize;
            if ($queryOffset < 0) {
                $queryLimit += $queryOffset;
                $queryOffset = 0;
            }
        }

        $items = [];

        if ($this->page <= $this->totalPages) {
            $items = $this->builder
                ->limit($queryLimit, $queryOffset)
                ->select()
                ->fetchAll($fetchMode);

            if ($this->reverse) {
                $items = array_reverse($items);
            }
        }

        $this->firstItemPos = $offset + 1;
        $this->lastItemPos = $offset + \count($items);

        return $items;
    }
}
