<?php

namespace DarkGhostHunter\TransbankApi\Clients\Webpay;

use DarkGhostHunter\TransbankApi\Exceptions\Webpay\ErrorResponseException;
use DarkGhostHunter\TransbankApi\Exceptions\Webpay\InvalidSignatureException;
use DarkGhostHunter\TransbankApi\Transactions\WebpayTransaction;
use Exception;

/**
 * Class PlusNullify
 *
 * This class allows the commerce to nullify a Transaction, totally or partially.
 *
 * @package DarkGhostHunter\TransbankApi\Clients\Webpay
 */
class PlusNullify extends WebpayClient
{
    /**
     * Endpoint type to use
     *
     * @var string
     */
    protected $endpointType = 'commerce';

    /**
     * Nulls a Transaction in Webpay
     *
     * @param WebpayTransaction $transaction
     * @return mixed
     * @throws ErrorResponseException
     * @throws InvalidSignatureException
     */
    public function nullify(WebpayTransaction $transaction)
    {
        $transaction = (object)[
            // Transaction Code or Capture Authorization Code
            'authorizationCode' => $transaction->authorizationCode,
            // Authorized Transaction amount to null (substract), or full Capture Amount
            'authorizedAmount' => $transaction->authorizedAmount,
            'buyOrder' => $transaction->buyOrder,
            'commerceId' => $transaction->commerceCode ?? $this->credentials->commerceCode,
            'nullifyAmount' => $transaction->nullifyAmount
        ];

        try {
            // Perform the capture with the data, and return if validates
            $response = $this->performNullify($transaction);
        } catch (Exception $e) {
            throw new ErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        if ($this->validate()) {
            return $response;
        }

        throw new InvalidSignatureException();

    }

    /**
     * Performs the Nullify on Webpay
     *
     * @param $transaction
     * @return mixed
     */
    protected function performNullify($transaction)
    {
        return (array)($this->connector->nullify([
            'nullificationInput' => $transaction
        ]))->return;
    }

}
