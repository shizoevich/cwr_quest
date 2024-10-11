<?php

namespace Tests\Helpers\OfficeAlly;

use Psy\Exception\BreakException;
use Symfony\Component\DomCrawler\Crawler;

class BillingProvidersOfficeAllyHelper
{
    public static function getBillingProviderDataFromHtml($billingProvidersHtml)
    {
        $crawler = new Crawler($billingProvidersHtml);

        $billingProviderData = null;

        try {
            $crawler->filter('table#ctl04_popupBase_grvPopup tr:not(.textbold)')->each(function ($node) use (&$billingProviderData) {
                $id = $node->filter('td:first-of-type script')->text();
                if ($id) {
                    $id = explode('|', $id);
                    if (!key_exists(10, $id)) {
                        return;
                    }
                    $phone = '';
                    if (key_exists(5, $id)) {
                        $phone = $id[5];
                    }
                    $id = $id[10];
                    $name = $node->filter('td')->eq(1)->text();
                    $address = $node->filter('td')->eq(2)->text();
                    $city = $node->filter('td')->eq(3)->text();
                    $state = $node->filter('td')->eq(4)->text();
                    $zip = $node->filter('td')->eq(5)->text();
                    $group_no = $node->filter('td')->eq(6)->text();
                    $tax_id = $node->filter('td')->eq(7)->text();
                    $npi = $node->filter('td')->eq(8)->text();

                    if (!is_null($name) && !is_null($address) && !is_null('city') && !is_null('state') && !is_null('zip')) {
                        $billingProviderData = compact('id', 'name', 'address', 'city', 'state', 'zip', 'group_no', 'tax_id', 'npi', 'phone');
                        throw new BreakException();
                    }
                }
            });
        } catch (BreakException $e) {
            return $billingProviderData;
        }
        return $billingProviderData;
    }

    public static function getBillingProviderDataStructure(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'address' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string',
            'group_no' => 'string',
            'tax_id' => 'string',
            'npi' => 'string',
            'phone' => 'string'
        ];
    }

    public static function getBillingProviderDataForHtml(): array
    {
        return [
            'id' => '190472',
            'name' => 'C Within Reach',
            'address' => '11111 Ventura Blvd. Suite 111',
            'city' => 'Encino',
            'state' => 'CA',
            'zip' => '91316-3738',
            'group_no' => '',
            'tax_id' => '473088813',
            'npi' => '1003207002',
            'phone' => '213-908-1234'
        ];
    }

    public static function getMockBillingProvidersHtml($billingProviderData): string
    {
        return '<table id="ctl04_popupBase_grvPopup">
            <tr align="left" style="background-color:White;">
                <td align="center" style="width:50px;">

                    <a href="javascript:void(0)" id="ctl04_popupBase_grvPopup_ctl02_lnkSelect"
                        onclick="PopupBase_OnSelect($(this).next().html().split(&#39;|&#39;));">Select</a>
                    <script
                        type="text/x-oa-template">' . $billingProviderData['name'] . '|' . $billingProviderData['address'] . '|' . $billingProviderData['city'] . '|' . $billingProviderData['state'] . '|' . $billingProviderData['zip'] . '|' . $billingProviderData['phone'] . '||' . $billingProviderData['tax_id'] . '|E|' . $billingProviderData['npi'] . '|' . $billingProviderData['id'] . '|261QM0850X|Change Within Reach Inc.</script>
                </td>
                <td class="wrap">' . $billingProviderData['name'] . '</td>
                <td class="wrap">' . $billingProviderData['address'] . '</td>
                <td>' . $billingProviderData['city'] . '</td>
                <td>' . $billingProviderData['state'] . '</td>
                <td>' . $billingProviderData['zip'] . '</td>
                <td>&nbsp;</td>
                <td>' . $billingProviderData['tax_id'] . '</td>
                <td>' . $billingProviderData['npi'] . '</td>
            </tr>
        </table>';
    }
}