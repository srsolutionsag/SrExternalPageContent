<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Settings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ValuePacker
{
    private const GLUE = '|||||';
    private const STRING_PREFIX = 'string';
    private const ARRAY_PREFIX = 'array';
    private const INT_PREFIX = 'int';
    private const BOOL_PREFIX = 'bool';
    private const NULL_PREFIX = 'null';
    private const TRUE = 'true';
    private const FALSE = 'false';

    private function prefixPattern()
    {
        static $prefix_pattern;
        if ($prefix_pattern === null) {
            $prefix_pattern = '(' . implode('|', [
                    preg_quote(self::STRING_PREFIX, '/'),
                    preg_quote(self::ARRAY_PREFIX, '/'),
                    preg_quote(self::INT_PREFIX, '/'),
                    preg_quote(self::BOOL_PREFIX, '/'),
                    preg_quote(self::NULL_PREFIX, '/')
                ]) . ')';
        }
        return $prefix_pattern;
    }

    /**
     * @param mixed $value
     */
    public function pack($value): string
    {
        if (is_string($value)) {
            return self::STRING_PREFIX . self::GLUE . $value;
        }
        if (is_array($value)) {
            $value = $this->packRecursive($value);

            return self::ARRAY_PREFIX . self::GLUE . json_encode($value, JSON_THROW_ON_ERROR);
        }
        if (is_int($value)) {
            return self::INT_PREFIX . self::GLUE . $value;
        }
        if (is_bool($value)) {
            return self::BOOL_PREFIX . self::GLUE . ($value ? self::TRUE : self::FALSE);
        }
        if (is_null($value)) {
            return self::NULL_PREFIX . self::GLUE;
        }

        throw new \InvalidArgumentException(
            'Only strings, integers and arrays containing those values are allowed, ' . gettype($value) . ' given.'
        );
    }

    private function packRecursive(array $value): array
    {
        array_walk($value, function (&$item): void {
            $item = is_array($item) ? $this->packRecursive($item) : $this->pack($item);
        });
        return $value;
    }

    private function unprefix(string $value): array
    {
        $str = '/^' . $this->prefixPattern() . preg_quote(self::GLUE, '/') . '(.*)/is';
        if (!preg_match($str, $value, $matches)) {
            return [self::NULL_PREFIX, null];
        }

        return [$matches[1], $matches[2]];
    }

    /**
     * @return mixed[]|bool|int|string|null
     */
    public function unpack(?string $value)
    {
        // simple detection
        if ($value === null) {
            return null;
        }
        if ($value === self::NULL_PREFIX . self::GLUE) {
            return null;
        }

        // type detection
        [$type, $unprefixed_value] = $this->unprefix($value);

        switch ($type) {
            case self::STRING_PREFIX:
                return $unprefixed_value;
            case self::BOOL_PREFIX:
                return $unprefixed_value === self::TRUE;
            case self::ARRAY_PREFIX:
                $unprefixed_value = json_decode($unprefixed_value, true, 512);
                if (!is_array($unprefixed_value)) {
                    return null;
                }

                return $this->unpackRecursive($unprefixed_value);
            case self::INT_PREFIX:
                return (int) $unprefixed_value;
            default:
                return null;
        }

        return null;
    }

    private function unpackRecursive(array $value): array
    {
        array_walk($value, function (&$item): void {
            $item = is_array($item) ? $this->unpackRecursive($item) : $this->unpack($item);
        });
        return $value;
    }

}
