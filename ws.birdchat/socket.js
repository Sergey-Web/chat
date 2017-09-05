const server = require('http').Server();

const io = require('socket.io')(server);

const Redis = require('ioredis');

const redis = new Redis();

redis.psubscribe('*', function(err, count){});

redis.on('pmessage', function(pattern, channel, message) {
    let company = [];
    company.pup();
    console.log(mes);
});

server.listen(3000);