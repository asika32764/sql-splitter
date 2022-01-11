# SQL Splitter as Stream/Iterator

This is a class that can split BIG SQL file or string as iterator so that can help us save memory when importing SQL to database.

## Install

```bash
composer require asika/sql-splitter
```

## Usage

```php
use Asika\SqlSplitter\SqlSplitter;

$it = SqlSplitter::splitFromFile(__DIR__ . '/path/to/db.sql');

// Loop iterator
foreach ($it as $query) {
    if (trim($query) !== '') {
      $db->prepare($query)->execute();
    }
}

// Or just convert to array
$queries = iterator_to_array($it);

// Available methods
SqlSplitter::splitSqlString('...');
SqlSplitter::splitFromFile('path/to/fil.sql');
SqlSplitter::splitFromPsr7Stream(new Stream('zip://file.zip#backup.sql'));
SqlSplitter::splitFromStream(fopen('s3://...', 'r'));
```

> To use PSR-7 Stream, you must install `psr/http-message`
