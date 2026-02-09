<?php
namespace CoreLib\Db;

/**
 * Paginator for the SelectBuilder
 */
class SelectBuilderPaginator implements PaginatorInterface
{
	/**
	 * @var SelectBuilder
	 */
	protected $_builder;

	/**
	 * @var int
	 */
	protected $_limit;

	/**
	 * @var int
	 */
	protected $_page;

	/**
	 * @var bool
	 */
	protected $_reverse;

	/**
	 * @var bool
	 */
	protected $_debug;

	/**
	 * @param array $config
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $config)
	{
		if (!isset($config['builder']) || !($config['builder'] instanceof SelectBuilder)) {
			throw new \InvalidArgumentException('Expect property "builder" an instance of "SelectBuilder"');
		}
		$this->_builder = $config['builder'];
		$this->_limit = isset($config['limit']) ? (int) $config['limit'] : 10;
		$this->_page = isset($config['page']) ? (int) $config['page'] : 1;
		$this->_reverse = isset($config['reverse']) && $config['reverse'];
		$this->_debug = isset($config['debug']) && $config['debug'];
	}

	/**
	 * Set the current page number
	 *
	 * @param int $page
	 */
	public function setCurrentPage($page)
	{
		$this->_page = $page;
	}

	public function setLimit($limit)
	{
		$this->_limit = (int) $limit;
	}

	public function getLimit()
	{
		return $this->_limit;
	}

	/**
	 * Returns a slice of the resultset to show in the pagination
	 *
	 * @param bool $bFetchObject
	 * @return \stdClass
	 */
	public function getPaginate($bFetchObject = false)
	{
		$aColumns = $this->_builder->getColumns();
		$mOrderBy = $this->_builder->getOrderBy();
		$oResult = $this->_builder
			->columns(['COUNT(*) AS total_items'])
			->limit(0)
			->orderBy(null)
			->execute($this->_debug);
		$iTotalItems = 0;
		while ($aResult = $oResult->fetchArray()) {
			$iTotalItems += (int) $aResult['total_items'];
		}
		$iLastPage = $this->_limit ? (int) ceil($iTotalItems / $this->_limit) : 1;
		$oPage = new \stdClass();
		$iQueryLimit = $this->_limit;
		$iOffset = $iQueryOffset = max(0, $this->_page - 1) * $iQueryLimit;
		if ($this->_page <= $iLastPage) {
			//$this->_page = $iLastPage;
			if ($this->_reverse) {
				// t:60 l:50 o:0 -> 60 - 0 - 50
				// t:60 l:50 o:50 -> 60 - 50 - 50
				$iQueryOffset = $iTotalItems - $iOffset - $this->_limit;
				if ($iQueryOffset < 0) {
					$iQueryLimit += $iQueryOffset;
					$iQueryOffset = 0;
				}
			}
			$oPage->items = $this->_builder
				->columns($aColumns)
				->orderBy($mOrderBy)
				->limit($iQueryLimit, $iQueryOffset)
				->execute($this->_debug)
				->fetchAll($bFetchObject ? \PDO::FETCH_OBJ : null);
			if ($this->_reverse) {
				$oPage->items = array_reverse($oPage->items);
			}
		} else {
			$oPage->items = [];
		}
		$oPage->current = max(1, $this->_page);
		$oPage->prev = max(1, $oPage->current - 1);
		$oPage->next = max(1, min($iLastPage, $oPage->current + 1));
		$oPage->last = $iLastPage;
		$oPage->total_pages = $iLastPage;
		$oPage->total_items = $iTotalItems;
		$oPage->first_item = $iOffset + 1;
		$oPage->last_item = $iOffset + count($oPage->items);
		$oPage->limit = $this->_limit;
		return $oPage;
	}
}
