<?php

/**
 * @file
 * Learning RabbitMQ - Log emitter direct.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare an exchange.
$channel->exchange_declare('direct_logs', 'direct', false, false, false);

// Get the message severity from the first CLI argument. Default to 'info'.
$severity = !empty($argv[1]) ? $argv[1] : 'info';

// Get the message data from the second CLI argument.
$data = implode(' ', array_slice($argv, 2));

// Fallback message.
if (empty($data)) {
    $data = "Hello World!";
}

// Create the message.
$msg = new AMQPMessage($data);

// Push the message to the queue.
$channel->basic_publish($msg, 'direct_logs', $severity);

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();
