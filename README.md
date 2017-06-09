Bittrex API Wrapper 
=======
This package is a wrapper for the Bittrex.com Exchange. It can be used to check the current market, check your portfolio and place buy and sell orders.

Requirements
======
* Account on bittrex.com, with an API key and API secret. 

Usage
======
Install this package using:

    composer install ypho/bittrex

Create an instance of the bittrex object, and call one of the available methods.

	use Ypho\Bittrex\Client;

	$key = 'here-comes-the-api-key-from-bittrex';
	$secret = 'this-is-the-api-secret-from-bittrex';

    // Create a new Bittrex client object
	$btx = new Client($key, $secret);
	
	// Get the current values for the BTC-LTC market
	$ticker = $btx->ticker('BTC-LTC);
	
	// Get the current orderbook for the BTC-DGB market
	$oderBook = $btx->orderBook('BTC-DGB');
	
	// Get your open orders for the BTC-ETH market
	$orders = $btx->orderHistory('BTC-ETH);