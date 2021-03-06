<?php

namespace DarkGhostHunter\TransbankApi;

use Exception;
use DarkGhostHunter\TransbankApi\Adapters\WebpayAdapter;
use DarkGhostHunter\TransbankApi\Exceptions\Webpay\TransactionTypeNullException;
use DarkGhostHunter\TransbankApi\Responses\WebpayOneclickResponse;
use DarkGhostHunter\TransbankApi\Responses\WebpayPlusMallResponse;
use DarkGhostHunter\TransbankApi\Responses\WebpayPlusResponse;
use DarkGhostHunter\TransbankApi\Exceptions\Credentials\CredentialsNotReadableException;
use DarkGhostHunter\TransbankApi\Helpers\Helpers;
use DarkGhostHunter\TransbankApi\ResponseFactories\WebpayResponseFactory;
use DarkGhostHunter\TransbankApi\TransactionFactories\WebpayTransactionFactory;
use Throwable;

/**
 * Class Webpay
 * @package DarkGhostHunter\TransbankApi
 * 
 * @method Transactions\WebpayTransaction       makeNormal(array $attributes = [])
 * @method WebpayPlusResponse             createNormal(array $attributes)
 * @method Transactions\WebpayMallTransaction   makeMallNormal(array $attributes = [])
 * @method WebpayPlusMallResponse         createMallNormal(array $attributes)
 * @method Transactions\WebpayTransaction       makeDefer(array $attributes = [])
 * @method WebpayPlusResponse             createDefer(array $attributes)
 * @method Transactions\WebpayMallTransaction   makeMallDefer(array $attributes = [])
 * @method WebpayPlusMallResponse         createMallDefer(array $attributes)
 * @method Transactions\WebpayTransaction       makeCapture(array $attributes = [])
 * @method WebpayPlusResponse             createCapture(array $attributes)
 * @method Transactions\WebpayTransaction       makeMallCapture(array $attributes = [])
 * @method WebpayPlusMallResponse         createMallCapture(array $attributes)
 * @method Transactions\WebpayTransaction       makeNullify(array $attributes = [])
 * @method WebpayPlusResponse             createNullify(array $attributes)
 * @method Transactions\WebpayTransaction       makeRegistration(array $attributes = [])
 * @method WebpayPlusResponse             createRegistration(array $attributes)
 * @method Transactions\WebpayTransaction       makeUnregistration(array $attributes = [])
 * @method WebpayOneclickResponse         createUnregistration(array $attributes)
 * @method Transactions\WebpayTransaction       makeCharge(array $attributes = [])
 * @method WebpayOneclickResponse         createCharge(array $attributes)
 * @method Transactions\WebpayTransaction       makeReverseCharge(array $attributes = [])
 * @method WebpayOneclickResponse         createReverseCharge(array $attributes)
 * @method Transactions\WebpayMallTransaction   makeMallCharge(array $attributes = [])
 * @method WebpayOneclickResponse         createMallCharge(array $attributes)
 * @method Transactions\WebpayMallTransaction   makeMallReverseCharge(array $attributes = [])
 * @method WebpayOneclickResponse         createMallReverseCharge(array $attributes)
 * @method Transactions\WebpayMallTransaction   makeMallNullify(array $attributes = [])
 * @method WebpayOneclickResponse         createMallNullify(array $attributes)
 * @method Transactions\WebpayMallTransaction   makeMallReverseNullify(array $attributes = [])
 * @method WebpayPlusResponse             createMallReverseNullify(array $attributes)
 * 
 * @method WebpayPlusMallResponse|WebpayPlusResponse    getDefer(string $transaction)
 * @method WebpayPlusMallResponse|WebpayPlusResponse    retrieveDefer(string $transaction)
 * @method WebpayPlusResponse                           confirmDefer(string $transaction)
 * @method WebpayPlusResponse                           confirmRegistration
 *
 * @method WebpayPlusResponse       getNormal(string $transaction)
 * @method WebpayPlusResponse       retrieveNormal(string $transaction)
 * @method bool                     confirmNormal(string $transaction)
 *
 * @method WebpayPlusMallResponse   getMallNormal(string $transaction)
 * @method WebpayPlusMallResponse   retrieveMallNormal(string $transaction)
 * @method bool                     confirmMallNormal(string $transaction)
 *
 * @method WebpayPlusResponse       getRegistration(string $transaction)
 *
 */
class Webpay extends AbstractService
{
    /**
     * Name of the default Webpay Public Certificate
     *
     * @const string
     */
    protected const WEBPAY_CERT = 'webpay.cert';

    /*
    |--------------------------------------------------------------------------
    | Booting
    |--------------------------------------------------------------------------
    */

    /**
     * Boot any logic needed for the Service, like the Adapter and Factories;
     *
     * @return void
     */
    protected function boot()
    {
        $this->bootAdapter();
        $this->bootTransactionFactory();
        $this->bootResponseFactory();
    }


    /**
     * Boot any logic needed for the Service, like the Adapter and Factory;
     *
     * @return void
     */
    protected function bootAdapter()
    {
        $this->adapter = new WebpayAdapter;
        $this->adapter->setIsProduction($this->isProduction());
    }

    /**
     * Instantiates (and/or boots) the Transaction Factory for the Service
     *
     * @return void
     */
    protected function bootTransactionFactory()
    {
        $this->transactionFactory = new WebpayTransactionFactory($this, $this->defaults);
    }

    /**
     * Instantiates (and/or boots) the Result Factory for the Service
     *
     * @return void
     */
    protected function bootResponseFactory()
    {
        $this->responseFactory = new WebpayResponseFactory($this);
    }

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */

    /**
     * Get the Service Credentials for the Environment
     *
     * @return mixed
     */
    protected function getProductionCredentials()
    {
        return array_merge([
            'webpayCert' => $this->getWebpayCertForEnvironment(),
        ], $this->transbankConfig->getCredentials('webpay')->toArray());
    }

    /**
     * Retrieve the Integration Credentials depending on the Transaction type
     *
     * @param string $type
     * @return array
     * @throws CredentialsNotReadableException
     */
    protected function getIntegrationCredentials(string $type = null)
    {
        // Get the directory path for the credentials for the transaction
        $environmentDir = $this->environmentCredentialsDirectory();

        $directory = $environmentDir . $this->integrationCredentialsForType($type);

        // List the folder contents from the transaction $type
        $contents = Helpers::dirContents($directory);

        // Return the credentials or fail miserably
        try {
            $credentials = [
                'commerceCode' => $commerceCode = strtok($contents[0], '.'),
                'privateKey' => file_get_contents($directory . "$commerceCode.key"),
                'publicCert' => file_get_contents($directory . "$commerceCode.cert"),
                'webpayCert' => $this->getWebpayCertForEnvironment(),
            ];

        } catch (Throwable $throwable) {
            throw new CredentialsNotReadableException($directory);
        }

        return $credentials;

    }

    /**
     * Returns the Webpay Public Certificate depending on the environment
     *
     * @return bool|string
     */
    protected function getWebpayCertForEnvironment()
    {
        return file_get_contents(
            $this->environmentCredentialsDirectory() . self::WEBPAY_CERT
        );
    }

    /**
     * Gets the directory of credentials for the transaction type
     *
     * @param string $type
     * @return string
     */
    protected function integrationCredentialsForType(string $type)
    {
        switch (true) {
            case strpos($type, 'oneclick') !== false:
                $directory = 'webpay-oneclick-normal';
                break;
            case strpos($type, 'defer') !== false:
            case strpos($type, 'capture') !== false:
            case strpos($type, 'nullify') !== false:
                $directory = 'webpay-plus-capture';
                break;
            case strpos($type, 'mall') !== false:
                $directory = 'webpay-plus-mall';
                break;
            default:
                $directory = 'webpay-plus-normal';
                break;
        }

        return $directory . '/';
    }

    /*
    |--------------------------------------------------------------------------
    | Main Operations
    |--------------------------------------------------------------------------
    */

    /**
     * Gets and Acknowledges a Transaction in Transbank
     *
     * @param $transaction
     * @param string|null $options
     * @return Contracts\ResponseInterface|WebpayPlusMallResponse|WebpayPlusResponse
     * @throws TransactionTypeNullException
     */
    public function getTransaction($transaction, $options = null)
    {
        if (!is_string($options)) {
            throw new TransactionTypeNullException;
        }

        return parent::getTransaction($transaction, $options);
    }

    /**
     * Retrieves a Transaction
     *
     * @param $transaction
     * @param $type
     * @return WebpayPlusResponse|WebpayPlusMallResponse|WebpayOneclickResponse
     */
    public function retrieveTransaction($transaction, $type)
    {
        // Set the correct adapter credentials
        $this->setAdapterCredentials($type);

        $this->logger->info("Retrieving [$type]: $transaction");

        return $this->parseResponse(
            $this->adapter->retrieve($transaction, $type),
            $type
        );
    }

    /**
     * Confirms a Transaction
     *
     * @param $transaction
     * @param $type
     * @return bool|WebpayPlusResponse
     */
    public function confirmTransaction($transaction, $type)
    {
        // Set the correct adapter credentials
        $this->setAdapterCredentials($type);

        $this->logger->info("Confirming [$type]: $transaction");

        $response = $this->adapter->confirm($transaction, $type);

        // If the response to the confirmation is just a boolean, return it
        if (is_bool($response)) {
            return $response;
        }

        return $this->parseResponse($response, $type);
    }

    /*
    |--------------------------------------------------------------------------
    | Parsers
    |--------------------------------------------------------------------------
    */

    /**
     * Transform the adapter raw answer of a transaction commitment to a
     * more friendly Webpay Response or WebpayOneclickResponse
     *
     * @param array $result
     * @param mixed $options
     * @return WebpayPlusResponse
     */
    protected function parseResponse(array $result, $options = null)
    {
        // Create the Response depending on the transaction type
        switch (true) {
            case strpos($options, 'oneclick') !== false:
                $response = new WebpayOneclickResponse($result);
                break;
            case strpos($options, 'mall') !== false:
                $response = new WebpayPlusMallResponse($result);
                break;
            default:
                $response = new WebpayPlusResponse($result);
        }

        // Add the Type to the Response
        $response->setType($options);

        // Set the status of the Response
        $response->dynamicallySetSuccessStatus();

        return $response;
    }
}