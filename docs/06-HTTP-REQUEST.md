# HTTP Request

**Handle user input safely** - Learn how to access GET/POST data, handle file uploads, read headers, detect request methods, and work with JSON input. Includes security best practices and input validation techniques.

`Merlin\Http\Request` provides normalized access to request data and uploads.

## Basic Input Access

The Request object provides a clean interface to access all incoming data. Methods support default values and work consistently across GET/POST.

```php
use Merlin\Http\Request;

$request = new Request();

$q = $request->get('q');
$page = $request->getQuery('page', 1);
$username = $request->getPost('username');

$all = $request->get();
$allGet = $request->getQuery();
$allPost = $request->getPost();
```

## Method and URL Data

Access request metadata like HTTP method, URI, and connection security. These helpers make it easy to implement method-based logic and security checks.

```php
$method = $request->getMethod();
$uri = $request->getUri();
$path = $request->getPath();

$isPost = $request->isPost();
$isSecure = $request->isSecure();
$isAjax = $request->isAjax();
```

## Server and Client Data

Access server variables and client information in a normalized way. These helpers handle edge cases and proxy configurations automatically.

```php
$host = $request->getHttpHost();
$port = $request->getPort();
$scheme = $request->getScheme();
$userAgent = $request->getUserAgent();
$clientIp = $request->getClientAddress(true);

$contentType = $request->getContentType();
$accept = $request->getAcceptableContent(true);
$bestLanguage = $request->getBestLanguage();
```

## JSON Body

For API endpoints, easily parse JSON request bodies. This is especially useful for modern single-page applications and mobile clients.

```php
$body = $request->getRequestBody();
$data = $request->getJsonBody(true); // assoc array
```

## File Uploads

Handle file uploads securely with the UploadedFile wrapper. It provides validation, type checking, and safe file operations.

```php
$file = $request->getFile('avatar');
if ($file && $file->isValid()) {
    $file->moveTo(__DIR__ . '/uploads/' . $file->getClientFilename());
}

$attachments = $request->getFiles('attachments');
```

Available `UploadedFile` methods:

- `isValid(): bool` – true when upload succeeded with no error
- `getClientFilename(): string` – original filename from the browser
- `getClientMediaType(): string` – MIME type reported by the browser
- `getSize(): int` – file size in bytes
- `moveTo(string $targetPath): void` – move file to destination

## Controller Example

```php
class UserController extends \Merlin\Mvc\Controller
{
    public function createAction(): array
    {
        $email = $this->request()->getPost('email');

        if (!$email) {
            return ['ok' => false, 'error' => 'email is required'];
        }

        User::query()->insert(['email' => $email]);

        return ['ok' => true];
    }
}
```

## Related

- [src/Http/Request.php](../src/Http/Request.php)
- [Controllers & Views](03-CONTROLLERS-VIEWS.md)
