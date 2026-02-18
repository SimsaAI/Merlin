# Security

**Keep your application secure** - Understand Merlin's security features including SQL injection protection, CSRF tokens, password hashing, encryption helpers, and secure session management. Learn security best practices and common pitfalls to avoid.

Merlin ships with secure-by-default query building and optional encryption helpers.

## SQL Injection Protection

Merlin's query builder uses prepared statements by default, automatically protecting against SQL injection. You rarely need to think about it - just use the builder API.

Use `Query`/`Model::query()` APIs and bind parameters.

```php
// Safe (bound)
$user = User::query()
    ->where('email = :email', ['email' => $inputEmail])
    ->first();

// Also safe (value escaped/bound by builder)
$user = User::query()->where('email', $inputEmail)->first();
```

Avoid string-concatenated SQL for user input.

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
$cookie = new Merlin\Http\Cookie('auth');
$cookie->setValue($token)
    ->useEncryption(true)
    ->setEncryptionKey($secretKey)
    ->setExpiration(time() + 3600)
    ->send();
```

## Crypt Helper

For general-purpose encryption needs, Merlin provides the Crypt class with authenticated encryption support. It automatically uses the best available encryption extension (Sodium or OpenSSL).

`Merlin\Crypt` supports modern authenticated encryption (depending on available extensions).

```php
$crypt = new Merlin\Crypt($secretKey);
$cipher = $crypt->encrypt('hello');
$plain = $crypt->decrypt($cipher);
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
