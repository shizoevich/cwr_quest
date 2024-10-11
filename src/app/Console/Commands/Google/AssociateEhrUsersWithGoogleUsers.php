<?php

namespace App\Console\Commands\Google;

use App\Helpers\Google\DirectoryService;
use App\User;
use Illuminate\Console\Command;

class AssociateEhrUsersWithGoogleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:associate-users {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pageToken = null;
        $tableData = [];
        while (true) {
            $users = $this->getUsers($pageToken);
            $bar = $this->output->createProgressBar(count($users->users));
            foreach ($users->users as $user) {
                $updatedCount = $this->updateUser($user->emails, $user->id);
                $tableData[] = [$user->primaryEmail, $user->id, $updatedCount];
                $bar->advance();
            }
            $bar->finish();
            $this->line('');
            $pageToken = $users->nextPageToken;
            if (null === $pageToken) {
                break;
            }
        }
        $this->table(['email', 'google id', 'updated'], $tableData);
    }

    private function getUsers($token = null)
    {
        $service = (new DirectoryService())->setScopes([\Google_Service_Directory::ADMIN_DIRECTORY_USER])->getService();

        return $service->users->listUsers([
            'customer'   => 'my_customer',
            'pageToken'  => $token,
//            'projection'      => 'custom',
//            'customFieldMask' => 'id,primaryEmail',
        ]);
    }

    /**
     * @param array $emails
     * @param string $googleId
     *
     * @return mixed
     */
    private function updateUser(array $emails, string $googleId)
    {
        foreach ($emails as $email) {
            $updatedCount = User::query()->where('email', $email['address'])
                ->when(!$this->option('force'), function ($query) {
                    $query->whereNull('google_id');
                })
                ->update(['google_id' => $googleId]);
            if($updatedCount > 0) {
                return $updatedCount;
            }
        }

        return 0;
    }
}
