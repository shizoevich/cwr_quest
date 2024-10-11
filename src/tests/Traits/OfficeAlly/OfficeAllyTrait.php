<?php

namespace Tests\Traits\OfficeAlly;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;

trait OfficeAllyTrait
{
    protected static function authorizationTest($test, $account) {
        $officeAllyHelper = new OfficeAllyHelper($account);

        $token = $officeAllyHelper->getRequestVerificationToken();

        $test->assertNotEmpty($token);
        
        unset($officeAllyHelper);
    }

    protected static function getOfficeAllyHelperMock(string $accountName, string $mockMethod, $mockData) {
        $officeAllyHelper = \Mockery::mock(new OfficeAllyHelper($accountName));
        $officeAllyHelper->shouldReceive($mockMethod)
            ->andReturn($mockData);

        return $officeAllyHelper;
    }
}
