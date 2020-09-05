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

interface SymmetricDriverInterface extends DriverInterface
{
    public function getKey(): string;

    public static function generateKey(array $options = []): string;
}
