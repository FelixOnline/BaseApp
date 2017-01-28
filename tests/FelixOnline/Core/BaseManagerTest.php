<?php

require_once __DIR__ . '/../../AppTestCase.php';
//require_once __DIR__ . '/../../utilities.php';

class BaseManagerTest extends AppTestCase
{
	public $fixtures = array(
		'articles',
		'article_authors',
		'categories',
		'audit_log'
	);

	public function getManager()
	{
		$manager = $this->mock('FelixOnline\\Core\\BaseManager')
			->new();

		$manager->this()->table = 'article';
		$manager->this()->class = 'FelixOnline\\Core\\Article';

		return $manager;
	}

	public function testSQL()
	{
		$manager = $this->getManager();

		$manager->filter("title IS NOT NULL")
			->filter("category IS NOT NULL")
			->order('id', 'DESC')
			->limit(0, 10);

		$sql = $manager->getSQL();

		$this->assertEquals($sql, '(SELECT `article`.`id`
FROM `article`
WHERE `article`.title IS NOT NULL
AND `article`.category IS NOT NULL
AND (`article`.deleted = 0 OR `article`.deleted IS NULL)
)
ORDER BY `article`.`id` DESC
LIMIT 0, 10');
	}

	public function testAll()
	{
		$manager = $this->getManager();

		$all = $manager->all();

		$this->assertCount(3, $all);
		$this->assertInstanceOf('FelixOnline\Core\Article', $all[0]);
	}

	public function testFilter()
	{
		$manager = $this->getManager();

		$filtered = $manager->filter('date < "2010-12-31 23:59:59"')
			->filter('`id` IN (1, 2)')
			->values();

		$this->assertCount(2, $filtered);
		$this->assertInstanceOf('FelixOnline\Core\Article', $filtered[0]);
	}

	public function testFilterParams()
	{
		$manager = $this->getManager();

		$manager->filter('category = %i', array(1));

		$sql = $manager->getSQL();

		$this->assertEquals($sql, '(SELECT `article`.`id`
FROM `article`
WHERE `article`.category = 1
AND (`article`.deleted = 0 OR `article`.deleted IS NULL)
)');
	}

	public function testFilterParamsException()
	{
		$manager = $this->getManager();

		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'Values is not an array'
		);
		$manager->filter('category = %i', 1);
	}

	public function testOrder()
	{
		$manager = $this->getManager();

		$query = $manager->filter('date < "2010-12-31 23:59:59"')
			->filter('`id` IN (1, 2)')
			->order('id', 'ASC');

		$results = $query->values();

		$this->assertCount(2, $results);
		$this->assertInstanceOf('FelixOnline\Core\Article', $results[0]);
		$this->assertEquals($results[0]->getId(), 1);
	}

	public function testOrderMultiple()
	{
		$manager = $this->getManager();

		$query = $manager->order(array('id', 'title'), 'DESC');

		$sql = $manager->getSQL();

		$this->assertEquals($sql, "(SELECT `article`.`id`
FROM `article`
WHERE (`article`.deleted = 0 OR `article`.deleted IS NULL)
)
ORDER BY `article`.`id` DESC, `article`.`title` DESC");
	}

	public function testOrderWithTable()
	{
		$manager = $this->getManager();

		$query = $manager->order('another_table.id', 'DESC');

		$sql = $manager->getSQL();

		$this->assertEquals($sql, "(SELECT `article`.`id`
FROM `article`
WHERE (`article`.deleted = 0 OR `article`.deleted IS NULL)
)
ORDER BY another_table.id DESC");
	}

	public function testLimit()
	{
		$manager = $this->getManager();

		$query = $manager->filter('date < "2010-12-31 23:59:59"')
			->order('id', 'ASC');

		$query->limit(0, 1);

		$results = $query->values();

		$this->assertCount(1, $results);
	}

	public function testCount()
	{
		$manager = $this->getManager();

		$query = $manager->filter('date < "2010-12-31 23:59:59"')
			->filter('`id` IN (1, 2)')
			->order('id', 'ASC');

		$count = $query->count();

		$this->assertEquals($count, 2);
	}

	public function testCountWithLimit()
	{
		$manager = $this->getManager();

		$query = $manager->filter('date < "2010-12-31 23:59:59"')
			->filter('`id` IN (1, 2)')
			->order('id', 'ASC')
			->limit(10, 10);

		$count = $query->count();

		$this->assertEquals($count, 2);
	}

	public function testQueryExceptionsBadQuery()
	{
		$manager = $this->getManager();

		$this->setExpectedException(
			'FelixOnline\Exceptions\SQLException'
		);
		$manager->filter('not valid sql')->values();
	}

	public function testQueryNoResults()
	{
		$manager = $this->getManager();

		$null = $manager->filter('id = 0')->values();

		$this->assertNull($null);
	}

	public function testGetOne()
	{
		$manager = $this->getManager();

		$one = $manager->filter('id = %i', array(1))
			->one();

		$this->assertInstanceOf('FelixOnline\Core\Article', $one);
	}

	public function testGetOneMoreThanOne()
	{
		$manager = $this->getManager();

		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'More than one result'
		);

		$one = $manager->filter('date < "2010-12-31 23:59:59"')
			->filter('`id` IN (1, 2)')
			->one();
	}

	public function testGetOneException()
	{
		$manager = $this->getManager();

		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'No results'
		);
		$manager->filter('id = 0')->one();
	}

	public function testJoin()
	{
		$m1 = $this->getManager();
		$m1->filter('date < "2010-12-31 23:59:59"');

		$m2 = $this->getManager();

		$m2->table = 'article_author';
		$m2->pk = 'article';

		$m2->filter('author = "%s"', array('felix'));

		$m1->join($m2);
		$m1->order('id', 'ASC');

		$sql = $m1->getSQL();

		$this->assertEquals($sql, '(SELECT `article`.`id`
FROM `article`
JOIN `article_author` ON ( `article`.`id` = `article_author`.`article` )

WHERE `article`.date < "2010-12-31 23:59:59"
AND (`article`.deleted = 0 OR `article`.deleted IS NULL)
AND `article_author`.author = "felix"
AND (`article_author`.deleted = 0 OR `article_author`.deleted IS NULL)
)
ORDER BY `article`.`id` ASC');
	}

	public function testNestedJoin()
	{
		$m1 = $this->getManager();
		$m1->filter('date < "2010-12-31 23:59:59"');

		$m2 = $this->getManager();

		$m2->table = 'category';
		$m2->pk = 'id';

		$m2->filter('id = "%s"', array('1'));

		$m3 = $this->getManager();

		$m3->table = 'category_author';
		$m3->pk = 'category';

		$m3->filter('user = "%s"', array('pk1811'));

		$m2->join($m3);

		$m1->join($m2, null, "category");
		$m1->order('id', 'ASC');

		$sql = $m1->getSQL();

		$this->assertEquals($sql, '(SELECT `article`.`id`
FROM `article`
JOIN `category` ON ( `article`.`category` = `category`.`id` )
JOIN `category_author` ON ( `category`.`id` = `category_author`.`category` )

WHERE `article`.date < "2010-12-31 23:59:59"
AND (`article`.deleted = 0 OR `article`.deleted IS NULL)
AND `category`.id = "1"
AND (`category`.deleted = 0 OR `category`.deleted IS NULL)
AND `category_author`.user = "pk1811"
AND (`category_author`.deleted = 0 OR `category_author`.deleted IS NULL)
)
ORDER BY `article`.`id` ASC');
	}

	public function testLeftJoin()
	{
		$m1 = $this->getManager();
		$m2 = $this->getManager();

		$m2->table = 'article_author';
		$m2->pk = 'article';

		$m2->filter('author = "%s"', array('felix'));

		$m1->join($m2, 'LEFT');

		$sql = $m1->getSQL();

		$this->assertEquals($sql, '(SELECT `article`.`id`
FROM `article`
LEFT JOIN `article_author` ON ( `article`.`id` = `article_author`.`article` )

WHERE (`article`.deleted = 0 OR `article`.deleted IS NULL)
AND `article_author`.author = "felix"
AND (`article_author`.deleted = 0 OR `article_author`.deleted IS NULL)
)');
	}

	public function testLeftJoinSpecificColumn()
	{
		$m1 = $this->getManager();
		$m2 = $this->getManager();

		$m2->table = 'article_author';
		$m2->pk = 'article';

		$m2->filter('author = "%s"', array('felix'));

		$m1->join($m2, 'LEFT', 'TEST');

		$sql = $m1->getSQL();

		$this->assertEquals($sql, '(SELECT `article`.`id`
FROM `article`
LEFT JOIN `article_author` ON ( `article`.`TEST` = `article_author`.`article` )

WHERE (`article`.deleted = 0 OR `article`.deleted IS NULL)
AND `article_author`.author = "felix"
AND (`article_author`.deleted = 0 OR `article_author`.deleted IS NULL)
)');
	}

	public function testJoinCount()
	{
		$m1 = $this->getManager();
		$m1->filter('date < "2010-12-31 23:59:59"');

		$m2 = $this->getManager();

		$m2->table = 'article_author';
		$m2->pk = 'article';

		$m2->filter('author = "%s"', array('felix'));

		$m1->join($m2);
		$m1->order('id', 'ASC');

		$count = $m1->count();

		$this->assertEquals($count, 2);
	}

	public function testBuild()
	{
		$manager = \FelixOnline\Core\BaseManager::build('FelixOnline\Core\Article', 'article', 'id');

		$this->assertEquals($manager->class, 'FelixOnline\Core\Article');
		$this->assertEquals($manager->table, 'article');
		$this->assertEquals($manager->pk, 'id');
	}

	public function testCache()
	{
		$app = \FelixOnline\Core\App::getInstance();

		$manager = $this->getManager();
		$manager->cache(true);

		$selects = $app['db']->get_row("SHOW STATUS LIKE 'Com_select'")->Value;
		$this->assertEquals(0, (int) $selects);

		$all = $manager->all();

		$selects = $app['db']->get_row("SHOW STATUS LIKE 'Com_select'")->Value;
		// 7 because of the selects when instantiating the models
		$this->assertEquals(7, (int) $selects);
		$this->assertCount(3, $all);
		$this->assertInstanceOf('FelixOnline\Core\Article', $all[0]);

		$all = $manager->all();

		$selects = $app['db']->get_row("SHOW STATUS LIKE 'Com_select'")->Value;
		$this->assertEquals(7, (int) $selects);
		$this->assertCount(3, $all);
		$this->assertInstanceOf('FelixOnline\Core\Article', $all[0]);
	}
}
