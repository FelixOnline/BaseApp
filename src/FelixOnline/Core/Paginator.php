<?php
namespace FelixOnline\Core;

/**
 * Paginator object that wraps a Manager
 */
class Paginator
{
    /**
     * Manager
     */
    public $manager;

    /**
     * Number of items per page
     */
    public $pageSize;

    /**
     * Current page
     */
    public $page;

    /**
     * Last page number
     */
    public $lastPage;

    /**
     * Total number of items
     */
    public $count;

    /**
     *
     */
    function __construct(BaseManager $manager, $page = 1, $pageSize = 20)
    {
        $this->manager = $manager;
        $this->page = $page;
        $this->pageSize = $pageSize;

        $this->calculateTotal();
    }

    protected function calculateTotal()
    {
        $this->count = $this->manager->count();

        $this->lastPage = (int) ceil($this->count / $this->pageSize);
    }

    /**
     * Get models for the specified page
     */
    public function getPage($page = NULL)
    {
        if (is_null($page)) {
            $page = $this->page;
        }
        list($offset, $size) = $this->getLimts($page);

        $this->manager->limit($offset, $size);

        return $this->manager->values();
    }

    /**
     * Returns array with first argument being the offset and the second the
     * page size
     */
    protected function getLimts($page)
    {
        $offset = ($page - 1) * $this->pageSize;
        return array($offset, $this->pageSize);
    }
}
