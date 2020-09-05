<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/encryption.
 *
 * @link     https://github.com/hyperf-ext/encryption
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/encryption/blob/master/LICENSE
 */
namespace HyperfExt\Encryption\Contract;

interface DriverInterface
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     *
     * @throws \HyperfExt\Encryption\Exception\EncryptException
     */
    public function encrypt($value, bool $serialize = true): string;

    /**
     * Decrypt the given value.
     *
     * @throws \HyperfExt\Encryption\Exception\DecryptException
     * @return mixed
     */
    public function decrypt(string $payload, bool $unserialize = true);
}
