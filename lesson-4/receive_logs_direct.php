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
$channel->exchange_declare('direct_logs', 'direct', false, false, false);

// Get the temporary queue name from the exchange.
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

// Get the requested severity from the CLI argument.
$severities = array_slice($argv, 1);

// Show error message if no severity argument passed.
if (empty($severities)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
    exit(1);
}

// Bind a queue for each severity.
foreach ($severities as $severity) {
    $channel->queue_bind($queue_name, 'direct_logs', $severity);
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
