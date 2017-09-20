<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/chat-style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js"></script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @guest
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var socket = io(':3000');

        $.ajax({
            type: 'POST',
            url: '/connectAgent',
            success: function(dataAgent) {
                if(dataAgent !== 'false') {
                    var agentId = dataAgent.agentId;
                    var userId = dataAgent.userId;
                    var channel = dataAgent.channel;
                    var role = dataAgent.role;
                    var invitations = dataAgent.invitations;
                    var messages = dataAgent.messages;

                    console.log(dataAgent);

                    if(userId === '') {
                        socket.on(channel + ':' + role, function(data) {
                            console.log(data);
                            $('#connectChat').css({'display': 'block'});
                        });
                        if(invitations > 0) {
                            $('#connectChat').css({'display': 'block'});
                        }
                    } else {
                        $('#connectChat').css({'display': 'none'});
                        $('#disconnectChat').css({'display': 'block'});
                        $('.send-messages-agent').css({'display': 'block'});

                        if(messages != '') {
                            var parseMessages = JSON.parse(messages);
                            parseMessages.forEach(function(item, i) {
                                $('.panel-body').append('<p>'+ item.name + ': ' + item.messages +'</p>');
                            });
                        }

                        socket.on(userId + ':' + agentId, function(data) {
                            var userId = data.userId;
                            var message = data.message;
                            var role = data.role;
                            if(role == 4) {
                                $('.panel-body').append('<p>' + userId + ': ' + message + '</p>');
                            }
                            console.log(data);
                        });
                    }
                }
            }
        });

        $('#connectChat').on('click', function(){
            $.ajax({
                type: 'POST',
                url: '/connectAgentUser',
                success: function(data) {
                    console.log(data);
                    var userId = data.userId;
                    var agentId = data.agentId;
                    var response = JSON.parse(data.messages);
                    response.forEach(function(item, i) {
                        $('.panel-body').append('<p>'+ item.name + ': ' + item.messages +'</p>');
                    });
                    $('#connectChat').css({'display': 'none'});
                    $('#disconnectChat').css({'display': 'block'});
                    $('.send-messages-agent').css({'display': 'block'});

                    if(userId != '') {
                        console.log(data);
                        socket.on(userId + ':' + agentId, function(data) {
                            var role = data.role;
                            var name = data.userId;
                            if(role == 4) {
                                $('.panel-body').append('<p>' + name + ': '+data.message+'</p>');
                            }
                        });
                    }
                }
            });
        });

        $('#sendMessage').on('click', function(){
            var textMessage = $('#textMessage').val();
            var message = {message: textMessage};
            $.ajax({
                type: 'POST',
                url: '/agentSendMessage',
                data: message,
                success: function(data) {
                    console.log(data)
                    var name = data.name;
                    var message = data.messages;
                    $('.panel-body').append('<p>' + name + ': ' + message + '</p>');
                }
            });
            $('#textMessage').val('');
        });

    </script>
</body>
</html>
