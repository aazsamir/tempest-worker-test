<?php

declare(strict_types=1);

namespace Aazsamir\TempestWorkerTest;

use Tempest\Http\GenericRequest;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Session\ManageSessionMiddleware;
use Tempest\Http\Session\TrackPreviousUrlMiddleware;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\PreventCrossSiteRequestsMiddleware;
use Tempest\Router\SetCookieHeadersMiddleware;

class Controller
{
    private const STATELESS = [
        TrackPreviousUrlMiddleware::class,
        PreventCrossSiteRequestsMiddleware::class,
        ManageSessionMiddleware::class,
        SetCookieHeadersMiddleware::class,
    ];

    #[Get('/', without: self::STATELESS)]
    #[Get('/simple', without: self::STATELESS)]
    public function simple()
    {
        return new Ok('Hello world!');
    }

    #[Get('/users', without: self::STATELESS)]
    public function users()
    {
        $users = User::all();
        $users = array_map(
            fn (User $user) => [
                'id' => $user->id->value,
                'name' => $user->name,
            ],
            $users,
        );
        
        return new Json($users);
    }

    #[Post('/echo', without: self::STATELESS)]
    public function echo(GenericRequest $request): Json
    {
        $body = $request->body;

        return new Json($body);
    }
}