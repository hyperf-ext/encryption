<?php

declare(strict_types=1);

namespace HyperfExt\Encryption\Contract;

interface EncryptionInterface extends DriverInterface
{
    /**
     * Get a driver instance.
     *
     * @param string|null $name
     *
     * @return \HyperfExt\Encryption\Contract\DriverInterface
     */
    public function getDriver(?string $name = null): DriverInterface;
}
