<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/encryption.
 *
 * @link     https://github.com/hyperf-ext/encryption
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/encryption/blob/master/LICENSE
 */
namespace HyperfExt\Encryption;

use Hyperf\Utils\ApplicationContext;
use HyperfExt\Encryption\Contract\DriverInterface;
use HyperfExt\Encryption\Contract\EncryptionInterface;

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
