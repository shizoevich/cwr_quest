<?php

namespace App\Jobs\Google;

use App\Helpers\Google\DirectoryService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetUserByEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    private $email;

    /**
     * GetUserByEmail constructor.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     * @return void
     * @throws \Google_Service_Exception
     */
    public function handle()
    {
        $service = (new DirectoryService())
            ->setScopes([\Google_Service_Directory::ADMIN_DIRECTORY_USER])
            ->getService();
        $user = null;
        try {
            $user = $service->users->get($this->email);
        } catch (\Google_Service_Exception $e) {
            if(404 !== $e->getCode()) {
                throw $e;
            }
        }

        return $user;
    }
}
