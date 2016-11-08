# Introduction
This is a set of PHP solutions for the RabbitMQ tutorials provided at
http://www.rabbitmq.com/tutorials

## Setup
You need a docker container for this stuff yo!

```
docker run -d -p 5672:5672 -p 15672:15672 --hostname my-rabbit --name some-rabbit rabbitmq:3-management
```

RabbitMQ will now be available at http://localhost:5672
The RabbitMQ Management UI will now be available at http://localhost:15672
