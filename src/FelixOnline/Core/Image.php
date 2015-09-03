<?php
namespace FelixOnline\Core;
/*
 * Image class
 *
 * Fields:
 *	  id			  -
 *	  title		   -
 *	  uri			 -
 *	  user			-
 *	  description	 -
 *	  timestamp	   -
 *	  v_offset		-
 *	  h_offset
 *	  attribution
 *	  attr_link
 *	  width
 *	  height
 */
class Image extends BaseDB
{
	public $dbtable = 'image';

	/**
	 * Constructor for Image class
	 * If initialised with id then store relevant data in object
	 *
	 * $id - ID of image (optional)
	 *
	 * Returns image object
	 */
	function __construct($id=NULL) {
		$fields = array(
			'title' => new Type\CharField(),
			'uri' => new Type\CharField(),
			'user' => new Type\ForeignKey('FelixOnline\Core\User'),
			'description' => new Type\CharField(),
			'timestamp' => new Type\DateTimeField(),
			'attribution' => new Type\CharField(),
			'attr_link' => new Type\CharField(),
			'width' => new Type\IntegerField(),
			'height' => new Type\IntegerField(),
		);

		parent::__construct($fields, $id);
	}

	/**
	 * Public: Get image source url
	 */
	public function getURL($width = '', $height = '') {
		$uri = $this->getName();
		if ($height && $width) {
			return Settings::get('image_url').$width.'/'.$height.'/'.$uri;
		} else if ($width) {
			return Settings::get('image_url').$width.'/'.$uri;
		} else { // original image
			return Settings::get('image_url').$uri;
		}
	}

	/**
	 * Public: Check if image is tall or not
	 */
	public function isTall() {
		if ($this->getWidth() < $this->getHeight()) {
			return true;
		}
		return false;
	}

	/**
	 * Public: Get image name
	 * Get image filename
	 */
	public function getName() {
		return str_replace('img/upload/', '', $this->getUri());
	}
}
