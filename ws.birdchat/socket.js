const server = require('http').Server();

const io = require('socket.io')(server);

const Redis = require('ioredis');

const redis = new Redis();

redis.psubscribe('*', function(err, count){});

/**
 * @param  {
 *      [role: 
 *          1 - superadmin
 *          2 - admin
 *          3 - agent
 *          4 - user
 *      ],
 *      [company: id]
 *  }
 * @return {[type]}
 */
redis.on('pmessage', function(pattern, channel, message) {
    let parseMessage = JSON.parse(message);
    let role = parseMessage.data.role;
    let userId = parseMessage.data.userId;
    if(role == 4) {
        io.emit(channel + ':' + 3, {connect: userId});
    }
    console.log(parseMessage.data);
});

server.listen(3000);