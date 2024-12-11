<?php
namespace Spatie\LaravelCipherSweet\Tests\TestClasses;

class UserObserver
{
    public function saved(User $user)
    {
        $msg = "saved: dirty=".sizeof($user->getDirty());
        \Log::info($msg);
    }

    public function saving(User $user)
    {
        $user->name .=".";

        $msg = "saving: dirty=".sizeof($user->getDirty());
        \Log::info($msg);
    }
}
