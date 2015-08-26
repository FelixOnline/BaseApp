<?php
namespace FelixOnline\Core;
/**
 * Advert class
 */
class Advert extends BaseDB
{
	public $dbtable = 'advert';

	function __construct($id = NULL)
	{
		$fields = array(
			'details' => new Type\CharField(),
			'image' => new Type\ForeignKey('FelixOnline\Core\Image'),
			'url' => new Type\CharField(),
			'start_date' => new Type\DateTimeField(),
			'end_date' => new Type\DateTimeField(),
			'max_impressions' => new Type\IntegerField(),
			'views' => new Type\IntegerField(),
			'clicks' => new Type\IntegerField(),
			'frontpage' => new Type\BooleanField(),
			'categories' => new Type\BooleanField(),
			'articles' => new Type\BooleanField(),
		);

		parent::__construct($fields, $id);
	}

	/**
	 * Public: Get categories that this advert is linked to
	 *
	 * Returns array of category objects
	 */
	public function getAllocatedCategories()
	{
		$categories = BaseManager::build('FelixOnline\Core\Category', 'advert_category', 'category')
			->filter('advert = %i', array($this->getId()))
			->values();

		return $categories;
	}

	public function viewAdvert() {
		$this->setViews($this->getViews() + 1)->save();

		return $this;
	}

	public function clickAdvert() {
		$this->setClicks($this->getClicks() + 1)->save();

		return $this;
	}

	public function getActive()
	{
		if($this->getMaxImpressions() <= $this->getViews()) {
			return false;
		}

		if(strtotime($this->getEndDate()) <= time()) {
			return false;
		}

		return true;
	}
}
