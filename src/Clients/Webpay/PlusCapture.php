<?php

namespace DarkGhostHunter\TransbankApi\Clients\Webpay;

use DarkGhostHunter\TransbankApi\Exceptions\Webpay\ErrorResponseException;
use DarkGhostHunter\TransbankApi\Transactions\WebpayTransaction;
use Exception;

/**
 * Class PlusCapture
 *
 * This class allows the commerce to capture an transaction made through WebpaySoap
 *
 * @package DarkGhostHunter\TransbankApi\WebpaySoap
 */
class PlusCapture extends WebpayClient
{
    /**
     * Endpoint type to use
     *
     * @var string
     */
    protected $endpointType = 'commerce';

    /**
     * Captures the transaction
     *
     * @param WebpayTransaction $transaction
     * @return mixed
     * @throws \DarkGhostHunter\TransbankApi\Exceptions\Webpay\ErrorResponseException
     */
    public function capture(WebpayTransaction $transaction)
    {
        $capture = (object)[
            'authorizationCode' => $transaction->authorizationCode,
            'buyOrder' => $transaction->buyOrder,
            'captureAmount' => $transaction->captureAmount,
            'commerceId' => $transaction->commerceId ?? $this->credentials->commerceCode,
        ];

        try {
            // Perform the capture with the data, and return if validates
            if (($response = $this->performCapture($capture)) && $this->validate())
                return $response;
        } catch (Exception $e) {
            throw new ErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Performs the WebpaySoap Capture operation
     *
     * @param $capture
     * @return array
     */
    protected function performCapture($capture)
    {
        return (array)($this->connector->capture([
            'captureInput' => $capture
        ]))->return;
    }

}