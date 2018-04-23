<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Version
{

    const SMALLER = -1;
    const EQUAL   = 0;
    const BIGGER  = 1;

    /**
     *
     * @param string $first
     * @param string $second
     * @param string $delimiter
     * @return int
     */
    public static function compare($first, $second, $delimiter = '.')
    {
        assert(is_string($first));
        assert(is_string($second));
        assert(is_string($delimiter));

        $first  = explode($delimiter, $first);
        $second = explode($delimiter, $second);

        $r = self::EQUAL;
        foreach ($first as $i => $value) {
            if ($value > $second[$i]) {
                $r = self::BIGGER;
                break;
            } elseif ($value < $second[$i]) {
                $r = self::SMALLER;
                break;
            }
        }

        return $r;
    }
}
