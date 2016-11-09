<?php

/**
 * @file
 * Learning RabbitMQ - Log receiver direct.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare an exchange.
$channel->exchange_declare('topic_logs', 'topic', false, false, false);

// Get the temporary queue name from the exchange.
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

// Get the binding keys from the first CLI argument.
$binding_keys = array_slice($argv, 1);

// Show error message if no binding keys argument passed.
if (empty($binding_keys)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
    exit(1);
}

// Bind a queue for each binding key.
foreach ($binding_keys as $binding_key) {
    $channel->queue_bind($queue_name, 'topic_logs', $binding_key);
}

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

/**
 * Display the message body.
 *
 * @var AMQPMessage $msg
 *   A message from RabbitMQ.
 */
$callback = function ($msg) {
    echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
