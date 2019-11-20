<?php

namespace App\Listeners;

use App\Services\WebSocketService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Redis;
use Str;

class AddUniqueKey
{
    private $webSocketService;
    /**
     * Create the event listener.
     *
     * @param WebSocketService $webSocketService
     */
    public function __construct(WebSocketService $webSocketService)
    {
        $this->webSocketService = $webSocketService;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        $this->webSocketService->addKey($user->id);
    }
}
