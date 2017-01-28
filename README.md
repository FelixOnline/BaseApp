# BaseApp

Felix Online reusable app framework.

BaseApp provides a database ORM and helper classes for Web and CLI applications.

[![Build Status](https://travis-ci.org/FelixOnline/BaseApp.png)](https://travis-ci.org/FelixOnline/BaseApp)

TODO: Documentation on gh-pages

## Database Concepts

### Models

Models are responsible for encapsulating everything to do with a particlar entity in Core, such as an Article or a Category. This mostly corresponds with a particular database table but a model can have relationships to other tables.

Each database model inherits off `BaseDB` and defines a list of fields which correspond to columns in a database table.

```php
namespace FelixOnline\Core;
use FelixOnline\Core\Type;

class Foo extends BaseDB
{
    public $dbtable = 'foo';

    public function __construct($id = NULL)
    {
        $fields = array(
            'text_field' => new Type\CharField(),
            'datetime' => new Type\DateTimeField(),
        );
        parent::__construct($fields, $id);
    }
}

// get a model that already exists
$foo = new Foo(1);

// Get model values
$foo->getTextField(); // 'text_field' column value
$foo->getDatetime(); // 'datetime' column as a timestamp

// or create an empty model
$fizz = new Foo();

// set values
$fizz->setTextField('Hello World');

// save the model to the database
$fizz->save();
```

You can also inherit off `BaseModel` if the model is not backed by a database table.

#### Foreign Keys

Model fields can be a relationship to another model through their primary key. If this is the case then when retrieving the value of that field you will get the model it correspondes to. You can also set a value it by passing the relational model as the parameter to the setter.

```php
namespace FelixOnline\Core;
use FelixOnline\Core\Type;

class Foo extends BaseDB
{
    public $dbtable = 'foo';

    public function __construct($id = NULL)
    {
        $fields = array(
            'bar' => new Type\ForeignKey('FelixOnline\Core\Bar'),
        );
        parent::__construct($fields, $id);
    }
}

class Bar extends BaseDB
{
    public $dbtable = 'bar';

    public function __construct($id = NULL)
    {
        $fields = array(
            'text' => new Type\CharField(),
        );
        parent::__construct($fields, $id);
    }
}

/**
 * Table: foo
 *
 * +----+------+
 * | id | bar  |
 * +----+------+
 * | 1  | 2    |
 * +----+------+
 *
 * Table: bar
 *
 * +----+--------+
 * | id | text   |
 * +----+--------+
 * | 1  | Fizz   |
 * +----+--------+
 * | 2  | Buzz   |
 * +----+--------+
 */

$foo = new Foo(1);

// get foreign key model
$bar = $foo->getBar(); // 'Bar' object
$bar->getText(); // 'Buzz'

// set a foreign key
$bar = new Bar(1);
$foo->setBar($bar); // set the 'bar' field on 'foo'
$foo->getBar()->getText(); // 'Fizz'
```

### Managers

Managers are responsible for selecting lists of models from the database with optional filtering. Some models have specific managers, like `ArticleManger` or `CatgoryManager` because they contain custom methods as well. However a generic manager representing a database table and model can be created on the fly using the builder method. See below.

```php
$manager = (new FelixOnline\Core\ArticleManager())    ->filter('published < NOW()')
    ->order('published', 'DESC');

$manager->count(); // get the number of models the manager will return

$manager->limit(0, 10); // limit the number of models

$manager->values(); // get an array of models

// You can also join managers together
$authorManager = (new \FelixOnline\Core\ArticleAuthorManager())
    ->filter("author = 'felix'");
$manager->join($authorManager);

$manager->values(); // articles by the user 'felix'
```

#### Manager builder

You can create managers on the fly by using the static method `build` on the `BaseManager` class.

```php
$author_manager = BaseManager::build(
    'FelixOnline\Core\User', // model class
    'article_author', // database table
    'author' // primary key
);

$author_manager->filter('article = %i', array(1))
    ->values();
```


## Tests

* Run `composer install` to install all dependencies
* Run `./vendor/bin/phpunit tests` in the root folder to run the tests
