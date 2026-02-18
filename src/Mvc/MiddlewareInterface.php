<?php

namespace Merlin\Mvc;

use Merlin\AppContext;
use Merlin\Http\Response;

interface MiddlewareInterface
{
    public function process(AppContext $context, callable $next): ?Response;
}
