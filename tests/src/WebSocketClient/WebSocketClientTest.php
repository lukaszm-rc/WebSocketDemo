<?php

namespace WebSocketClient;

use WebSocketClient\WebSocketClient;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-19 at 21:57:39.
 */
class WebSocketClientTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var WebSocketClient
	 */
	protected $object;

	protected $client, $loop;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		
	}

	public function testCallbackPingPong() {
		$loop = \React\EventLoop\Factory::create();
		$response;
		$i = 0;


		$client = new WebSocketClient($loop, SERVER_IP, SERVER_PORT, SERVER_PATH);
		$client->setOnMessageCallback(
				function (WebSocketClient $client, $data) use (&$response, $loop) {
			$response = (array) $data;
			print_r($response);
			if ($response['type'] === "response") {

				$client->disconnect();
				$loop->stop();
			}
		}
		);

		$loop->addPeriodicTimer(1, function () use (&$client, $loop, &$i) {
			$client->send(MessageFactory::createRequest("1", "ping"));
			echo ".";
			$i++;
			if ($i > 10) {
				$this->markTestIncomplete(
						'Didnt received response in 10 seconds, test failed.'
				);
				$client->disconnect();
				$loop->stop();
			}
		});
		$loop->run();
		$this->client = $client;
		$this->assertEquals('pong', $response['response']['data']);
	}

	public function testPingPong() {
		$loop = \React\EventLoop\Factory::create();
		$response;
		$client = new WebSocketClient($loop, SERVER_IP, SERVER_PORT, SERVER_PATH);

		$loop->addPeriodicTimer(1, function () use (&$client, $loop, &$response) {
			$client->send(MessageFactory::createRequest("1", "ping"));
			WebSocketClient::onTick();
		});
		$loop->addPeriodicTimer(3, function () use (&$client, $loop) {
			$client->disconnect();
			$loop->stop();
		});
		$loop->run();

		$this->assertGreaterThan(0, $client->responses, sprintf("Expected value greater than 0, got %s ", $client->responses));
		$this->assertGreaterThan(0, $client->requests, sprintf("Expected value greater than 0, got %s ", $client->requests));
	}

	public function testOnConnection() {
		$loop = \React\EventLoop\Factory::create();
		$response;
		$client = new WebSocketClient($loop, SERVER_IP, SERVER_PORT, SERVER_PATH);
		$client->setOnConnectCallback(
				function (WebSocketClient $conn) use (&$response, $loop) {
			$response = "ok";
			
			$conn->getSocket()->disconnect();
			$loop->stop();
		}
		);
		$loop->run();
		$this->assertEquals('ok', $response);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

}
