const server = require('http').Server();

const io = require('socket.io')(server);

const Redis = require('ioredis');

const redis = new Redis();

redis.psubscribe('*', function(err, count){});

redis.on('pmessage', function(pattern, channel, message) {
    let parseMessage = JSON.parse(message);
    let role = parseMessage.data.role;
    let userId = parseMessage.data.useId;

    if(role === 'agent') {
        console.log(userId);
        io.emit('channel:'+role, {connect: .1});
    }
    //emit(channel)
});

server.listen(3000);