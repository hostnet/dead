<?php

/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class SettingsException extends \Exception
{
    const NOT_PRESENT = 0;
    const INVALID     = 1;

    public function __construct($setting, $type = self::NOT_PRESENT, $previous = null)
    {
        assert(is_string($setting));
        assert(is_numeric($type) && $type >= self::NOT_PRESENT && $type <= self::INVALID);
        assert($previous instanceof Exception || $previous === null);

        switch ($type) {
            case self::NOT_PRESENT:
                $message = "Setting $setting is not present";
                break;
            case self::INVALID:
                $message = "Setting $setting is invalid";
                break;
        }

        parent::__construct($message, 0, $previous);
    }
}
