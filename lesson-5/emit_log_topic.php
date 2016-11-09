<?php

/**
 * @file
 * Learning RabbitMQ - Log emitter topic.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare an exchange.
$channel->exchange_declare('topic_logs', 'topic', false, false, false);

// Get the routing key from the first CLI argument.
$routing_key = !empty($argv[1]) ? $argv[1] : 'anonymous.info';

// Get the message data from the second CLI argument.
$data = implode(' ', array_slice($argv, 2));

// Fallback message.
if (empty($data)) {
    $data = "Hello World!";
}

// Create the message.
$msg = new AMQPMessage($data);

// Push the message to the exchange.
$channel->basic_publish($msg, 'topic_logs', $routing_key);

echo " [x] Sent ",$routing_key,':',$data," \n";

$channel->close();
$connection->close();
