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
                    'description' => 'The config for hyperf-ext/encryption.',
                    'source' => __DIR__ . '/../publish/encryption.php',
                    'destination' => BASE_PATH . '/config/autoload/encryption.php',
                ],
            ],
        ];
    }
}
