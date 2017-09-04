const server = require('http').Server();

const io = require('socket.io')(server);

const Redis = require('ioredis');

const redis = new Redis();

redis.subscribe('test-channel');

redis.on('message', function(channel, message) {
    console.log(message);
});

server.listen(3000);