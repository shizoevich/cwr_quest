<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Option;
use App\Models\Officeally\OfficeAllyCookie;
use App\Helpers\Sites\OfficeAlly\OfficeAlly;
use GuzzleHttp\Psr7\Request;
use App\Exceptions\Officeally\OfficeallyAuthenticationException;
use Tests\Helpers\OfficeAlly\LoginOfficeAllyHelper;

class OfficeAllyLoginTest extends TestCase
{
    protected const OA_ACCOUNT = Option::OA_ACCOUNT_3;

    public function testLoginWithValidCredentials()
    {
        $officeAlly = new OfficeAlly(self::OA_ACCOUNT);
        $isLoggedIn = $officeAlly->loginWithPuppeteer();
        $this->assertTrue($isLoggedIn);

        $cookies = OfficeAllyCookie::query()
            ->where('account_name', self::OA_ACCOUNT)
            ->latest('updated_at')
            ->first();
        $this->assertNotNull($cookies);
        $this->assertNotNull($cookies->cookies);

        $client = LoginOfficeAllyHelper::getClient($cookies->cookies);
        $request = new Request('GET', 'Appointments/ViewAppointments.aspx');
        $response = $client->send($request, []);

        $isAuthenticated = LoginOfficeAllyHelper::isResponseAuthenticated($response);
        $this->assertTrue($isAuthenticated);

        $pageContent = $response->getBody()->getContents();
        $token = LoginOfficeAllyHelper::getRequestVerificationTokenFromHtml($pageContent);
        $this->assertNotEmpty($token);
    }

    public function testLoginWithInvalidCredentials()
    {
        Option::where('option_name', 'officeally_credentials')->delete();
        $this->assertDatabaseMissing(self::TABLE_OPTIONS, ['option_name' => 'officeally_credentials']);

        $officeAlly = new OfficeAlly(self::OA_ACCOUNT);
        $isLoggedIn = $officeAlly->loginWithPuppeteer();
        $this->assertFalse($isLoggedIn);

        $cookies = OfficeAllyCookie::query()
            ->where('account_name', self::OA_ACCOUNT)
            ->latest('updated_at')
            ->first();
        $this->assertNull($cookies);

        $client = LoginOfficeAllyHelper::getClient([]);
        $request = new Request('GET', 'Appointments/ViewAppointments.aspx');
        $response = $client->send($request, []);

        $isAuthenticated = LoginOfficeAllyHelper::isResponseAuthenticated($response);
        $this->assertFalse($isAuthenticated);
    }

    public function testLoginOnGetRequest()
    {
        $officeAllyMock = \Mockery::mock(OfficeAlly::class)->makePartial();
        $officeAllyMock->shouldReceive('loginWithPuppeteer')
            ->times(2)
            ->andReturn(false, false);

        try {
            $officeAllyMock->get('Appointments/ViewAppointments.aspx', [], true);
        } catch (OfficeallyAuthenticationException $e) {
           //
        }
    }

    public function testLoginOnPostRequest()
    {
        $officeAllyMock = \Mockery::mock(OfficeAlly::class)->makePartial();
        $officeAllyMock->shouldReceive('loginWithPuppeteer')
            ->times(2)
            ->andReturn(false, false);

        try {
            $officeAllyMock->post("CommonUserControls/Appointments/Api.aspx?oper=GetAppointmentData", [
                'headers' => [
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                ],
                'json'    => [
                    'appointmentID'              => null,
                    '__RequestVerificationToken' => null,
                ]
            ], true);
        } catch (OfficeallyAuthenticationException $e) {
           //
        }
    }
}
