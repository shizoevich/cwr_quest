<?php

namespace App\Jobs\Parsers\Manual;

use App\Events\ParserStatusChanged;
use App\Models\Parser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class AbstractParser
 * @package App\Jobs\Parsers\Guzzle
 */
abstract class AbstractParser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * AbstractParser constructor.
     */
    public function __construct()
    {
        //
    }
    
    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $this->handleParser();
        $this->resetParserStatus();
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $this->resetParserStatus();
    }

    protected function resetParserStatus()
    {
        Parser::query()
            ->where('service', $this->getServiceName())
            ->where('name', $this->getParserName())
            ->update([
                'status' => Parser::STATUS_READY_TO_SYNCHRONIZATION,
                'started_at' => Carbon::now(),
            ]);
        event(new ParserStatusChanged());
    }

    abstract protected function handleParser();
    
    abstract protected function getParserName(): string;

    protected function getServiceName(): string
    {
        return Parser::SERVICE_OFFICEALLY;
    }
}
