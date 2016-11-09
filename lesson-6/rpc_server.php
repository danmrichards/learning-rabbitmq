<?php

/**
 * @file
 * Learning RabbitMQ - RPC server.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare a queue.
$channel->queue_declare('rpc_queue', false, false, false, false);

/**
 * Basic fibonacci sequence generator.
 *
 * @param int $n
 *   Current sequence number.
 *
 * @return int
 *   Next sequence number.
 */
function fib($n)
{
    if ($n == 0) {
        return 0;
    }

    if ($n == 1) {
        return 1;
    }

    return fib($n-1) + fib($n-2);
}

echo " [x] Awaiting RPC requests\n";

/**
 * RPC callback.
 *
 * @var AMQPMessage $req
 *   The RPC message.
 */
$callback = function ($req) {
    $n = intval($req->body);
    echo " [.] fib(", $n, ")\n";

    // Generate a fibonacci message.
    $msg = new AMQPMessage((string) fib($n), array(
      'correlation_id' => $req->get('correlation_id')
    ));

    // Push the message to the queue.
    $req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to'));

    // Acknowledge delivery.
    $req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']);
};

// Consume the RPC queue.
$channel->basic_qos(null, 1, null);
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
