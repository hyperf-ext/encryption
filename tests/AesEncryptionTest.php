<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/encryption.
 *
 * @link     https://github.com/hyperf-ext/encryption
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/encryption/blob/master/LICENSE
 */
namespace HyperfTest;

use HyperfExt\Encryption\Driver\AesDriver;
use HyperfExt\Encryption\Exception\DecryptException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class AesEncryptionTest extends TestCase
{
    public function testEncryption()
    {
        $e = new AesDriver([
            'key' => base64_encode(str_repeat('a', 16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $encrypted = $e->encrypt('foo');
        $this->assertNotSame('foo', $encrypted);
        $this->assertSame('foo', $e->decrypt($encrypted));
    }

    public function testEncryptionUsingBase64EncodedKey()
    {
        $e = new AesDriver([
            'key' => base64_encode(random_bytes(16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $encrypted = $e->encrypt('foo');
        $this->assertNotSame('foo', $encrypted);
        $this->assertSame('foo', $e->decrypt($encrypted));
    }

    public function testEncryptedLengthIsFixed()
    {
        $e = new AesDriver([
            'key' => base64_encode(str_repeat('a', 16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $lengths = [];
        for ($i = 0; $i < 100; ++$i) {
            $lengths[] = strlen($e->encrypt('foo'));
        }
        $this->assertSame(min($lengths), max($lengths));
    }

    public function testWithCustomCipher()
    {
        $e = new AesDriver([
            'key' => base64_encode(str_repeat('b', 32)),
            'cipher' => 'AES-256-CBC',
        ]);
        $encrypted = $e->encrypt('bar');
        $this->assertNotSame('bar', $encrypted);
        $this->assertSame('bar', $e->decrypt($encrypted));

        $e = new AesDriver([
            'key' => base64_encode(random_bytes(32)),
            'cipher' => 'AES-256-CBC',
        ]);
        $encrypted = $e->encrypt('foo');
        $this->assertNotSame('foo', $encrypted);
        $this->assertSame('foo', $e->decrypt($encrypted));
    }

    public function testDoNoAllowLongerKey()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');

        new AesDriver([
            'key' => base64_encode(str_repeat('z', 32)),
            'cipher' => 'AES-128-CBC',
        ]);
    }

    public function testWithBadKeyLength()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');

        new AesDriver([
            'key' => base64_encode(str_repeat('a', 5)),
            'cipher' => 'AES-128-CBC',
        ]);
    }

    public function testWithBadKeyLengthAlternativeCipher()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');

        new AesDriver([
            'key' => base64_encode(str_repeat('a', 16)),
            'cipher' => 'AES-256-CFB8',
        ]);
    }

    public function testWithUnsupportedCipher()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');

        new AesDriver([
            'key' => base64_encode(str_repeat('c', 16)),
            'cipher' => 'AES-256-CFB8',
        ]);
    }

    public function testExceptionThrownWhenPayloadIsInvalid()
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('The payload is invalid.');

        $e = new AesDriver([
            'key' => base64_encode(str_repeat('a', 16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $payload = $e->encrypt('foo');
        $payload = str_shuffle($payload);
        $e->decrypt($payload);
    }

    public function testExceptionThrownWithDifferentKey()
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('The MAC is invalid.');

        $a = new AesDriver([
            'key' => base64_encode(str_repeat('a', 16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $b = new AesDriver([
            'key' => base64_encode(str_repeat('b', 16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $b->decrypt($a->encrypt('baz'));
    }

    public function testExceptionThrownWhenIvIsTooLong()
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('The payload is invalid.');

        $e = new AesDriver([
            'key' => base64_encode(str_repeat('a', 16)),
            'cipher' => 'AES-128-CBC',
        ]);
        $payload = $e->encrypt('foo');
        $data = json_decode(base64_decode($payload), true);
        $data['iv'] .= $data['value'][0];
        $data['value'] = substr($data['value'], 1);
        $modified_payload = base64_encode(json_encode($data));
        $e->decrypt($modified_payload);
    }
}
