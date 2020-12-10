<?php

/**
 * Copyright (c) 2020 Dennis Morhardt
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/gglnx/osf-parser
 */

namespace OsfParser;

use OsfParser\Exception\ParseException;

class Token
{
    private $value;
    private $type;
    private $lineNumber;

    public const T_HEADER = 1;
    public const T_HEADER_END = 2;
    public const T_COLON = 3;
    public const T_WHITESPACE = 4;
    public const T_TIMECODE = 5;
    public const T_UNIX_TIMESTAMP = 6;
    public const T_URL = 7;
    public const T_MARKDOWN_URL = 8;
    public const T_TEXT = 9;
    public const T_HASHTAG = 10;

    public function __construct(int $type, $value, int $lineNumber)
    {
        $this->type = $type;
        $this->value = $value;
        $this->lineNumber = $lineNumber;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLength(): int
    {
        return strlen($this->value);
    }
}
