<?php

require_once __DIR__ . '/../../AppTestCase.php';

use FelixOnline\Base\Type\IntegerField;
use FelixOnline\Base\Type\CharField;

class BaseDBTest extends AppTestCase
{
    public $fixtures = array(
        'articles',
        'users',
        'audit_log'
    );

    public function testInit()
    {
        $model = new \FelixOnline\Base\BaseDB(array(
            'title' => new \FelixOnline\Base\Type\CharField()
        ), 1, 'article');

        $this->assertEquals($model->getTitle(), 'Fighting for Libel Reform');
    }

    public function testAssignedPrimaryKey()
    {
        $model = new \FelixOnline\Base\BaseDB(array(
            'user' => new \FelixOnline\Base\Type\CharField(array(
                'primary' => true
            ))
        ), 'felix', 'user');

        $this->assertEquals($model->getUser(), 'felix');
    }

    public function testNoModelFoundException()
    {
        $this->setExpectedException(
            'FelixOnline\Exceptions\ModelNotFoundException',
            'No model in database'
        );
        $model = new \FelixOnline\Base\BaseDB(array(
            'title' => new \FelixOnline\Base\Type\CharField()
        ), 0, 'article');
    }

    public function testConstructSelectSQL()
    {
        $model = $this->mock('FelixOnline\\Base\\BaseDB')
            ->new();

        $model->this()->dbtable = 'test';
        $model->this()->pk = 'id';

        $fields = array(
            'id' => (new IntegerField())->setValue(1),
            'title' => new CharField(),
        );

        $this->assertEquals(
            $model->constructSelectSQL($fields),
            "SELECT `id`, `title` FROM `test` WHERE `id` = 1"
        );
    }

    public function testConstructInsertSQL()
    {
        $model = $this->mock('FelixOnline\\Base\\BaseDB')
            ->new();

        $model->this()->dbtable = 'test';
        $model->this()->pk = 'id';

        $fields = array(
            'foo' => (new CharField())->setValue('bar'), // string
            'fizz' => (new IntegerField())->setValue(1), // number
            'buzz' => (new CharField())->setValue(NULL), // null
            'empty' => (new CharField())->setValue(''), // empty
        );

        $this->assertEquals(
            $model->constructInsertSQL($fields),
            "INSERT INTO `test` ( `foo`, `fizz`, `buzz`, `empty` ) VALUES ( 'bar', 1, NULL, '' )"
        );
    }

    public function testConstructUpdateSQL()
    {
        $model = $this->mock('FelixOnline\\Base\\BaseDB')
            ->new();

        $model->this()->dbtable = 'test';
        $model->this()->pk = 'id';
        $model->this()->constructorId = '1';

        $fields = array(
            'id' => (new IntegerField())->setValue(1),
            'foo' => (new CharField())->setValue('bar'), // string
            'fizz' => (new IntegerField())->setValue(1), // number
            'buzz' => (new CharField())->setValue(NULL), // null
            'empty' => (new CharField())->setValue(''), // empty
        );

        $changed = array(
            'foo' => (new CharField())->setValue('fizz'), // string
        );

        $this->assertEquals(
            $model->constructUpdateSQL($changed, $fields),
            "UPDATE `test` SET `foo`='fizz' WHERE `id`='1'"
        );
    }

    public function testSave()
    {
        $this->assertEquals(3, $this->getConnection()->getRowCount('user'));

        $user = new \FelixOnline\Base\BaseDB(array(
            'user' => (new CharField(array('primary' => true)))->setValue('test'),
            'name' => (new CharField())->setValue('Joe Blogs'),
            'role' => (new IntegerField())->setValue(10),
        ), null, 'user');

        $user->save();

        $this->assertEquals(4, $this->getConnection()->getRowCount('user'));
    }

    public function testDelete()
    {
        $this->assertEquals(3, $this->getConnection()->getRowCount('user'));

        $user = new \FelixOnline\Base\BaseDB(array(
            'user' => new CharField(array('primary' => true)),
            'name' => new CharField(),
            'role' => new IntegerField(),
            'info' => new CharField(),
        ), 'felix', 'user');

        $user->delete();

        $this->assertEquals(3, $this->getConnection()->getRowCount('user'));

        $this->setExpectedException('FelixOnline\Exceptions\ModelNotFoundException', 'This model has been deleted');

        $user = new \FelixOnline\Base\BaseDB(array(
            'user' => new CharField(array('primary' => true)),
            'name' => new CharField(),
            'role' => new IntegerField(),
            'info' => new CharField(),
        ), 'felix', 'user');
    }

    public function testSaveArticle()
    {
        $this->assertEquals(4, $this->getConnection()->getRowCount('article'));

        $user = new \FelixOnline\Base\BaseDB(array(
            'title' => (new CharField())->setValue('test'),
            'teaser' => (new CharField())->setValue('test'),
            'category' => (new IntegerField())->setValue(1),
            'text1' => (new IntegerField())->setValue(1),
        ), null, 'article');

        $user->save();

        $this->assertEquals(5, $this->getConnection()->getRowCount('article'));
    }

    public function testSaveUpdate()
    {
        $this->assertEquals(3, $this->getConnection()->getRowCount('user'));

        $user = new \FelixOnline\Base\BaseDB(array(
            'user' => new CharField(array('primary' => true)),
            'name' => new CharField(),
            'role' => new IntegerField(),
            'info' => new CharField(),
        ), 'felix', 'user');

        $user->setInfo('Foo bar');
        $user->save();

        $this->assertEquals(3, $this->getConnection()->getRowCount('user'));

        $pdo = $this->getConnection()->getConnection();
        $sth = $pdo->prepare("SELECT info FROM user WHERE user = 'felix'");
        $sth->execute();
        $info = $sth->fetchColumn();
        $this->assertEquals($info, 'Foo bar');
    }

    public function testSaveNoFieldsException()
    {
        $this->setExpectedException('FelixOnline\Exceptions\InternalException', 'No fields defined');

        $model = new \FelixOnline\Base\BaseDB(array());
    }

    public function testSaveNoDbtableException()
    {
        $this->setExpectedException('FelixOnline\Exceptions\InternalException', 'No table specified');

        $model = new \FelixOnline\Base\BaseDB(array(
            'foo' => 'bar'
        ));
    }
}
