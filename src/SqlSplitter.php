<?php

/**
 * Part of SqlSplitter project.
 *
 * @copyright  Copyright (C) 2020 Simon Asika.
 * @license    MIT
 */

namespace Asika\SqlSplitter;

use Psr\Http\Message\StreamInterface;

/**
 * The SqlSplitter
 */
class SqlSplitter
{
    /**
     * Split from sql file as iterator.
     *
     * @param  string  $file
     *
     * @return  \Generator
     */
    public static function splitFromFile(string $file): \Generator
    {
        return static::splitFromStream(fopen($file, 'rb'));
    }

    /**
     * Split sql string as iterator.
     *
     * @param  string  $sql
     *
     * @return  \Generator
     */
    public static function splitSqlString(string $sql): \Generator
    {
        return static::splitFromStream(static::stringToStream($sql));
    }

    /**
     * Split from PSR7 Stream Interface
     *
     * @param  StreamInterface  $stream
     *
     * @return  \Generator
     */
    public static function splitFromPsr7Stream(StreamInterface $stream): \Generator
    {
        if (!$stream->isSeekable()) {
            return static::splitFromStream(static::stringToStream((string) $stream->getContents()));
        }

        return (static function (StreamInterface $stream): \Generator {
            $start = 0;
            $open  = false;
            $char  = '';
            $end   = $stream->getSize();
            $i     = 0;

            while (!$stream->eof()) {
                $current = $stream->read(1);

                if (($current === '"' || $current === '\'')) {
                    $n = 2;

                    while ($stream->seek($i - $n + 1) === '\\' && $n < $i) {
                        $n++;
                    }

                    if ($n % 2 === 0) {
                        if ($open) {
                            if ($current === $char) {
                                $open = false;
                                $char = '';
                            }
                        } else {
                            $open = true;
                            $char = $current;
                        }
                    }
                }

                if (($current === ';' && !$open) || $i === $end - 1) {
                    $stream->seek($start);
                    $query = $stream->read($i - $start + 1);

                    if (trim($query) !== '') {
                        yield $query;
                    }

                    $start = $i + 1;
                }

                // fseek() will always set feof to FALSE, we must read file once to make pointer move to next position.
                $stream->seek($i);
                $stream->read(1);

                $i++;
            }
        })($stream);
    }

    /**
     * Split sql from stream resource.
     *
     * @param  resource  $fp
     *
     * @return  \Generator
     */
    public static function splitFromStream($fp): \Generator
    {
        if (!is_resource($fp)) {
            throw new \InvalidArgumentException('Argument 1 must be a resource.');
        }

        if (!stream_get_meta_data($fp)['seekable']) {
            $fp = static::stringToStream((string) stream_get_contents($fp));
        }

        return (static function ($fp): \Generator {
            $start = 0;
            $open  = false;
            $char  = '';
            $end   = fstat($fp)['size'];
            $i     = 0;

            while (!feof($fp)) {
                $current = fread($fp, 1);

                if (($current === '"' || $current === '\'')) {
                    $n = 2;

                    while (fseek($fp, $i - $n + 1) === '\\' && $n < $i) {
                        $n++;
                    }

                    if ($n % 2 === 0) {
                        if ($open) {
                            if ($current === $char) {
                                $open = false;
                                $char = '';
                            }
                        } else {
                            $open = true;
                            $char = $current;
                        }
                    }
                }

                if (($current === ';' && !$open) || $i == $end - 1) {
                    fseek($fp, $start);
                    $query = fread($fp, $i - $start + 1);

                    if (trim($query) !== '') {
                        yield $query;
                    }

                    $start = $i + 1;
                }

                // fseek() will always set feof to FALSE, we must read file once to make pointer move to next position.
                fseek($fp, $i);
                fread($fp, 1);
                $i++;
            }
        })($fp);
    }

    /**
     * Convert string to stream by using php://memory
     *
     * @param  string  $string
     *
     * @return  resource
     */
    private static function stringToStream(string $string)
    {
        $fp = fopen('php://memory', 'rb+');

        fwrite($fp, $string);

        rewind($fp);

        return $fp;
    }
}
