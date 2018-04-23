<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class GraphTaskInterface implements TaskInterface
{

    public function run()
    {
        $settings = Settings::instance()->getCommand();

        switch ($settings->getCommandName()) {
            case "saturation":
                $t = new SaturationGraphTask();
                break;
            default:
                $settings->displayUsage();
        }

        $t->run();
    }
}
