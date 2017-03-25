<?php

require_once __DIR__ . '/../../AppTestCase.php';
//require_once __DIR__ . '/../../utilities.php';

class BaseManagerTest extends AppTestCase {
    public $fixtures = array(
        'audit_log',
        'article_authors'
    );

    public function getManager() {
        $manager = $this->mock('FelixOnline\\Core\\BaseManager')
        ->new();

        $manager->this()->table = 'audit_log';
        $manager->this()->class = 'FelixOnline\\Core\\AuditLog';

        return $manager;
    }

    public function testBuildManagerWithNull() {
        $manager = FelixOnline\Core\BaseManager::build(
            'FelixOnline\\Core\\AuditLog');

        $this->assertEquals($manager->table, 'audit_log');
        $this->assertEquals($manager->pk, 'id');
    }


    public function testSQL() {
        $manager = $this->getManager();

        $manager->filter("table IS NOT NULL")
                ->filter("key IS NOT NULL")
                ->order('id', 'DESC')
                ->limit(0, 10);

        $sql = $manager->getSQL();

        $this->assertEquals($sql, '(SELECT `audit_log`.*
FROM `audit_log`
WHERE `audit_log`.table IS NOT NULL
AND `audit_log`.key IS NOT NULL
AND (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
)
ORDER BY `audit_log`.`id` DESC
LIMIT 0, 10');
    }

    public function testAll() {
        $manager = $this->getManager();

        $all = $manager->all();

        $this->assertCount(3, $all);
        $this->assertInstanceOf('FelixOnline\Core\AuditLog', $all[0]);
        $this->assertEquals('create', $all[0]->getAction());
    }

    public function testFilter() {
        $manager = $this->getManager();

        $filtered = $manager->filter('action = "create"')
                            ->filter('`id` IN (1, 2)')
                            ->values();

        $this->assertCount(2, $filtered);
        $this->assertInstanceOf('FelixOnline\Core\AuditLog', $filtered[0]);
    }

    public function testFilterParams() {
        $manager = $this->getManager();

        $manager->filter('table = %i', array(1));

        $sql = $manager->getSQL();

        $this->assertEquals($sql, '(SELECT `audit_log`.*
FROM `audit_log`
WHERE `audit_log`.table = 1
AND (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
)');
    }

    public function testFilterParamsException() {
        $manager = $this->getManager();

        $this->setExpectedException(
            'FelixOnline\Exceptions\InternalException',
            'Values is not an array'
        );
        $manager->filter('category = %i', 1);
    }

    public function testOrder() {
        $manager = $this->getManager();

        $query = $manager->filter('action = "create"')
                         ->filter('`id` IN (1, 2)')
                         ->order('id', 'ASC');

        $results = $query->values();

        $this->assertCount(2, $results);
        $this->assertInstanceOf('FelixOnline\Core\AuditLog', $results[0]);
        $this->assertEquals($results[0]->getId(), 1);
    }

    public function testOrderMultiple() {
        $manager = $this->getManager();

        $query = $manager->order(array('id', 'key'), 'DESC');

        $sql = $manager->getSQL();

        $this->assertEquals($sql, "(SELECT `audit_log`.*
FROM `audit_log`
WHERE (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
)
ORDER BY `audit_log`.`id` DESC, `audit_log`.`key` DESC");
    }

    public function testOrderWithTable() {
        $manager = $this->getManager();

        $query = $manager->order('another_table.id', 'DESC');

        $sql = $manager->getSQL();

        $this->assertEquals($sql, "(SELECT `audit_log`.*
FROM `audit_log`
WHERE (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
)
ORDER BY another_table.id DESC");
    }

    public function testLimit() {
        $manager = $this->getManager();

        $query = $manager->filter('action = "create"')
        ->order('id', 'ASC');

        $query->limit(0, 1);

        $results = $query->values();

        $this->assertCount(1, $results);
    }

    public function testCount() {
        $manager = $this->getManager();

        $query = $manager->filter('action = "create"')
        ->filter('`id` IN (1, 2)')
        ->order('id', 'ASC');

        $count = $query->count();

        $this->assertEquals($count, 2);
    }

    public function testCountWithLimit() {
        $manager = $this->getManager();

        $query = $manager->filter('action = "create"')
        ->filter('`id` IN (1, 2)')
        ->order('id', 'ASC')
        ->limit(10, 10);

        $count = $query->count();

        $this->assertEquals($count, 2);
    }

    public function testQueryExceptionsBadQuery() {
        $manager = $this->getManager();

        $this->setExpectedException(
            'FelixOnline\Exceptions\SQLException'
        );
        $manager->filter('not valid sql')->values();
    }

    public function testQueryNoResults() {
        $manager = $this->getManager();

        $null = $manager->filter('id = 0')->values();

        $this->assertNull($null);
    }

    public function testGetOne() {
        $manager = $this->getManager();

        $one = $manager->filter('id = %i', array(1))
                       ->one();

        $this->assertInstanceOf('FelixOnline\Core\AuditLog', $one);
    }

    public function testGetOneMoreThanOne() {
        $manager = $this->getManager();

        $this->setExpectedException(
            'FelixOnline\Exceptions\InternalException',
            'More than one result'
        );

        $one = $manager->filter('action = "create"')
                       ->filter('`id` IN (1, 2)')
                       ->one();
    }

    public function testGetOneException() {
        $manager = $this->getManager();

        $this->setExpectedException(
            'FelixOnline\Exceptions\InternalException',
            'No results'
        );
        $manager->filter('id = 0')->one();
    }

    public function testJoin() {
        $m1 = $this->getManager();
        $m1->filter('date < "2010-12-31 23:59:59"');

        $m2 = $this->getManager();

        $m2->table = 'article_author';
        $m2->pk = 'article';

        $m2->filter('author = "%s"', array('felix'));

        $m1->join($m2);
        $m1->order('id', 'ASC');

        $sql = $m1->getSQL();

        $this->assertEquals($sql, '(SELECT `audit_log`.*
FROM `audit_log`
JOIN `article_author` ON ( `audit_log`.`id` = `article_author`.`article` )

WHERE `audit_log`.date < "2010-12-31 23:59:59"
AND (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
AND `article_author`.author = "felix"
AND (`article_author`.deleted = 0 OR `article_author`.deleted IS NULL)
)
ORDER BY `audit_log`.`id` ASC');
    }

    public function testNestedJoin() {
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

        $this->assertEquals($sql, '(SELECT `audit_log`.*
FROM `audit_log`
JOIN `category` ON ( `audit_log`.`category` = `category`.`id` )
JOIN `category_author` ON ( `category`.`id` = `category_author`.`category` )

WHERE `audit_log`.date < "2010-12-31 23:59:59"
AND (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
AND `category`.id = "1"
AND (`category`.deleted = 0 OR `category`.deleted IS NULL)
AND `category_author`.user = "pk1811"
AND (`category_author`.deleted = 0 OR `category_author`.deleted IS NULL)
)
ORDER BY `audit_log`.`id` ASC');
    }

    public function testLeftJoin() {
        $m1 = $this->getManager();
        $m2 = $this->getManager();

        $m2->table = 'article_author';
        $m2->pk = 'article';

        $m2->filter('author = "%s"', array('felix'));

        $m1->join($m2, 'LEFT');

        $sql = $m1->getSQL();

        $this->assertEquals($sql, '(SELECT `audit_log`.*
FROM `audit_log`
LEFT JOIN `article_author` ON ( `audit_log`.`id` = `article_author`.`article` )

WHERE (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
AND `article_author`.author = "felix"
AND (`article_author`.deleted = 0 OR `article_author`.deleted IS NULL)
)');
    }

    public function testLeftJoinSpecificColumn() {
        $m1 = $this->getManager();
        $m2 = $this->getManager();

        $m2->table = 'article_author';
        $m2->pk = 'article';

        $m2->filter('author = "%s"', array('felix'));

        $m1->join($m2, 'LEFT', 'TEST');

        $sql = $m1->getSQL();

        $this->assertEquals($sql, '(SELECT `audit_log`.*
FROM `audit_log`
LEFT JOIN `article_author` ON ( `audit_log`.`TEST` = `article_author`.`article` )

WHERE (`audit_log`.deleted = 0 OR `audit_log`.deleted IS NULL)
AND `article_author`.author = "felix"
AND (`article_author`.deleted = 0 OR `article_author`.deleted IS NULL)
)');
    }

    public function testJoinCount() {
        $m1 = $this->getManager();
        $m1->filter('action = "create"');

        $m2 = $this->getManager();

        $m2->table = 'article_author';
        $m2->pk = 'article';

        $m2->filter('author = "%s"', array('felix'));

        $m1->join($m2);
        $m1->order('id', 'ASC');

        $count = $m1->count();

        $this->assertEquals($count, 2);
    }

    public function testBuild() {
        $manager = \FelixOnline\Core\BaseManager::build(
            'FelixOnline\Core\Article',
            'article',
            'id'
        );

        $this->assertEquals($manager->class, 'FelixOnline\Core\Article');
        $this->assertEquals($manager->table, 'article');
        $this->assertEquals($manager->pk, 'id');
    }

    public function testCache() {
        $app = \FelixOnline\Core\App::getInstance();

        $manager = $this->getManager();
        $manager->cache(true);

        $selects = $app['db']->get_row("SHOW STATUS LIKE 'Com_select'")->Value;
        $this->assertEquals(0, (int) $selects);

        $all = $manager->all();

        $selects = $app['db']->get_row("SHOW STATUS LIKE 'Com_select'")->Value;
        // 1 for the initial SELECT, no need to initialize each model any more
        $this->assertEquals(1, (int) $selects);
        $this->assertCount(3, $all);
        $this->assertInstanceOf('FelixOnline\Core\AuditLog', $all[0]);

        $all = $manager->all();

        $selects = $app['db']->get_row("SHOW STATUS LIKE 'Com_select'")->Value;
        $this->assertEquals(1, (int) $selects);
        $this->assertCount(3, $all);
        $this->assertInstanceOf('FelixOnline\Core\AuditLog', $all[0]);
    }
}
