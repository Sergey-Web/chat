const server = require('http').Server();

const io = require('socket.io')(server);

const Redis = require('ioredis');

const redis = new Redis();

redis.psubscribe('*', function(err, count){});

redis.on('pmessage', function(pattern, channel, message) {
    let parseMessage = JSON.parse(message);
    let role = parseMessage.data.role;
    let agentId = parseMessage.data.agent;
    let userId = parseMessage.data.userId;

    if(!agentId && role == 4) {
        io.emit(channel + ':' + 3, {invite: userId});
    }
    
    if(agentId) {
        io.emit(userId + ':' + agentId, {connect: userId});
    }
    console.log(parseMessage.data);
});

server.listen(3000);