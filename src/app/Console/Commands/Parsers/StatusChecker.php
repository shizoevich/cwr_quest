<?php

namespace App\Console\Commands\Parsers;

use App\Mail\ParserStacked;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class StatusChecker extends Command
{
    /**
     * @var array
     *
     * key - queue name
     * value - max jobs in queue
     */
    private $queues = [];
    
    /**
     * @var string[]
     */
    private $fixAllowedQueues;
    
    private $stackedJobs = [];
    
    private $emails = [];
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:check-status';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking stacked parsers';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $emails = config('parser.emails');
        $this->emails = !empty($emails) ? explode(',', $emails) : [];
        $this->queues = [
            'parser' => config('parser.job_limits.parser'), //Base parsers (appointments, patients, etc.)
            'tridiuum-parser' => config('parser.job_limits.tridiuum_parser'), //Tridiuum patients parser
            'tridiuum' => config('parser.job_limits.tridiuum'), //Tridiuum appointments parser (+ creating patients), availability synchronization
            'tridiuum-long' => config('parser.job_limits.tridiuum'), //Tridiuum appointments parser (+ creating patients), availability synchronization
            'tridiuum-availability' => config('parser.job_limits.tridiuum_availability'), //Tridiuum appointments parser (+ creating patients), availability synchronization
        ];
        $this->fixAllowedQueues = [
            'tridiuum-parser',
            'tridiuum',
            'tridiuum-long',
            'tridiuum-availability',
        ];
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(empty($this->emails)) {
            $this->output->error('No email addresses to send report.');
            return;
        }
        $counts = $this->getJobCounts();
        $this->checkStackedJobs($counts);
        if($this->hasStackedJobs()) {
            $this->reportStackedJobs();
            $this->fixStackedJobs();
        }
    }
    
    private function fixStackedJobs()
    {
        foreach ($this->stackedJobs as $stackedJob) {
            if(in_array($stackedJob['queue'], $this->fixAllowedQueues)) {
                \Redis::connection()->del('queues:' . $stackedJob['queue']);
                exec('sudo -i pm2 restart ' . $stackedJob['queue']);
            }
        }
    }
    
    private function reportStackedJobs()
    {
        $info = [];
        foreach ($this->stackedJobs as $stackedJob) {
            $info[$stackedJob['queue']] = $this->prepareStackedJobsInfo($this->getStackedJobsInfo($stackedJob['queue']), (object)$stackedJob);
        }
        \Mail::to($this->emails)->send(new ParserStacked($info));
    }
    
    /**
     * @param Collection $jobs
     * @param \stdClass  $data
     *
     * @return Collection
     */
    private function prepareStackedJobsInfo(Collection $jobs, \stdClass $data): Collection
    {
        $jobs->transform(function ($job) {
            $payload = json_decode($job->payload, true);
            $reservedAt = data_get($job, 'reserved_at');
            $availableAt = data_get($job, 'available_at');
        
            return [
                'parser'       => data_get($payload, 'displayName'),
                'reserved_at'  => $reservedAt ? Carbon::createFromTimestamp($reservedAt)->toDateTimeString() : null,
                'available_at' => $availableAt ? Carbon::createFromTimestamp($availableAt)->toDateTimeString() : null,
            ];
        });
    
        return collect([
            'count' => $data->count,
            'jobs'  => $jobs
        ]);
    }
    
    /**
     * @param string $queue
     *
     * @return Collection
     */
    private function getStackedJobsInfo(string $queue): Collection
    {
        return \DB::table('jobs')
            ->where('queue', $queue)
            ->limit($this->queues[$queue])
            ->get([
                'payload',
                'available_at',
                'reserved_at'
            ]);
    }
    
    /**
     * @param array $counts
     */
    private function checkStackedJobs(array $counts)
    {
        foreach ($counts as $item) {
            if($item['count'] > $this->queues[$item['queue']]) {
                $this->stackedJobs[] = $item;
            }
        }
    }
    
    /**
     * @return bool
     */
    private function hasStackedJobs(): bool
    {
        return !empty($this->stackedJobs);
    }
    
    /**
     * @return array
     */
    private function getJobCounts(): array
    {
        $queues = array_keys($this->queues);
        $result = [];
        foreach ($queues as $queue) {
            $result[] = [
                'queue' => $queue,
                'count' => \Queue::size($queue),
            ];
        }
        
        return $result;
    }
}
