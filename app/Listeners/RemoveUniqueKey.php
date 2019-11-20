<?php

namespace App\Listeners;

use App\Services\WebSocketService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemoveUniqueKey
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $user = $event->user;
        $this->webSocketService->removeKey($user->id);
    }
}
