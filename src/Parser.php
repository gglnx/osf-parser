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

class Parser
{
    private const PATTERN = [
        Token::T_HEADER => '/^(HEAD(?:ER)?)/',
        Token::T_HEADER_END => '/^(\/HEAD(?:ER)?)/',
        Token::T_COLON => '/^(:)/',
        Token::T_WHITESPACE => '/^(\s+)/',
        Token::T_TIMECODE => '/^((?:\d\d?:)?(?:\d?\d:)(?:[0-5]\d)(?:.\d+)?)/',
        Token::T_UNIX_TIMESTAMP => '/^(\d+)/',
        Token::T_URL => '/^(<\S+:\S+>)/',
        Token::T_MARKDOWN_URL => '/^(\[.*\]\(\S+:\S+\))/',
        Token::T_TEXT => '/^(\S+)/',
        Token::T_HASHTAG => '/^(#\S+)/',
    ];

    private const ROOT_TOKEN = [
        Token::T_HEADER,
        Token::T_HEADER_END,
        Token::T_TIMECODE,
        Token::T_UNIX_TIMESTAMP,
        Token::T_TEXT,
    ];

    private const INLINE_HEADER_TOKEN = [
        Token::T_WHITESPACE,
        Token::T_COLON,
        Token::T_TEXT,
    ];

    private const INLINE_TOKEN = [
        Token::T_WHITESPACE,
        Token::T_URL,
        Token::T_MARKDOWN_URL,
        Token::T_HASHTAG,
        Token::T_TEXT,
    ];

    private $insideHeader = false;
    private $filename = null;

    public function parse(string $input, int $flags = 0)
    {
        // Split input into lines, increment key by 1, remove empty lines
        $lines = preg_split('/\r\n|\r|\n/', $input);
        $lines = array_combine(range(1, count($lines)), array_values($lines));
        $lines = array_filter($lines, function ($line) {
            return trim($line) !== '';
        });

        // Tokenize every line
        $tokens = [];
        foreach ($lines as $lineNumber => $line) {
            $offset = 0;

            // Process line
            while ($offset < strlen($line)) {
                $token = $this->tokenize($line, $lineNumber, $offset);

                if ($token === false) {
                    throw new ParseException(
                        'No valid Open Shownotes Format token found',
                        $lineNumber,
                        $line,
                        $this->filename
                    );
                }

                if ($token->getType() === Token::T_HEADER) {
                    if (count($tokens) > 0) {
                        throw new ParseException(
                            'Header tag middle in document',
                            $lineNumber,
                            $line,
                            $this->filename
                        );
                    }

                    $this->insideHeader = true;
                }

                if ($token->getType() === Token::T_HEADER_END) {
                    if (!$this->insideHeader) {
                        throw new ParseException(
                            'Header end tag before opening header tag',
                            $lineNumber,
                            $line,
                            $this->filename
                        );
                    }

                    $this->insideHeader = false;
                }

                $tokens[] = $token;
                $offset += $token->getLength();
            }
        }

        var_dump($tokens);
    }

    private function tokenize(string $line, int $lineNumber, int $offset)
    {
        $string = substr($line, $offset);

        // If offset is 0, check first for root token
        if ($offset === 0) {
            return $this->matchToken($string, $lineNumber, self::ROOT_TOKEN);
        }

        // If inside header, check for inline token
        if ($this->insideHeader) {
            return $this->matchToken($string, $lineNumber, self::INLINE_HEADER_TOKEN);
        }

        // Check for other inline tokens
        return $this->matchToken($string, $lineNumber, self::INLINE_TOKEN);
    }

    private function matchToken(string $string, int $lineNumber, array $tokenToCheck)
    {
        foreach ($tokenToCheck as $token) {
            if (preg_match(self::PATTERN[$token], $string, $matches)) {
                return new Token($token, $matches[0], $lineNumber);
            }
        }

        return false;
    }
}
