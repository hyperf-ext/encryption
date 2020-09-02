<?php

declare(strict_types=1);

namespace HyperfExt\Encryption\Driver;

use Hyperf\Utils\Str;
use HyperfExt\Encryption\Contract\SymmetricDriverInterface;
use HyperfExt\Encryption\Exception\DecryptException;
use HyperfExt\Encryption\Exception\EncryptException;
use RuntimeException;

class AesDriver implements SymmetricDriverInterface
{
    /**
     * The encryption key.
     *
     * @var string
     */
    protected $key;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher;

    /**
     * Create a new encrypter instance.
     *
     * @param array $options
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct(array $options = [])
    {
        $key = base64_decode((string)($options['key'] ?? ''));
        $cipher = (string)($options['cipher'] ?? 'AES-128-CBC');

        if (static::supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param string $key
     * @param string $cipher
     *
     * @return bool
     */
    public static function supported(string $key, string $cipher): bool
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
            ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Create a new encryption key for the given cipher.
     *
     * @param array $options
     *
     * @return string
     */
    public static function generateKey(array $options = []): string
    {
        $cipher = $options['cipher'] ?? 'AES-128-CBC';
        return base64_encode(random_bytes($cipher === 'AES-128-CBC' ? 16 : 32));
    }

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
    public function encrypt($value, bool $serialize = true): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));

        // First we will encrypt the value using OpenSSL. After this is encrypted we
        // will proceed to calculating a MAC for the encrypted value so that this
        // value can be verified later as not having been changed by the users.
        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $this->key, 0, $iv
        );

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        // Once we get the encrypted value we'll go ahead and base64_encode the input
        // vector and create the MAC for the encrypted value so we can then verify
        // its authenticity. Then, we'll JSON the data into the "payload" array.
        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'), JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

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
    public function decrypt(string $payload, $unserialize = true)
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller. If we are
        // unable to decrypt this value we will throw out an exception message.
        $decrypted = \openssl_decrypt(
            $payload['value'], $this->cipher, $this->key, 0, $iv
        );

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Create a MAC for the given value.
     *
     * @param string $iv
     * @param mixed  $value
     *
     * @return string
     */
    protected function hash(string $iv, $value): string
    {
        return hash_hmac('sha256', $iv . $value, $this->key);
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param string $payload
     *
     * @return array
     *
     * @throws \HyperfExt\Encryption\Exception\DecryptException
     */
    protected function getJsonPayload(string $payload): array
    {
        $payload = json_decode(base64_decode($payload), true);

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value. We'll also check the MAC for this encryption.
        if (!$this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }

        if (!$this->validMac($payload)) {
            throw new DecryptException('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param mixed $payload
     *
     * @return bool
     */
    protected function validPayload($payload): bool
    {
        try {
            return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
                strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
        } catch (\Throwable $exception) {
        }
        return false;
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param array $payload
     *
     * @return bool
     */
    protected function validMac(array $payload): bool
    {
        $calculated = $this->calculateMac($payload, $bytes = random_bytes(16));

        return hash_equals(
            hash_hmac('sha256', $payload['mac'], $bytes, true), $calculated
        );
    }

    /**
     * Calculate the hash of the given payload.
     *
     * @param array  $payload
     * @param string $bytes
     *
     * @return string
     */
    protected function calculateMac(array $payload, string $bytes)
    {
        return hash_hmac(
            'sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true
        );
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
