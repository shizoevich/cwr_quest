<?php

declare(strict_types=1);

namespace App\Components\Square;

use Square\Apis\BaseApi;
use Square\Exceptions\ApiException;
use Square\ApiHelper;
use Square\Http\ApiResponse;
use Square\Http\HttpRequest;
use Square\Http\HttpResponse;
use Square\Http\HttpMethod;
use Square\Http\HttpContext;
use Unirest\Request;
use Square\Apis\TransactionsApi as BaseTransactionsApi;

/**
 * Copied from Old Square SDK
 * Class TransactionsApi
 * @package App\Components\Square
 */
class TransactionsApi extends BaseTransactionsApi
{
    /**
     * Lists transactions for a particular location.
     *
     * Transactions include payment information from sales and exchanges and refund
     * information from returns and exchanges.
     *
     * Max results per [page](#paginatingresults): 50
     *
     * @deprecated
     *
     * @param string $locationId The ID of the location to list transactions for.
     * @param string|null $beginTime The beginning of the requested reporting period, in RFC 3339
     *                               format.
     *
     *                               See [Date ranges](#dateranges) for details on date
     *                               inclusivity/exclusivity.
     *
     *                               Default value: The current time minus one year.
     * @param string|null $endTime The end of the requested reporting period, in RFC 3339 format.
     *
     *                             See [Date ranges](#dateranges) for details on date
     *                             inclusivity/exclusivity.
     *
     *                             Default value: The current time.
     * @param string|null $sortOrder The order in which results are listed in the response (`ASC`
     *                               for
     *                               oldest first, `DESC` for newest first).
     *
     *                               Default value: `DESC`
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *                            Provide this to retrieve the next set of results for your
     *                            original query.
     *
     *                            See [Paginating results](#paginatingresults) for more
     *                            information.
     *
     * @return ApiResponse Response from the API call
     *
     * @throws ApiException Thrown if API call fails
     */
    public function listTransactions(
        string $locationId,
        ?string $beginTime = null,
        ?string $endTime = null,
        ?string $sortOrder = null,
        ?string $cursor = null
    ): ApiResponse {
        //prepare query string for API call
        $_queryBuilder = '/v2/locations/{location_id}/transactions';
        
        //process optional query parameters
        $_queryBuilder = ApiHelper::appendUrlWithTemplateParameters($_queryBuilder, [
            'location_id' => $locationId,
        ]);
        
        //process optional query parameters
        ApiHelper::appendUrlWithQueryParameters($_queryBuilder, [
            'begin_time'  => $beginTime,
            'end_time'    => $endTime,
            'sort_order'  => $sortOrder,
            'cursor'      => $cursor,
        ]);
        
        //validate and preprocess url
        $_queryUrl = ApiHelper::cleanUrl($this->config->getBaseUri() . $_queryBuilder);
        
        //prepare headers
        $_headers = [
            'user-agent'    => BaseApi::USER_AGENT,
            'Accept'        => 'application/json',
            'Square-Version' => $this->config->getSquareVersion(),
            'Authorization' => sprintf('Bearer %1$s', $this->config->getAccessToken())
        ];
        $_headers = ApiHelper::mergeHeaders($_headers, $this->config->getAdditionalHeaders());
        
        $_httpRequest = new HttpRequest(HttpMethod::GET, $_headers, $_queryUrl);
        
        //call on-before Http callback
        if ($this->getHttpCallBack() != null) {
            $this->getHttpCallBack()->callOnBeforeRequest($_httpRequest);
        }
        // Set request timeout
        Request::timeout($this->config->getTimeout());
        
        // and invoke the API call request to fetch the response
        try {
            $response = Request::get($_queryUrl, $_headers);
        } catch (\Unirest\Exception $ex) {
            throw new ApiException($ex->getMessage(), $_httpRequest);
        }
        
        $_httpResponse = new HttpResponse($response->code, $response->headers, $response->raw_body);
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);
        
        //call on-after Http callback
        if ($this->getHttpCallBack() != null) {
            $this->getHttpCallBack()->callOnAfterRequest($_httpContext);
        }
        
        if (!$this->isValidResponse($_httpResponse)) {
            return ApiResponse::createFromContext($response->body, null, $_httpContext);
        }
        
        $mapper = $this->getJsonMapper();
        $deserializedResponse = $mapper->mapClass($response->body, 'Square\\Models\\ListTransactionsResponse');
        return ApiResponse::createFromContext($response->body, $deserializedResponse, $_httpContext);
    }
}
