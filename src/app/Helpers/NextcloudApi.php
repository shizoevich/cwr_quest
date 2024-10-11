<?php
/**
 * Created by PhpStorm.
 * User: naumenko_da
 * Date: 31.07.2017
 * Time: 15:14
 */

namespace App\Helpers;


use Sabre\DAV\Client;
use Sabre\DAV\MkCol;

class NextcloudApi
{
    private $apiPoint = '/nextcloud/remote.php/webdav';
    private $client;

    public function __construct()
    {
        $this->client = new Client(config('nextcloud'));
        $this->client->addCurlSetting(CURLOPT_SSL_VERIFYPEER, false);
        $this->client->addCurlSetting(CURLOPT_SSL_VERIFYHOST, false);
    }

    public function createFolder($name)
    {
        $url = $this->apiPoint . '/' . $name;

        return $this->client->request('MKCOL', $url);
    }



    /**
     * @return string
     */
    public function uploadFile($fileName, $filePath)
    {
        $path_parts = pathinfo($filePath);
        $url = $this->apiPoint . '/' . $fileName;
        $fh = fopen($filePath, 'r');
        $content = fread($fh, filesize($filePath));
        fclose($fh);
        return $this->client->request('PUT', $url, $content);
    }

    public function shareFile($fileNextcloudPath)
    {
        $url = '/nextcloud/ocs/v2.php/apps/files_sharing/api/v1/shares';

        $r = $this->client->request('GET', $url . '?' . http_build_query([
                'format' => 'json',
                'path' => $fileNextcloudPath,
                'reshares' => true
            ]),null,[
                'OCS-APIREQUEST'=>'true'
        ]);

        $r = $this->client->request('GET', $url . '?' . http_build_query([
                'format' => 'json',
                'path' => $fileNextcloudPath,
                'shared_with_me' => true,
            ]), null, [
            'OCS-APIREQUEST' => 'true',
        ]);

        $r = $this->client->request('POST', $url . '?format=json' , http_build_query([
                'path' => $fileNextcloudPath,
                'password' => '',
                'passwordChanged' => false,
                'permissions' => 3,
                'expireDate' => '',
                'shareType' => 3,
            ]), [
            'OCS-APIREQUEST' => 'true',
        ]);
        $r = json_decode($r['body'], true);
        $nextCloudFileId = $r['ocs']['data']['id'];

        $r = $this->client->request('PUT', $url .'/'.$nextCloudFileId. '?format=json' , http_build_query([
            'permissions' => 3
        ]), [
            'OCS-APIREQUEST' => 'true',
        ]);
//        $r = json_decode($r['body'], true);

        $r = $this->client->request('GET', $url . '?' . http_build_query([
                'format' => 'json',
                'path' => $fileNextcloudPath,
                'reshares' => true
            ]),null,[
            'OCS-APIREQUEST'=>'true'
        ]);

        if($r['statusCode'] < 400) {
            $r = json_decode($r['body'], true);
            $shareData = $r['ocs']['data'][0];
            $shareData['nextcloud_id'] = $nextCloudFileId;

            return $shareData;
        } else {
            return false;
        }
    }

    public function deleteFile($filePath)
    {
        $url = $this->apiPoint . '/' . $filePath;

        return $this->client->request('DELETE', $url);
    }

}