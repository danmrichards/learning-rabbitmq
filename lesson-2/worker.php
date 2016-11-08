<?php

/**
 * @file
 * Learning RabbitMQ - Worker.
 */
error_reporting(E_ALL);
ini_set("display_errors", "1");

require_once __DIR__ . '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

// Connect to RabbitMQ.
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare a queue.
$channel->queue_declare('task_queue', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

/**
 * Display the message body.
 *
 * @var AMQPMessage $msg
 *   A message from RabbitMQ.
 */
$callback = function ($msg) {
    // Display the body and spoof working by sleeping for each dot in the body.
    echo " [x] Received ", $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done", "\n";

    // Send an ackknowledgement that we're done with this item.
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

// Consume the queue. Note that ackknowledgements are enabled.
$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the channel and connection.
$channel->close();
$connection->close();
