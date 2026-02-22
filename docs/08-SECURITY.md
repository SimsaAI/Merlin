# Security

**Keep your application secure** - Understand Merlin's security features including SQL injection protection, CSRF tokens, password hashing, encryption helpers, and secure session management. Learn security best practices and common pitfalls to avoid.

## SQL Injection Protection

Merlin supports two ways of passing values into queries:

### Bound parameters

Only values passed through ->bind() remain real PDO parameters and offer full SQL‑injection protection.

```php
User::query()
    ->where('email = :email')
    ->bind(['email' => $email])
    ->firstModel();
```

### Inline values

Values passed directly to where() are safely escaped and inserted into the SQL string, but not bound as real parameters.

```php
User::query()
    ->where('email = :email', ['email' => $email])
    ->firstModel();
```

Use bind() for any user‑provided or dynamic input.

## Password Storage

Never store passwords in plain text. Always use PHP's built-in password hashing functions which automatically handle salting and use strong algorithms like bcrypt.

Use PHP password API for credentials:

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
$isValid = password_verify($plainPassword, $hash);
```

## Cookies and Encryption

When storing sensitive data in cookies (like session tokens or remember-me tokens), use encryption to prevent tampering and information disclosure.

Use `Merlin\Http\Cookie` with encryption when storing sensitive values.

```php
use Merlin\Http\Cookie;

// Fluent builder; Cookie::make() or new Cookie() both work
$cookie = Cookie::make('auth')
    ->set($token)
    ->encrypted()              // enable encryption
    ->key($secretKey)          // optional: explicit key (overrides global default)
    ->expires(time() + 3600)
    ->send();

// Reading a cookie value
$value = $ctx->cookies()->get('auth');

// Deleting a cookie
$ctx->cookies()->delete('auth');
```

The `Cookie` object exposes the following fluent API:

| Method                                  | Description                            |
| --------------------------------------- | -------------------------------------- |
| `set(mixed $value): static`             | Set the cookie value                   |
| `value(mixed $default = null): mixed`   | Read the current value                 |
| `encrypted(bool $state = true): static` | Enable/disable encryption via `Crypt`  |
| `key(?string $key): static`             | Encryption key (falls back to app key) |
| `expires(int $timestamp): static`       | Expiry Unix timestamp                  |
| `path(string $path): static`            | Cookie path                            |
| `domain(string $domain): static`        | Cookie domain                          |
| `secure(bool $state): static`           | HTTPS-only flag                        |
| `httpOnly(bool $state): static`         | `HttpOnly` flag                        |
| `send(): static`                        | Queue cookie for sending               |
| `delete(): void`                        | Delete cookie by setting past expiry   |

## Crypt Helper

For general-purpose encryption needs, Merlin provides the Crypt class with authenticated encryption support. It automatically uses the best available encryption extension (libsodium preferred, OpenSSL fallback).

`Merlin\Crypt` is a **static-only** class – never instantiate it.

```php
use Merlin\Crypt;

$encrypted = Crypt::encrypt('hello', $secretKey);
$plain     = Crypt::decrypt($encrypted, $secretKey); // null on failure/tamper

// Explicit cipher selection
Crypt::encrypt($value, $key, Crypt::CIPHER_CHACHA20_POLY1305);
Crypt::encrypt($value, $key, Crypt::CIPHER_AES_256_GCM);
Crypt::encrypt($value, $key, Crypt::CIPHER_AUTO); // default (best available)

// Availability checks
Crypt::hasSodium();          // bool
Crypt::hasOpenSSL();         // bool
Crypt::getAvailableCipher(); // string constant
```

## Request Validation

Validate and normalize all external input:

- Route params (`{id:int}` for typed routing)
- Query/body params from `Request`
- Uploaded files (`UploadedFile` checks)

## Session Safety

- Regenerate session IDs on login/privilege changes
- Store minimal sensitive data in session
- Expire old sessions and apply inactivity timeout

## Logging and Secrets

- Never log raw passwords, tokens, or full secrets
- Redact PII in production logs
- Keep encryption keys outside source control (env/secret manager)

## Related

- [Logging](09-LOGGING.md)
- [HTTP Request](06-HTTP-REQUEST.md)
