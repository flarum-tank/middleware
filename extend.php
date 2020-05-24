<?php

/*
 * This file is part of tank/middleware.
 *
 * Copyright (c) 2019 Matthew Kilgore.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Tank\Middleware;

use Flarum\Extend;

return [
    (new Extend\Middleware('forum'))
        ->add(InsertDNSPrefetch::class)
        ->add(AddPreloadHeaders::class),
    (new Extend\Middleware('api'))
        ->add(ApiAddPreloadHeaders::class)
];
