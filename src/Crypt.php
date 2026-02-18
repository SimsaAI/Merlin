<?php

namespace Merlin;

/**
 * Simple encryption utility supporting Sodium and OpenSSL
 */
class Crypt
{
    /**
     * Supported ciphers
     */
    public const CIPHER_CHACHA20_POLY1305 = 'chacha20-poly1305';
    public const CIPHER_AES_256_GCM = 'aes-256-gcm';
    public const CIPHER_AUTO = 'auto';

    /**
     * Encrypt a value using the specified cipher
     *
     * @param string $value The value to encrypt
     * @param string $key The encryption key (at least 32 bytes recommended)
     * @param string $cipher The cipher to use: 'chacha20-poly1305', 'aes-256-gcm', or 'auto'
     * @return string Base64-encoded encrypted value
     * @throws Exception
     */
    public static function encrypt($value, $key, $cipher = self::CIPHER_AUTO)
    {
        if ($cipher === self::CIPHER_AUTO) {
            $cipher = self::getAvailableCipher();
        }

        if ($cipher === self::CIPHER_CHACHA20_POLY1305) {
            return self::encryptSodium($value, $key);
        } elseif ($cipher === self::CIPHER_AES_256_GCM) {
            return self::encryptOpenSSL($value, $key);
        } else {
            throw new Exception("Unsupported cipher: {$cipher}");
        }
    }

    /**
     * Decrypt a value using the specified cipher
     *
     * @param string $value The base64-encoded encrypted value
     * @param string $key The encryption key
     * @param string $cipher The cipher to use: 'chacha20-poly1305', 'aes-256-gcm', or 'auto'
     * @return string|null The decrypted value or null on failure
     * @throws Exception
     */
    public static function decrypt($value, $key, $cipher = self::CIPHER_AUTO)
    {
        if ($cipher === self::CIPHER_AUTO) {
            $cipher = self::getAvailableCipher();
        }

        if ($cipher === self::CIPHER_CHACHA20_POLY1305) {
            return self::decryptSodium($value, $key);
        } elseif ($cipher === self::CIPHER_AES_256_GCM) {
            return self::decryptOpenSSL($value, $key);
        } else {
            throw new Exception("Unsupported cipher: {$cipher}");
        }
    }

    /**
     * Check if Sodium is available
     *
     * @return bool
     */
    public static function hasSodium()
    {
        return function_exists('sodium_crypto_aead_chacha20poly1305_ietf_encrypt');
    }

    /**
     * Check if OpenSSL is available
     *
     * @return bool
     */
    public static function hasOpenSSL()
    {
        return function_exists('openssl_encrypt');
    }

    /**
     * Get the best available cipher (prefers Sodium over OpenSSL)
     *
     * @return string
     * @throws Exception
     */
    public static function getAvailableCipher()
    {
        if (self::hasSodium()) {
            return self::CIPHER_CHACHA20_POLY1305;
        } elseif (self::hasOpenSSL()) {
            return self::CIPHER_AES_256_GCM;
        } else {
            throw new Exception("No encryption library available (Sodium or OpenSSL required)");
        }
    }

    /**
     * Encrypt using Sodium (ChaCha20-Poly1305)
     *
     * @param string $value
     * @param string $key
     * @return string Base64-encoded: nonce + ciphertext
     * @throws Exception
     */
    protected static function encryptSodium($value, $key)
    {
        if (!self::hasSodium()) {
            throw new Exception("Sodium extension not available");
        }

        // Derive a proper key from the input key (Sodium requires 32 bytes)
        $derivedKey = hash('sha256', $key, true);

        // Generate a random nonce (12 bytes for ChaCha20-Poly1305-IETF)
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);

        // Encrypt with authenticated encryption
        $ciphertext = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
            $value,
            '',  // No additional data
            $nonce,
            $derivedKey
        );

        // Return base64-encoded: nonce + ciphertext
        return base64_encode($nonce . $ciphertext);
    }

    /**
     * Decrypt using Sodium (ChaCha20-Poly1305)
     *
     * @param string $value Base64-encoded encrypted value
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    protected static function decryptSodium($value, $key)
    {
        if (!self::hasSodium()) {
            throw new Exception("Sodium extension not available");
        }

        // Derive the same key
        $derivedKey = hash('sha256', $key, true);

        // Decode from base64
        $encrypted = base64_decode($value);
        if ($encrypted === false) {
            return null;
        }

        $nonceSize = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES;

        if (strlen($encrypted) <= $nonceSize) {
            return null;
        }

        // Extract nonce and ciphertext
        $nonce = substr($encrypted, 0, $nonceSize);
        $ciphertext = substr($encrypted, $nonceSize);

        // Decrypt
        $decrypted = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
            $ciphertext,
            '',  // No additional data
            $nonce,
            $derivedKey
        );

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Encrypt using OpenSSL (AES-256-GCM)
     *
     * @param string $value
     * @param string $key
     * @return string Base64-encoded: iv + tag + ciphertext
     * @throws Exception
     */
    protected static function encryptOpenSSL($value, $key)
    {
        if (!self::hasOpenSSL()) {
            throw new Exception("OpenSSL extension not available");
        }

        $method = 'aes-256-gcm';

        // Derive a proper key from the input key
        $derivedKey = hash('sha256', $key, true);

        // Generate IV
        $ivSize = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivSize);

        // Encrypt with GCM mode (authenticated encryption)
        $tag = '';
        $ciphertext = openssl_encrypt(
            $value,
            $method,
            $derivedKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',  // No additional data
            16   // Tag length
        );

        if ($ciphertext === false) {
            throw new Exception("OpenSSL encryption failed");
        }

        // Return base64-encoded: iv + tag + ciphertext
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt using OpenSSL (AES-256-GCM)
     *
     * @param string $value Base64-encoded encrypted value
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    protected static function decryptOpenSSL($value, $key)
    {
        if (!self::hasOpenSSL()) {
            throw new Exception("OpenSSL extension not available");
        }

        $method = 'aes-256-gcm';

        // Derive the same key
        $derivedKey = hash('sha256', $key, true);

        // Decode from base64
        $encrypted = base64_decode($value);
        if ($encrypted === false) {
            return null;
        }

        $ivSize = openssl_cipher_iv_length($method);
        $tagSize = 16;

        if (strlen($encrypted) <= $ivSize + $tagSize) {
            return null;
        }

        // Extract IV, tag, and ciphertext
        $iv = substr($encrypted, 0, $ivSize);
        $tag = substr($encrypted, $ivSize, $tagSize);
        $ciphertext = substr($encrypted, $ivSize + $tagSize);

        // Decrypt
        $decrypted = openssl_decrypt(
            $ciphertext,
            $method,
            $derivedKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $decrypted !== false ? $decrypted : null;
    }
}
