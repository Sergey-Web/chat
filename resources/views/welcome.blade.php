<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">  

        <title>{{ config('app.name', 'Laravel') }}</title>
        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/chat-style.css') }}" rel="stylesheet">
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js"></script>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    BirdChat
                </div>
                <div class="board-chat"></div>
                <textarea name="message" id="textMessage" cols="30" rows="2" style="width:100%"></textarea>
                <button class="btn btn-primary" id="sendMessage">Send message</button>

            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var socket = io(':3000');

                function scrollBottom() {
                    var objDiv = document.getElementsByClassName('board-chat');
                    objDiv[0].scrollTop = objDiv[0].scrollHeight;
                }

                $.ajax({
                    type: "POST",
                    url: "/connectUser",
                    success: function(data){
                        console.log(data);
                        var channel = data.channel;
                        var userId = data.userId;
                        var agentId = data.agentId;
                        var connect = data.connect;
                        var nameAgent = data.name;
                        var messages = data.messages;
                        var name;

                        socket.on(userId + ':connect', function(data){
                            console.log(data);
                            var agentId = data.agentId;
                            var name = data.name;

                            socket.on(userId + ':' + agentId, function(data) {
                                console.log(data);
                                var role = data.role;
                                var name = data.name;
                                var message = data.message;
                                if(role == 3) {
                                    $('.board-chat').append('<p>' + name + ': ' + message + '</p>');
                                    scrollBottom();
                                }
                            });
                        });

                        socket.on(userId + ':disconnect', function(data){
                            console.log(data);
                            var userId = data.userId;
                            var agentId = data.agentId;

                            socket.removeAllListeners(userId + ':' + agentId);
                        });

                        if(messages && messages != '') {
                            var parseMessages = JSON.parse(messages);
                            parseMessages.forEach(function(item, i) {
                                var name = (item.name != '') ? item.name : 'You';
                                $('.board-chat').append('<p>'+ name + ': ' + item.messages +'</p>');
                                scrollBottom();
                            });
                        }

                        if(agentId != ''){
                            socket.on(userId + ':' + agentId, function(data) {
                                var message = data.message;
                                var nameAgent = data.name;
                                var role = data.role;

                                if(role == 3) {
                                    $('.board-chat').append('<p>' + nameAgent + ': ' + message + '</p>');
                                    scrollBottom();
                                }
                            });
                        }
                    }
                });

                $('#sendMessage').on('click', function() {
                    var textMessage = $('#textMessage').val();
                    var messages = {
                            'messages': textMessage
                         };
                    $.ajax({
                        type: "POST",
                        url: "/userSendMessage",
                        data: messages,
                        success: function(data){
                            console.log(data);
                            var message = (data.messages != null) ? data.messages : '';

                            $('.board-chat').append('<p>You' + ': ' + message + '</p>');
                            scrollBottom();
                        }
                    });
                    $('#textMessage').val('');
                });
            });
        </script>
    </body>
</html>
