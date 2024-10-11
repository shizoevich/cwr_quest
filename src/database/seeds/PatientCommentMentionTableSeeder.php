<?php

use App\Models\Patient\Comment\PatientCommentMention;
use App\ProviderCommentMention;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PatientCommentMentionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProviderCommentMention::query()
            ->orderByDesc('created_at')
            ->chunk(500, function (Collection $collection) {
                $collection->each(function ($providerCommentMention) {
                    $user = User::withTrashed()
                        ->select(['id', 'provider_id'])
                        ->with(['roles'])
                        ->when($providerCommentMention->provider_id > 0, function ($query) use ($providerCommentMention) {
                            $query->where('provider_id', $providerCommentMention->provider_id);
                        })
                        ->when($providerCommentMention->provider_id < 0, function ($query) use ($providerCommentMention) {
                            $query->where('id', $providerCommentMention->provider_id * (-1));
                        })
                        ->first();

                    if (empty($user)) {
                        return;
                    }

                    $readAt = null;

                    if ($user->isOnlyProvider()) {
                        $readAt = $providerCommentMention->readed_at;
                    } else if ($user->isAdmin() || $user->isSecretary()) {
                        $readAt = $providerCommentMention->admin_readed_at;
                    }

                    $mention = PatientCommentMention::create([
                        'user_id' => $user->id,
                        'comment_id' => $providerCommentMention->comment_id,
                        'model' => $providerCommentMention->model,
                        'created_at' => $providerCommentMention->created_at,
                        'updated_at' => $providerCommentMention->updated_at,
                    ]);

                    if ($readAt) {
                        $mention->addViewForUser($user->id, $readAt);
                    }

                    if ($providerCommentMention->admin_readed_at) {
                        $mention->addViewsForAllSecretaries($providerCommentMention->admin_readed_at);
                    }
                });
            });
    }
}
