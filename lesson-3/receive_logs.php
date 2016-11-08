<?php

/**
 * @file
 * Learning RabbitMQ - Log receiver.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare an exchange.
$channel->exchange_declare('logs', 'fanout', false, false, false);

// Get the temporary queue name from the exchange.
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

// Bind the queue to the exchange.
$channel->queue_bind($queue_name, 'logs');

echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

/**
 * Display the message body.
 *
 * @var AMQPMessage $msg
 *   A message from RabbitMQ.
 */
$callback = function ($msg) {
    echo ' [x] ', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
