<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;

class TridiuumCredentialStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        /** @var User $user */
        if (key_exists('user_id', $this->data) && !empty($this->data['user_id'])) {
            $user = User::findOrFail($this->data['user_id']);
        } else {
            $user = Auth::user();
        }

        $username = data_get($this->data, 'tridiuum_username');
        $password = data_get($this->data, 'tridiuum_password');

        return $user->provider->update([
            'tridiuum_username'              => $username,
            'tridiuum_password'              => $username && $password ? encrypt($password) : null,
            'tridiuum_credentials_failed_at' => null,
        ]);
    }
}
