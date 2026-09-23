<?php

namespace App\Service\Messaging;

final class MessageEncryptionService
{
    public const ALGORITHM = 'XChaCha20-Poly1305';
    public const KEY_VERSION = 'v1';

    public function encrypt(string $plaintext, string $key): array
    {
        $normalizedKey = $this->normalizeKey($key);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            '',
            $nonce,
            $normalizedKey
        );

        return [
            'ciphertext' => base64_encode($ciphertext),
            'nonce' => base64_encode($nonce),
            'algorithm' => self::ALGORITHM,
            'keyVersion' => self::KEY_VERSION,
            'contentPreview' => '[encrypted content]',
        ];
    }

    public function decrypt(string $ciphertext, string $nonce, string $key): string
    {
        $normalizedKey = $this->normalizeKey($key);
        $decodedCiphertext = base64_decode($ciphertext, true);
        $decodedNonce = base64_decode($nonce, true);

        if ($decodedCiphertext === false || $decodedNonce === false) {
            throw new \InvalidArgumentException('Invalid encrypted payload or nonce.');
        }

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $decodedCiphertext,
            '',
            $decodedNonce,
            $normalizedKey
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Failed to decrypt message content.');
        }

        return $plaintext;
    }

    public function generateDataKey(): string
    {
        return random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
    }

    public function contentPreview(string $plaintext): string
    {
        if ($plaintext === '') {
            return '[encrypted content]';
        }

        $clean = trim((string) preg_replace('/\s+/', ' ', strip_tags($plaintext)));
        if ($clean === '') {
            return '[encrypted content]';
        }

        return '[encrypted content]';
    }

    private function normalizeKey(string $key): string
    {
        $binary = preg_match('/^[0-9a-fA-F]+$/', $key) === 1
            ? hex2bin($key)
            : $key;

        if ($binary === false || strlen($binary) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            $digest = hash('sha256', $key, true);

            return $digest;
        }

        return $binary;
    }
}
