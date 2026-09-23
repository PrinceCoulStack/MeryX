<?php

namespace App\Service\Messaging;

final class KeyWrapService
{
    public function __construct(private readonly MessageEncryptionService $encryptionService)
    {
    }

    public function wrapDataKey(string $dataKey, string $masterKey, string $keyVersion = 'v1'): array
    {
        $wrapped = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $dataKey,
            '',
            random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES),
            $this->normalizeKey($masterKey)
        );

        return [
            'wrappedKey' => base64_encode($wrapped),
            'keyVersion' => $keyVersion,
            'algorithm' => 'XChaCha20-Poly1305',
        ];
    }

    public function unwrapDataKey(string $wrappedKey, string $masterKey, string $keyVersion = 'v1'): string
    {
        $decoded = base64_decode($wrappedKey, true);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Wrapped key payload is invalid.');
        }

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $decoded,
            '',
            random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES),
            $this->normalizeKey($masterKey)
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Unable to unwrap conversation data key for version '.$keyVersion.'.');
        }

        return $plaintext;
    }

    private function normalizeKey(string $key): string
    {
        $digest = hash('sha256', $key, true);

        return $digest;
    }
}
