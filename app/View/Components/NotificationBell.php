<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class NotificationBell extends Component
{
    public int $unreadCount;

    /**
     * The latest notifications of the authenticated user.
     *
     * @var Collection<int, DatabaseNotification>
     */
    public Collection $recentNotifications;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $user = auth()->user();

        $this->unreadCount = $user?->unreadNotifications()->count() ?? 0;
        $this->recentNotifications = $user
            ? $user->notifications()->latest()->limit(6)->get()
            : collect();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.notification-bell');
    }
}
