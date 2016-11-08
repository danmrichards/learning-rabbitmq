<?php

/**
 * @file
 * Learning RabbitMQ - Task sender.
 */

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare a queue.
$channel->queue_declare('task_queue', false, true, false, false);

// Get the message data from the cli argument.
$data = implode(' ', array_slice($argv, 1));

// Fallback message.
if (empty($data)) {
    $data = "Hello World!";
}

// Create the message.
$msg = new AMQPMessage($data, array(
    'delivery_mode' => 2 // Makes message persistent.
));

// Push the message to the queue.
$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data, "\n";

// Close the channel and connection.
$channel->close();
$connection->close();
