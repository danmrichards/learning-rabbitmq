<?php

/**
 * @file
 * Learning RabbitMQ - Consumer.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare a queue.
$channel->queue_declare('hello', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

/**
 * Display the message body.
 *
 * @var AMQPMessage $msg
 *   A message from RabbitMQ.
 */
$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";
};

// Consume the queue.
$channel->basic_consume('hello', '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the channel and connection.
$channel->close();
$connection->close();
