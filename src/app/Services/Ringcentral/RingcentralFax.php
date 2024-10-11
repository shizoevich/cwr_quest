<?php

namespace App\Services\Ringcentral;

use App\Http\Requests\Fax\DownloadRequest;
use App\Models\FaxModel\Fax;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Option;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\Ringcentral\SDK\SDK;

class RingcentralFax
{
    /** @var SDK */
    protected $sdk;

    public function __construct()
    {
        $this->initSdk();
        $this->login();
    }

    /**
     * @return bool
     * @throws \RingCentral\SDK\Http\ApiException
     */
    private function login()
    {
        $loginDetails = $this->getLoginDetails();
        $this->sdk->platform()->auth()->setData($loginDetails);
        if ($this->sdk->platform()->loggedIn()) {
            return true;
        }
        $this->sdk->platform()->login([
            'jwt' => config('ringcentral.faxes.jwt')
        ]);
        $this->updateLoginDetails($this->sdk->platform()->auth()->data());

        return $this->sdk->platform()->loggedIn();
    }

    private function initSdk()
    {
        if (!isset($this->sdk)) {
            $this->sdk = new SDK(
                config('ringcentral.appKey'),
                config('ringcentral.appSecret'),
                config('ringcentral.server')
            );
        }
    }

    private function updateLoginDetails(array $data)
    {
        $encryptedData = encrypt($data);
        Option::setOptionValue('ringcentral_credentials_faxes', $encryptedData);
    }

    /**
     * @return array
     */
    private function getLoginDetails()
    {
        $encryptedData = Option::getOptionValue('ringcentral_credentials_faxes');
        try {
            $data = decrypt($encryptedData);
        } catch (DecryptException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return [];
        }

        return $data ?? [];
    }

    public function faxList($page)
    {
        $response = $this->sdk->platform()
            ->get('/account/~/extension/~/message-store?direction=Outbound&direction=Inbound&messageType=Fax&availability=Alive&dateFrom=2015-06-10T00:00:00.000Z&dateTo=2100-10-10T00:00:00.000Z&page='.$page.'&perPage=100');

        $faxData = [];
        $paging = __data_get($response->jsonArray(), 'paging', []);
        $navigation = __data_get($response->jsonArray(), 'navigation', []);
        $records = __data_get($response->jsonArray(), 'records', []);
        $faxData = ['paging' => $paging, 'navigation' => $navigation, 'records' => $records];

        return $faxData;
    }

    public function pdfDownload($fileName, $uri)
    {
        $sdkForPdf = $this->sdk->platform()->get($uri);
        $pdfFile = response($sdkForPdf->text(), 200)->header('Content-Type', 'application/pdf');
        Storage::disk('faxes')->put($fileName, $pdfFile);
    }

    public function faxPdf(DownloadRequest $request)
    {
        $faxId = $request->input('fax_id');
        $filename = Fax::where('id', $faxId)->first()->file_name;
        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make(Storage::disk('faxes')->get($filename), 200, $headers);
    }

    public function store(string $recipient, $file, $documentName, $coverIndex = null)
    {
        $body = [
            'to' => [
                ['phoneNumber' => $recipient],
            ],
            'faxResolution' => 'High',
        ];
    
        if (isset($coverIndex)) {
            $body['coverIndex'] = $coverIndex;
        }

        $request = $this->sdk->createMultipartBuilder()
            ->setBody($body)
            ->add($file, $documentName)
            ->request('/account/~/extension/~/fax');
        
        $response = $this->sdk->platform()->sendRequest($request);
        
        return $response->jsonArray();
    }
}
