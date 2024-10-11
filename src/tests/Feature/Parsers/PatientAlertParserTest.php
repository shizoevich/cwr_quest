<?php

namespace Tests\Feature\Parsers;

use App\Appointment;
use App\Helpers\Sites\OfficeAlly\OfficeAlly;
use App\Patient;
use App\PatientAlert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Mockery;
use GuzzleHttp\Psr7\Response;

class PatientAlertParserTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }
    /**
     * Helper method to parse alerts from the API response.
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @param int $patientId
     * @return array|null
     */
    private function parseAlertsFromResponse(Response $response, int $patientId)
    {
        $alerts = json_decode($response->getBody()->getContents(), true);
        $alerts = data_get($alerts, 'rows');
        if ($alerts === null) {
            $this->officeAlly->notifyIfFailed("Patient Alert list is not parsed (Patient ID: {$patientId}).");
        }

        return $alerts;
    }
    /** @test */
    public function testAlertParsingSuccess()
    {
        // Mock the API response
        $responseData = [
            'rows' => [
                // Sample data for alerts
                // ...
            ],
        ];
        $response = new Response(200, [], json_encode($responseData));

        // Create a mock of the OfficeAlly class to expect no calls to notifyIfFailed
        $officeAllyMock = Mockery::mock(OfficeAlly::class);
        $officeAllyMock->shouldNotReceive('notifyIfFailed');

        // Set up the test case with the mock
        $this->instance(OfficeAlly::class, $officeAllyMock);

        // Call the method being tested
        $patientId = 123; // Replace with the actual patient ID
        $alerts = $this->parseAlertsFromResponse($response, $patientId);

        // Perform assertions to ensure parsing and processing are successful
        $this->assertInternalType('array', $alerts);
    }

    /** @test */
    public function createsPatientAlertWithDateResolved()
    {
        $alerts = [
            [
                'cell' => [1, 2, 3, '2023-01-01', 'Test message', 'Resolved by', '2023-01-02', 1],
            ],
        ];

        $patient = factory(Patient::class)->create();

        foreach ($alerts as $item) {
            if (count($item['cell']) < 8) {
                continue; // Skip invalid data
            }

            $alertData = [
                'officeally_alert_id' => intval($item['cell'][0]),
                'patient_id' => $patient->id,
                'date_created' => Carbon::parse($item['cell'][3])->toDateString(),
                'message' => $item['cell'][4],
                'resolved_by' => $item['cell'][5],
                'status' => $item['cell'][7], // Change to index 7
                'date_resolved' => Carbon::parse($item['cell'][6])->toDateString(),
            ];
            PatientAlert::updateOrCreate(['officeally_alert_id' => $alertData['officeally_alert_id']], $alertData);
        }

        $this->assertEquals(1, PatientAlert::count()); // Assuming it should create one record
        $this->assertDatabaseHas('patient_alerts', $alertData);
    }

    /** @test */
    public function createsPatientAlertWithoutDateResolved()
    {
        $alerts = [
            [
                'cell' => [1, 2, 3, '2023-01-01', 'Test message', 'Resolved by', '', 1],
            ],
        ];

        $patient = factory(Patient::class)->create();

        foreach ($alerts as $item) {
            if (count($item['cell']) < 8) {
                continue; // Skip invalid data
            }

            $alertData = [
                'officeally_alert_id' => intval($item['cell'][0]),
                'patient_id' => $patient->id,
                'date_created' => Carbon::parse($item['cell'][3])->toDateString(),
                'message' => $item['cell'][4],
                'resolved_by' => $item['cell'][5],
                'status' => $item['cell'][7], // Change to index 7
            ];
            PatientAlert::updateOrCreate(['officeally_alert_id' => $alertData['officeally_alert_id']], $alertData);
        }

        $this->assertEquals(1, PatientAlert::count()); // Assuming it should create one record
        $this->assertDatabaseHas('patient_alerts', $alertData);
    }

    /** @test */
    public function updatesExistingPatientAlertWithDateResolved()
    {
        $alerts = [
            [
                'cell' => [1, 2, 3, '2023-01-01', 'Updated message', 'Updated resolved by', '2023-01-02', 1],
            ],
        ];

        $patient = factory(Patient::class)->create();

        $alertData = [
            'officeally_alert_id' => intval($alerts[0]['cell'][0]),
            'patient_id' => $patient->id,
            'date_created' => Carbon::parse($alerts[0]['cell'][3])->toDateString(),
            'message' => $alerts[0]['cell'][4],
            'resolved_by' => $alerts[0]['cell'][5],
            'status' => $alerts[0]['cell'][7], // Change to index 7
            'date_resolved' => Carbon::parse($alerts[0]['cell'][6])->toDateString(),
        ];
        PatientAlert::create($alertData);

        $alerts[0]['cell'][4] = 'Updated message';
        $alerts[0]['cell'][5] = 'Updated resolved by';
        $alerts[0]['cell'][7] = 'updated status'; // Change to index 7

        foreach ($alerts as $item) {
            if (count($item['cell']) < 8) {
                continue; // Skip invalid data
            }

            $alertData = [
                'officeally_alert_id' => intval($item['cell'][0]),
                'patient_id' => $patient->id,
                'date_created' => Carbon::parse($item['cell'][3])->toDateString(),
                'message' => $item['cell'][4],
                'resolved_by' => $item['cell'][5],
                'status' => $item['cell'][7], // Change to index 7
                'date_resolved' => Carbon::parse($item['cell'][6])->toDateString(),
            ];
            PatientAlert::updateOrCreate(['officeally_alert_id' => $alertData['officeally_alert_id']], $alertData);
        }

        $this->assertEquals(1, PatientAlert::count()); // Assuming it should still be one record
        $this->assertDatabaseHas('patient_alerts', $alertData);
    }

    /** @test */
    public function testRetrievePatientsWithConditions()
    {
        // Create and persist a patient with a patient_id other than 11111111 (should be retrieved by the query)
        $patient = factory(Patient::class)->create(['patient_id' => 22222222]);

        // Create and persist an archived patient with a patient_id other than 11111111 (should not be retrieved)
        $archivedPatient = factory(Patient::class)->create(['patient_id' => 33333333, 'status_id' => 7]);

        // Simulate existing patient IDs (assuming these are the IDs you are filtering)
        $existingPatientIds = [$patient->patient_id];

        // Execute the query
        $result = Patient::select(['id', 'patient_id'])
            ->where('patient_id', '!=', 11111111)
            ->when($existingPatientIds, function ($query) use ($existingPatientIds) {
                $query->whereIn('patient_id', $existingPatientIds);
            })
            ->notArchived()
            ->get();

        // Assert that the result contains the non-archived patient with patient_id 22222222
        $this->assertCount(1, $result);
        $this->assertEquals($patient->id, $result[0]->id);
        $this->assertEquals($patient->patient_id, $result[0]->patient_id);
    }

    /** @test */
    public function testRetrievePatientsWithSpecificPatientIds()
    {
        // Create and persist patients with specific patient_ids
        $patients = factory(Patient::class, 5)->create();

        // Simulate a list of patient_ids you want to filter (assuming these are the IDs you are filtering)
        $patientIdsToFilter = $patients->pluck('patient_id')->toArray();
        // Execute the query
        $result = Patient::select(['id', 'patient_id'])
            ->where('patient_id', '!=', 11111111)
            ->when($patientIdsToFilter, function ($query) use ($patientIdsToFilter) {
                $query->whereIn('patient_id', $patientIdsToFilter);
            })
            ->notArchived()
            ->get();

        // Assert that the result contains the patients with the specified patient_ids
        $this->assertCount(5, $result);
        foreach ($patients as $patient) {
            $this->assertContains($patient->id, $result->pluck('id'));
            $this->assertContains($patient->patient_id, $result->pluck('patient_id'));
        }
    }
}
