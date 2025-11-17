<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:set {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the specified user as an administrator.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return Command::FAILURE;
        }
        $user->is_admin = true;
        $user->save();
        $this->info("User with email {$email} has been set as an administrator.");
        return Command::SUCCESS;
    }
}
