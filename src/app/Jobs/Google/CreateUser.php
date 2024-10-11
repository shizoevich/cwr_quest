<?php

namespace App\Jobs\Google;

use App\Helpers\Google\DirectoryService;
use App\Mail\Google\AccessToAccount;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;
    /**
     * @var string
     */
    private $password;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config('app.env') !== 'production') {
            $googleUserId = rand(1, 2000000);
            $this->user->update(['google_id' => $googleUserId]);
            return;
        }
        
        $service = (new DirectoryService())
            ->setScopes([\Google_Service_Directory::ADMIN_DIRECTORY_USER])
            ->getService();
        $googleUser = new \Google_Service_Directory_User();
        $googleUser->setPrimaryEmail($this->user->email);
        $googleUser->setPassword($this->getPassword());
        $googleUser->setChangePasswordAtNextLogin(true);
        $googleUser->setSuspended(false);
        $this->setUserName($googleUser);
        $this->addPersonalEmail($googleUser);
        $googleUser = $service->users->insert($googleUser);
        $this->user->update(['google_id' => $googleUser->id]);

        $this->sendAccessToAccountByEmail();
    }

    private function sendAccessToAccountByEmail() {
        \Mail::to($this->user->therapistSurvey->personal_email)
            ->queue(new AccessToAccount($this->user->email,$this->getPassword()));
    }

    /**
     * @return string
     */
    private function getPassword(): string
    {
        if (!isset($this->password)) {
            $this->password = str_random(10);
        }

        return $this->password;
    }

    /**
     * @param \Google_Service_Directory_User $googleUser
     */
    private function addPersonalEmail(\Google_Service_Directory_User &$googleUser)
    {
        if (null !== $this->user->therapistSurvey->personal_email) {
            $userPersonalEmail = new \Google_Service_Directory_UserEmail();
            $userPersonalEmail->setAddress($this->user->therapistSurvey->personal_email);
            $userPersonalEmail->setPrimary(false);
            $userPersonalEmail->setType('home');
            $googleUser->setEmails([$userPersonalEmail]);
        }
    }

    /**
     * @param \Google_Service_Directory_User $googleUser
     */
    private function setUserName(\Google_Service_Directory_User &$googleUser)
    {
        $userName = new \Google_Service_Directory_UserName();
        $userName->setGivenName($this->user->therapistSurvey->first_name);
        $userName->setFamilyName($this->user->therapistSurvey->last_name);
        $googleUser->setName($userName);
    }
}
