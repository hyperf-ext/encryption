<?php

declare(strict_types=1);

namespace HyperfExt\Encryption;

use HyperfExt\Encryption\Command\GenKeyCommand;
use HyperfExt\Encryption\Contract\EncryptionInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EncryptionInterface::class => EncryptionManager::class,
            ],
            'commands' => [
                GenKeyCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for HyperfExt\\Encryption.',
                    'source' => __DIR__ . '/../publish/ext-encryption.php',
                    'destination' => BASE_PATH . '/config/autoload/ext-encryption.php',
                ],
            ],
        ];
    }
}
