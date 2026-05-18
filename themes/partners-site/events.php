<div class="add-user">
    <a href="#addUser"><svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="800px" height="800px" viewBox="0 0 45.402 45.402" xml:space="preserve">
            <g>
                <path d="M41.267,18.557H26.832V4.134C26.832,1.851,24.99,0,22.707,0c-2.283,0-4.124,1.851-4.124,4.135v14.432H4.141   c-2.283,0-4.139,1.851-4.138,4.135c-0.001,1.141,0.46,2.187,1.207,2.934c0.748,0.749,1.78,1.222,2.92,1.222h14.453V41.27   c0,1.142,0.453,2.176,1.201,2.922c0.748,0.748,1.777,1.211,2.919,1.211c2.282,0,4.129-1.851,4.129-4.133V26.857h14.435   c2.283,0,4.134-1.867,4.133-4.15C45.399,20.425,43.548,18.557,41.267,18.557z" />
            </g>
        </svg>Dodaj użytkownika</a>
</div>
<div class="events">
    <div class="result">

    </div>
</div>
<div id="ex1" class="modal">
    <div class="title"></div>
    <input type="text" name="password" value="" id="password" class="password" />
    <button class="change_password btn btn-submit">Zmień hasło</button>
</div>
<div id="addUser" class="modal">
    <div class="title">Dodaj użytkownika</div>
    <div> Nazwa użyktownika<br />
        <input type="text" name="username" placeholder="Nazwa użytkownika" value="" id="username" class="username" />
    </div>
    <div> Adres email<br />
        <input type="email" name="email" placeholder="Adres email" value="" id="email" class="email" />
    </div>
    <div> Hasło<br />
        <input type="text" name="password" placeholder="Hasło" value="" id="password" class="password" />
    </div>
    <div> Wyvbierz instancję<br />
        <select name="instance" id="instance">

        </select>
    </div>
    <br />
    <br />
    <button class="add_user btn btn-submit">Dodaj użytkownika</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script type="text/javascript">
    jQuery(document).ready(function($) {
        var id = '';
        var instance = '';
        var email = '';
        $.get("https://events.dealervolvo.pl/api/getUsers?token=test", function(data) {
            data = JSON.parse(data);
            var k = '';
            $('#instance').append('<option value="All">Wszystkie</option>');
            $.each(data, function(key, value) {

                if (k !== key) {
                    $('.result').prepend('<div class="result-record" data-key="' + key + '">' + key + '</div>');
                    $('#instance').append('<option value="' + key + '">' + key + '</option>');
                    k = key;
                }
                $.each(value, function(k, v) {

                    $('div.result-record[data-key="' + key + '"]').append('<div class="single_user" data-key="' + key + '" data-id="' + v._id + '"  data-email="' + v.email + '" id=""><a href="#ex1"><span class="edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M7.127 22.562l-7.127 1.438 1.438-7.128 5.689 5.69zm1.414-1.414l11.228-11.225-5.69-5.692-11.227 11.227 5.689 5.69zm9.768-21.148l-2.816 2.817 5.691 5.691 2.816-2.819-5.691-5.689z"/></svg></span></a><a href="#" class="hideUser">' + (v.active ? '<span class="red active" style="background:green;display:inline-block;width:10px;height:10px;margin-left:2px;border-radius:100%;"></span>' : '<span class="red inactive" style="background:red;display:inline-block;width:10px;height:10px;margin-left:2px;border-radius:100%;"></span>') + '</a> ' + v.user + ' - ' + v.email + '</div>');
                })


            })

            // $(".result").html(data);
            // alert("Load was performed.");
            $('.single_user a').on('click', function() {
                $('#ex1 .title').attr('data-id', $(this).parent().attr('data-id')).attr('data-instance', $(this).parent().attr('data-key')).text($(this).parent().attr('data-email'));
                email = $(this).parent().attr('data-email');
                id = $(this).parent().attr('data-id');
                instance = $(this).parent().attr('data-key');




            })
            $('a[href="#ex1"]').click(function(event) {

                $(this).modal();
                return false;
            });
            $('a[href="#addUser"]').click(function(event) {

                $(this).modal();
                return false;
            });
            $('#addUser button').click(function() {
                var instance = $('#instance option:selected').val();
                console.log(instance);
                var username = $('input#username').val();
                var email = $('input[type="email"]').val();
                var pass = $('#addUser input[name="password"]').val();
                if (instance == 'All') {                    
                    $.each($('#instance option'), function(key, value) {
                        var instance = $(this).val();
                        console.log(instance);
                        if ($(this).val() !== 'All') {
                            fetch('https://events.dealervolvo.pl/' + instance + '/api/cockpit/saveUser?token=4ca43516c3548033e78fa126f2ae9b', {
                                    method: 'post',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        user: {
                                            user: username,
                                            email: email,
                                            password: pass,
                                            active: true,
                                            group: 'admin'

                                        } // user data (user, name, email, active, group)
                                    })
                                })
                                .then(user => user.json())
                                .then(user => console.log(user));

                        }
                    });
                    alert('Użytkownik dodany');

                } else {
                    fetch('https://events.dealervolvo.pl/' + instance + '/api/cockpit/saveUser?token=4ca43516c3548033e78fa126f2ae9b', {
                            method: 'post',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                user: {
                                    user: username,
                                    email: email,
                                    password: pass,
                                    active: true,
                                    group: 'admin'

                                } // user data (user, name, email, active, group)
                            })
                        })
                        .then(user => user.json())
                        .then(user => console.log(user));
                    alert('Użytkownik dodany');
                }
            })
            $('.hideUser').click(function() {
                if ($(this).find('span.active').length > 0) {
                    var status = false;  
                    var messageBox = 'Konto wyłączone';              
                } else {
                    var status = true;
                    var messageBox = 'Konto włączone'
                }
                email = $(this).parent().attr('data-email');
                $.each($('.single_user[data-email="' + email + '"]'), function(key, value) {

                    id = $(this).attr('data-id');
                    instance = $(this).attr('data-key');
                    fetch('https://events.dealervolvo.pl/' + instance + '/api/cockpit/saveUser?token=4ca43516c3548033e78fa126f2ae9b', {
                            method: 'post',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                user: {
                                    _id: id,
                                    active: status
                                } // user data (user, name, email, active, group)
                            })
                        })
                        .then(user => user.json())
                        .then(user => console.log(user));

                })
                alert(messageBox);
            }) 
            $('#ex1 button').click(function() {
                if ($('#ex1 input').val().length < 4) {
                    alert('hasło jest za krótkie');
                    return false;
                }

                $.each($('.single_user[data-email="' + email + '"]'), function(key, value) {

                    id = $(this).attr('data-id');
                    instance = $(this).attr('data-key');
                    fetch('https://events.dealervolvo.pl/' + instance + '/api/cockpit/saveUser?token=4ca43516c3548033e78fa126f2ae9b', {
                            method: 'post',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                user: {
                                    _id: id,
                                    password: $('#ex1 input').val()
                                } // user data (user, name, email, active, group)
                            })
                        })
                        .then(user => user.json())
                        .then(user => console.log(user));

                })
                alert('hasło zmienione');


            })
        });


        $('.single_user span').click(function() {


        })

    })
</script>
<style>
    .hideUser svg {
        color: green;
        margin-left: 5px;
        width: 15px;
        height: 15px;
    }

    .add-user svg {
        width: 15px;
        height: 15px;
        display: inline-block;
        margin-right: 10px;
        margin-top: 10px;
        color: green;
    }

    #ex1 .title {
        display: block;
        width: 100%;
        padding: 10px 0;
        font-size: 20px;

    }

    .events>div {
        display: flex;
        flex-wrap: wrap;
    }

    .result-record {
        width: 20%;
        padding: 10px 5px;
        font-weight: bold;
        font-size: 16px;
    }

    .single_user {
        font-weight: normal;
        font-size: 14px;
        text-indent: 20px;
    }

    .single_user svg {
        width: 10px;
        height: 10px;
    }

    .single_user span:hover {
        cursor: pointer;
    }
</style>