<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:make-admin {email}')]
#[Description('Promote an existing user to administrator.')]
class MakeUserAdmin extends Command
{
    public function handle(ActivityLogger $activityLogger): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user exists with that email address.');

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info($user->email.' is already an administrator.');

            return self::SUCCESS;
        }

        $user->forceFill(['role' => 'admin'])->save();
        $activityLogger->record(null, 'user.promoted', 'user', $user->id, $user->name);

        $this->info($user->email.' is now an administrator.');

        return self::SUCCESS;
    }
}
