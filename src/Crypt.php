<?php

declare(strict_types=1);

namespace HyperfExt\Encryption;

use HyperfExt\Encryption\Contract\DriverInterface;
use HyperfExt\Encryption\Contract\EncryptionInterface;
use Hyperf\Utils\ApplicationContext;

abstract class Crypt
{
    public static function getDriver(?string $name = null): DriverInterface
    {
        return ApplicationContext::getContainer()->get(EncryptionInterface::class)->getDriver($name);
    }

    public static function encrypt($value, bool $serialize = true, ?string $driverName = null): string
    {
        return static::getDriver($driverName)->encrypt($value, $serialize);
    }

    public static function decrypt(string $payload, bool $unserialize = true, ?string $driverName = null)
    {
        return static::getDriver($driverName)->decrypt($payload, $unserialize);
    }
}
