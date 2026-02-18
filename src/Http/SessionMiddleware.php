<?php
namespace Merlin\Http;

use Merlin\AppContext;
use Merlin\Http\Response;
use Merlin\Mvc\MiddlewareInterface;

/**
 * Middleware to manage PHP sessions.
 * 
 * This middleware ensures that a session is started for each request and 
 * provides access to session data through the AppContext. It also ensures
 * that session data is properly saved at the end of the request before the
 * response is sent.
 */
class SessionMiddleware implements MiddlewareInterface
{
    public function process(AppContext $context, callable $next): ?Response
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $context->session = new Session($_SESSION);

        $response = $next();

        session_write_close();

        return $response;
    }
}
