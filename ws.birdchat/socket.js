const server = require('http').Server();

const io = require('socket.io')(server);

const Redis = require('ioredis');

const redis = new Redis();

redis.psubscribe('*', function(err, count){});

redis.on('pmessage', function(pattern, channel, message) {
    let parseMessage = JSON.parse(message);
    let role = parseMessage.data.role;
    let agentId = parseMessage.data.agentId;
    let userId = parseMessage.data.userId;
    let messages = parseMessage.data.messages;

    if(!agentId && role == 4) {
        io.emit(channel + ':' + 3, {invite: userId});
    }else if(agentId && role == 3 || role == 4) {
        io.emit(userId + ':' + agentId, {message: messages});
    }
    console.log(parseMessage.data);
});

server.listen(3000);