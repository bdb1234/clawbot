<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

/**
 * This will be our debug class for clawbot.  The class name is purposefully left short.
 */
class L
{
    /**
     * @var bool
     */
    private static $IS_DEBUG_ON = false;

    /**
     * For debug level messages.  Will only disply if $IS_DEBUG_ON === true
     *
     * @param string        $rawMessage
     * @param array         $extraArgs
     */
    public static function Debug($rawMessage, array $extraArgs = array())
    {
        if (!self::$IS_DEBUG_ON) return;

        self::Log('DEBUG', $rawMessage, $extraArgs);
    }

    /**
     * Fatal error message.  This will halt execution
     *
     * @param string        $rawMessage
     * @param array         $extraArgs
     */
    public static function Error($rawMessage, array $extraArgs = array())
    {
        self::Log('FATAL', $rawMessage, $extraArgs);
        exit;
    }

    /**
     * Log the message in a standard format
     *
     * @param string        $category
     * @param string        $rawMessage
     * @param array         $extraArgs
     */
    private static function Log($category, $rawMessage, array $extraArgs = array())
    {
        $argsStringBuilder  = array();
        foreach ($extraArgs as $key => $val) {
            $argsStringBuilder[] = "$key : $val";
        }
        $argsStringBuilder  = implode(" | ", $argsStringBuilder);

        if (!empty($argsStringBuilder)) {
            echo sprintf("%s -- %s -- %s\n", $category, $rawMessage, $argsStringBuilder);
            return;
        }

        echo sprintf("%s -- %s\n", $category, $rawMessage);
    }
}