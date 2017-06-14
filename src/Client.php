<?php

namespace Ypho\Bittrex;

class Client
{
    /** @var string */
    private $baseURI = 'https://bittrex.com/api/v1.1/';

    /** @var string */
    private $key;

    /** @var string */
    private $secret;

    /**
     * Client constructor.
     * @param $key
     * @param $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * Used to get the open and available trading markets at Bittrex along with other meta data.
     *
     * @return array
     */
    public function markets()
    {
        return $this->_call('public/getmarkets', [], false);
    }

    /**
     * Used to get all supported currencies at Bittrex along with other meta data.
     *
     * @return mixed
     */
    public function currencies()
    {
        return $this->_call('public/getcurrencies', [], false);
    }

    /**
     * Used to get the current tick values for a market.
     *
     * @param string $market
     * @return \stdClass
     */
    public function ticker($market = 'BTC-LTC')
    {
        return $this->_call('public/getticker', [
            'market' => $market,
        ], false);
    }

    /**
     * Used to get the last 24 hour summary of all active exchanges
     * @return mixed
     */
    public function marketSummaries()
    {
        return $this->_call('public/getmarketsummaries', [], false);
    }

    /**
     * Used to get retrieve the orderbook for a given market.
     *
     * @param string $market
     * @param string $type
     * @param int $depth
     * @return mixed
     */
    public function orderBook($market = 'BTC-LTC', $type = 'both', $depth = 20)
    {
        return $this->_call('public/getorderbook', [
            'market' => $market,
            'type' => $type,
            'depth' => $depth,
        ], false);
    }

    /**
     * Used to retrieve the latest trades that have occured for a specific market.
     *
     * @param string $market
     * @return mixed
     */
    public function marketHistory($market = 'BTC-LTC')
    {
        return $this->_call('public/getmarkethistory', [
            'market' => $market,
        ], false);
    }

    /**
     * Used to place a buy order in a specific market.
     * Make sure you have the proper permissions set on your API keys for this call to work.
     *
     * @param $market
     * @param $quantity
     * @param $rate
     * @return mixed
     */
    public function buyLimit($market, $quantity, $rate)
    {
        return $this->_call('market/buylimit', [
            'market' => $market,
            'quantity' => $quantity,
            'rate' => $rate
        ], true);
    }

    /**
     * Used to place an sell order in a specific market.
     * Make sure you have the proper permissions set on your API keys for this call to work.
     *
     * @param $market
     * @param $quantity
     * @param $rate
     * @return mixed
     */
    public function sellLimit($market, $quantity, $rate)
    {
        return $this->_call('market/selllimit', [
            'market' => $market,
            'quantity' => $quantity,
            'rate' => $rate,
        ], true);
    }

    /**
     * Used to cancel a buy or sell order.
     *
     * @param $uuid
     * @return mixed
     */
    public function cancel($uuid)
    {
        return $this->_call('market/cancel', [
            'uuid' => $uuid,
        ], true);
    }

    /**
     * Get all orders that you currently have opened. A specific market can be requested.
     *
     * @param null $market
     * @return mixed
     */
    public function openOrders($market = null)
    {
        $parameters = [];
        if(!is_null($market)) $parameters['market'] = $market;

        return $this->_call('market/getopenorders', $parameters, true);
    }

    /**
     * Returns an array with all balances, or retrieve the balance from your account for a specific currency.
     *
     * @param null $currency
     * @return mixed
     */
    public function balance($currency = null)
    {
        if(is_null($currency)) {
            return $this->_call('account/getbalances', [], true);
        } else {
            return $this->_call('account/getbalance', [
                'currency' => $currency,
            ], true);
        }
    }

    /**
     * Used to retrieve or generate an address for a specific currency.
     * If one does not exist, the call will fail and return ADDRESS_GENERATING until one is available.
     *
     * @param string $currency
     * @return mixed
     */
    public function depositAddress($currency = 'BTC')
    {
        return $this->_call('account/getdepositaddress', [
            'currency' => $currency,
        ], true);
    }

    /**
     * Used to withdraw funds from your account.
     *
     * @param $currency
     * @param $quantity
     * @param $address
     * @param $paymentId
     * @return mixed
     */
    public function withdraw($currency, $quantity, $address, $paymentId){
        return $this->_call('account/withdraw', [
            'currency' => $currency,
            'quantity' => $quantity,
            'address' => $address,
            'paymentid' => $paymentId,
        ], true);
    }

    /**
     * Used to retrieve a single order by uuid.
     *
     * @param $uuid
     * @return mixed
     */
    public function order($uuid){
        return $this->_call('account/getorder', [
            'uuid' => $uuid,
        ], true);
    }

    /**
     * Used to retrieve your order history for the given market. If no market is given,
     * all orders will be returned.
     *
     * @param null $market
     * @return mixed
     */
    public function orderHistory($market = null) {
        $parameters = [];
        if(!is_null($market)) $parameters['market'] = $market;

        return $this->_call('account/getorderhistory', $parameters, true);
    }

    /**
     * Used to retrieve your withdrawal history for given currency. If no currency is given,
     * all withdrawals will be returned.
     *
     * @param null $currency
     * @return mixed
     */
    public function withdrawHistory($currency = null) {
        $parameters = [];
        if(!is_null($currency)) $parameters['currency'] = $currency;

        return $this->_call('account/getwithdrawalhistory', $parameters, true);
    }

    /**
     * Used to retrieve your deposit history for given currency. If no currency is given,
     * all deposits will be returned.
     *
     * @param null $currency
     * @return mixed
     */
    public function depositHistory($currency = null) {
        $parameters = [];
        if(!is_null($currency)) $parameters['currency'] = $currency;

        return $this->_call('account/getdeposithistory', $parameters, true);
    }

    /**
     * All the magic happens here
     *
     * @param $method
     * @param array $params
     * @param bool $apiKey
     * @return mixed
     * @throws \Exception
     */
    private function _call($method, $params = array(), $apiKey = false)
    {
        // Build the URL
        $url = $this->baseURI . $method;

        // Add API key and nonce
        if ($apiKey == true) {
            $params['apikey'] = $this->key;
            $params['nonce'] = time();
        }

        // Add parameters to the URL
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        // Sign some stuff based on secret
        $sign = hash_hmac('sha512', $url, $this->secret);

        // Initialize cURL
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['apisign: ' . $sign]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL call
        $result = curl_exec($ch);

        // Get the response and return the result, or throw an error
        $response = json_decode($result);

        if ($response->success == false) {
            throw new \Exception ($response->message);
        }

        return $response->result;
    }
}