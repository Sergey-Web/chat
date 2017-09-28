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
    let name = parseMessage.data.name;
    let messages = parseMessage.data.messages;
    let connect = parseMessage.data.connect;
    let disconnect = parseMessage.data.disconnect;
    let storageInvite = parseMessage.data.storageInvite;

    if(connect == true) {
        io.emit(userId + ':connect', {agentId: agentId, name: name});
        if(storageInvite == 'false') {
            io.emit(channel + ':' + 3, {storageInvite: storageInvite});
        }
    } else if(disconnect == true) {
        io.emit(userId + ':disconnect', {agentId: agentId, userId: userId});
    } else if(!agentId && role == 4) {
        io.emit(channel + ':' + 3, {invite: userId});
    } else if(agentId && role == 3 || role == 4) {
        io.emit(userId + ':' + agentId, {message: messages, role: role, name: name, userId: userId});
    }
    console.log(parseMessage.data.messages);
});

server.listen(3000);