<?php

declare(strict_types=1);

namespace HyperfExt\Encryption\Contract;

interface DriverInterface
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @param bool  $serialize
     *
     * @return string
     *
     * @throws \HyperfExt\Encryption\Exception\EncryptException
     */
    public function encrypt($value, bool $serialize = true): string;

    /**
     * Decrypt the given value.
     *
     * @param string $payload
     * @param bool   $unserialize
     *
     * @return mixed
     *
     * @throws \HyperfExt\Encryption\Exception\DecryptException
     */
    public function decrypt(string $payload, bool $unserialize = true);
}
