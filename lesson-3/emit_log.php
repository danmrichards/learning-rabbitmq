<?php

/**
 * @file
 * Learning RabbitMQ - Log emitter.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare an exchange.
$channel->exchange_declare('logs', 'fanout', false, false, false);

// Get the message data from the cli argument.
$data = implode(' ', array_slice($argv, 1));

// Fallback message.
if (empty($data)) {
    $data = "info: Hello World!";
}

// Create the message.
$msg = new AMQPMessage($data);

// Push the message to the queue.
$channel->basic_publish($msg, 'logs');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();
