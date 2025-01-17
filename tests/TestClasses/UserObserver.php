<?php

namespace Spatie\LaravelCipherSweet\Tests\TestClasses;

class UserObserver
{
    public function saved(User $user)
    {
        $msg = "saved: changed=".sizeof($user->getChanges());
        \Log::info($msg);
    }

    public function saving(User $user)
    {
        $user->name .= ".";

        $msg = "saving: dirty=".sizeof($user->getDirty());
        \Log::info($msg);
    }
}
