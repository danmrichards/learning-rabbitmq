<?php

/**
 * @file
 * Learning RabbitMQ - RPC Client.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Connects to the Fibonacci RPC server and gets messages.
 */
class FibonacciRpcClient
{
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Connect to RabbitMQ.
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();

        // Declare a queue.
        list($this->callback_queue, ,) = $this->channel->queue_declare("", false, false, true, false);

        // Consume the queue and set the callback function.
        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            false,
            false,
            false,
            array($this, 'onResponse')
        );
    }

    /**
     * Queue callback.
     *
     * @param AMQPMessage $rep
     *   A message from the queue.
     */
    public function onResponse($rep)
    {
        // Ensure the correlation ID matches before getting the message.
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    /**
     * Generate messages to be pushed to the RPC queue.
     *
     * @param int $n
     *   Number of RPC messages to generate.
     *
     * @return int
     *   Fibonacci number from the RPC server.
     */
    public function call($n)
    {
        $this->response = null;
        $this->corr_id = uniqid();

        // Build the RPC message, note the correlation ID.
        $msg = new AMQPMessage((string) $n, array(
          'correlation_id' => $this->corr_id,
          'reply_to' => $this->callback_queue
        ));

        $this->channel->basic_publish($msg, '', 'rpc_queue');

        while (!$this->response) {
            $this->channel->wait();
        }

        return intval($this->response);
    }
}

// Fire up the RPC client and get the messages.
$fibonacci_rpc = new FibonacciRpcClient();
$response = $fibonacci_rpc->call(30);

echo " [.] Got ", $response, "\n";
