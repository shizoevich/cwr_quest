<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Parser\Run as RunRequest;
use App\Jobs\Parsers\Manual\DiagnosesParser;
use App\Jobs\Parsers\Manual\PaymentsParser;
use App\Jobs\Parsers\Manual\ProvidersParser;
use App\Jobs\Parsers\Manual\AppointmentsParser;
use App\Jobs\Parsers\Manual\PatientsParser;
use App\Jobs\Parsers\Manual\PatientVisitsParser;
use App\Models\Parser;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ParserController extends Controller
{
    /**
     *
     */
    public function index()
    {
        return response()->json([
            'parsers' => Parser::all(),
        ]);
    }
    
    public function run(RunRequest $request)
    {
        Parser::query()->whereIn('id', $request->input('ids'))->each(function(Parser $parser) {
            if($parser->service === Parser::SERVICE_OFFICEALLY) {
                $jobs = [];
                switch ($parser->name) {
                    case 'appointments':
                        $jobs[] = new AppointmentsParser();
                        break;
                    case 'patients':
                        $jobs[] = new PatientsParser();
                        break;
                    case 'visits':
                        $jobs[] = new PatientVisitsParser();
                        break;
                    case 'providers':
                        $jobs[] = new ProvidersParser();
                        break;
                    case 'payments':
                        $jobs[] = new PaymentsParser();
                        break;
                    case 'diagnoses':
                        $jobs[] = new DiagnosesParser();
                        break;
                }
                if(!empty($jobs)) {
                    foreach ($jobs as $job) {
                        dispatch($job->onQueue('parser'));
                    }
                }
                $parser->update([
                    'status' => Parser::STATUS_SYNCHRONIZATION,
                    'started_at' => Carbon::now(),
                ]);
            }
        });
        
        return response()->json(null, 204);
    }
}
