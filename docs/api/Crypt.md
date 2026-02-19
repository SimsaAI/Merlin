# 🧩 Merlin\Crypt

Simple encryption utility supporting Sodium and OpenSSL

## 📌 Constants

- **CIPHER_CHACHA20_POLY1305** = `'chacha20-poly1305'`
- **CIPHER_AES_256_GCM** = `'aes-256-gcm'`
- **CIPHER_AUTO** = `'auto'`

## 🚀 Public methods

### `encrypt()`

`public static function encrypt($value, $key, $cipher = 'auto') : mixed`

Encrypt a value using the specified cipher

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | `🎲 mixed` | `` | The value to encrypt |
| `$key` | `🎲 mixed` | `` | The encryption key (at least 32 bytes recommended) |
| `$cipher` | `🎲 mixed` | `'auto'` | The cipher to use: 'chacha20-poly1305', 'aes-256-gcm', or 'auto' |

**➡️ Return value**

- Type: `mixed`
- Description: Base64-encoded encrypted value

**⚠️ Throws**

- \Exception 

### `decrypt()`

`public static function decrypt($value, $key, $cipher = 'auto') : mixed`

Decrypt a value using the specified cipher

**🧭 Parameters**

| Name | Type | Default | Description |
|---|---|---|---|
| `$value` | `🎲 mixed` | `` | The base64-encoded encrypted value |
| `$key` | `🎲 mixed` | `` | The encryption key |
| `$cipher` | `🎲 mixed` | `'auto'` | The cipher to use: 'chacha20-poly1305', 'aes-256-gcm', or 'auto' |

**➡️ Return value**

- Type: `mixed`
- Description: The decrypted value or null on failure

**⚠️ Throws**

- \Exception 

### `hasSodium()`

`public static function hasSodium() : mixed`

Check if Sodium is available

**➡️ Return value**

- Type: `mixed`

### `hasOpenSSL()`

`public static function hasOpenSSL() : mixed`

Check if OpenSSL is available

**➡️ Return value**

- Type: `mixed`

### `getAvailableCipher()`

`public static function getAvailableCipher() : mixed`

Get the best available cipher (prefers Sodium over OpenSSL)

**➡️ Return value**

- Type: `mixed`

**⚠️ Throws**

- \Exception 

