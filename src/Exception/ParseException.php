<?php

/**
 * Copyright (c) 2020 Dennis Morhardt
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/gglnx/osf-parser
 */

namespace OsfParser\Exception;

use RuntimeException;
use Throwable;

final class ParseException extends RuntimeException
{
    private $parsedFile;
    private $parsedLine;
    private $rawLineText;

    public function __construct(
        string $message,
        int $parsedLine = -1,
        string $rawLineText = null,
        string $parsedFile = null,
        Throwable $previous = null
    ) {
        $this->parsedFile = $parsedFile;
        $this->parsedLine = $parsedLine;
        $this->rawLineText = $rawLineText;
        $this->message = $message;

        if (null !== $this->parsedFile) {
            $this->message .= sprintf(' in %s', $this->parsedFile);
        }

        if ($this->parsedLine >= 0) {
            $this->message .= sprintf(' at line %d', $this->parsedLine);
        }

        if ($this->rawLineText) {
            $this->message .= sprintf(': %s', $this->rawLineText);
        }

        parent::__construct($this->message, 0, $previous);
    }

    public function getRawLineText()
    {
        return $this->rawLineText;
    }

    public function getParsedFile()
    {
        return $this->parsedFile;
    }

    public function getParsedLine()
    {
        return $this->parsedLine;
    }
}
