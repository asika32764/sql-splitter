<?php

/**
 * Part of sql-splitter project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    __LICENSE__
 */

namespace Asika\SqlSplitter\Test;

use Asika\SqlSplitter\SqlSplitter;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Stream\Stream;
use Windwalker\Http\Stream\StringStream;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The SqlSplitterTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SqlSplitterTest extends TestCase
{
    use BaseAssertionTrait;

    public function testSplitFromStream()
    {
        $this->assertQueries(
            SqlSplitter::splitFromStream(fopen(self::getFilePath(), 'rb'))
        );
    }

    public function testSplitFromFile()
    {
        $this->assertQueries(
            SqlSplitter::splitFromFile(self::getFilePath())
        );
    }

    public function testSplitSqlString()
    {
        $this->assertQueries(
            SqlSplitter::splitSqlString(file_get_contents(self::getFilePath()))
        );
    }

    public function testSplitFromPsr7Stream()
    {
        $this->assertQueries(
            SqlSplitter::splitFromPsr7Stream(new Stream(self::getFilePath()))
        );
        $this->assertQueries(
            SqlSplitter::splitFromPsr7Stream(new StringStream(file_get_contents(self::getFilePath())))
        );
    }

    public function assertQueries(\Generator $queries)
    {
        $queries = iterator_to_array($queries);

        self::assertStringDataEquals(
            'SELECT * FROM "foo" WHERE "flower" = \'Sakura\';',
            $queries[0]
        );

        self::assertStringDataEquals(
            '-- Below is a string contains \';\'
INSERT INTO "foo" ("id", "flower", "title") VALUES (1, \'Sakura\', \'S; ku; ra\');',
            $queries[1]
        );

        self::assertStringDataEquals(
            '-- Empty line
;',
            $queries[2]
        );

        self::assertStringDataEquals(
            '# Hash comments
INSERT INTO "foo" ("id", "flower", "title") VALUES (2, \'Rose\', \'Rose\');',
            $queries[3]
        );
    }

    public static function getFilePath(): string
    {
        return __DIR__ . '/fixtures/test.sql';
    }
}
