/**
 * Created by Администратор on 13.04.2019.
 */
$( document ).ready(function() {
    $('.authorization').on('click', function() {
        console.log('click auth');
        $('.hidden_register').hide();
        $('.hidden_authorization').fadeIn(300);

    });
    $('.register').on('click', function() {
        $('.hidden_authorization').hide();
        $('.hidden_register').fadeIn(300);

    });
    $(document).on('click', '.like_repos', function(e) {
        var id = $(this).attr('data-id');
        var id_author = $(this).attr('data-id_author');
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(this).css({'color':'red'})
        $.ajax({
            /* the route pointing to the post function */
            url: '/postajax',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, id: id, id_author: id_author, action: 'like'},
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {

            }
        });

    });
    $(document).on('click', '.dislike_repos', function(e) {
        var id = $(this).attr('data-id');
        var id_author = $(this).attr('data-id_author');
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(this).css({'color':'#3ea6ff'});
        $(this).fadeOut(300);
        $.ajax({
            /* the route pointing to the post function */
            url: '/postajax',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, id: id, id_author: id_author, action: 'dislike'},
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {
                console.log(data);
            }
        });

    });
    $('.search_repos').on('click', function() {
        var user_name = $('.user_name').val();
        var search_word = $('.search_word').val();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        if(user_name != '' && search_word != '')
        {
            $('.loader').css({'display':'block'});
            $.ajax({
                /* the route pointing to the post function */
                url: '/postajax',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, user_name: user_name, search_word: search_word, action: 'search'},
                dataType: 'JSON',
                /* remind that 'data' is the response of the AjaxController */
                success: function (data) {
                    $('.loader').css({'display':'none'});
                    console.log(data.items);
                    var html = '';
                    var answerAPI = data.items;
                    if(typeof answerAPI != 'undefined')
                    {
                        answerAPI.forEach(function(element) {
                            var desc = '';
                            if(element.description == null)
                            {
                                desc = 'Нету описания';
                            }
                            else
                            {
                                desc = element.description;
                            }
                            html +='<div class="col-md-12 reposit_st">' +
                                '<div class="author_info">' +
                                '<img src="'+element.owner.avatar_url +'" alt="" class="avatar">' +
                                '<h4 class="owner_name">'+element.owner.login+'</h4>' +
                                '</div>' +
                                '<h3 data-url_repo="'+element.url+'" class="repos_name">'+element.name+'</h3>' +
                                '<span>'+ desc +'</span>'+
                                '</div>';
                        });
                        if(answerAPI.length <= 0)
                            html = '<div class="null_array"><h2>Ничего не найдено!</h2> </div>';
                        $('.all_repos').html(html);
                    }
                }
            });
        }
        else
        {
            $('.search_word').addClass('error_input');
        }
    });
    $('.show_all_repos').on('click', function() {
        $('.loader').css({'display':'block'});
        var cur_user = $('.cur_user').val();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            /* the route pointing to the post function */
            url: '/postajax',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, cur_user:cur_user, action: 'show_all'},
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {
                $('.loader').css({'display':'none'});
                var html = '';
                var answerAPI = data;
                if(typeof answerAPI != 'undefined')
                {
                    answerAPI.forEach(function(element) {
                        var desc = '';
                        if(element.description == null)
                        {
                            desc = 'Нету описания';
                        }
                        else
                        {
                            desc = element.description;
                        }
                        html +='<div class="col-md-12 reposit_st">' +
                            '<div class="author_info">' +
                            '<img src="'+element.owner.avatar_url +'" alt="" class="avatar">' +
                            '<h4 class="owner_name">'+element.owner.login+'</h4>' +
                            '</div>' +
                            '<h3 data-url_repo="'+element.url+'" class="repos_name">'+element.name+'</h3>' +
                            '<span>'+ desc +'</span>';
                        if(element.like ==1 && element.deslike ==1)
                        {
                            html += '</div>';
                        }
                        else if (element.like == 0)
                        {
                            html += '<i class="fa fa-heart like_repos" data-id="'+element.id+'" data-id_author="'+element.owner.id+'"></i>';
                            html += '</div>';
                        }
                        else if(element.like ==1 && element.deslike ==0)
                        {
                            html += '<i class="fa fa-thumbs-down dislike_repos" data-id="'+element.id+'" data-id_author="'+element.owner.id+'"></i>';
                            html += '</div>';
                        }



                    });
                    if(answerAPI.length <= 0)
                        html = '<div class="null_array"><h2>Ничего не найдено!</h2> </div>';
                    $('.all_repos').html(html);
                }
            }
        });
    });
    $(document).on('click', '.repos_name', function(e) {
        $('.loader').css({'display':'block'});
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var url_value = $(this).attr('data-url_repo');
        $.ajax({
            /* the route pointing to the post function */
            url: '/postajax',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN,  url_value:url_value, action: 'show_one'},
            dataType: 'JSON',
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {
                $('.loader').css({'display':'none'});
                var html = '';
                var desc = '';
                if(data.description == null)
                {
                    desc = 'Нету описания';
                }
                else
                {
                    desc = data.description;
                }
                html +='<div class="col-md-12 reposit_st">' +
                    '<div class="author_info">' +
                    '<img src="'+data.owner.avatar_url +'" alt="" class="avatar">' +
                    '<h4>'+data.owner.login+'</h4>' +
                    '</div>' +
                    '<h3>'+data.name+'</h3>' +
                    '<span>'+ desc +'</span>'+
                    '<i class="fa fa-thumbs-down icon-down">'+data.count_like+'</i>'+
                    '<i class="fa fa-heart icon-heart">'+data.count_deslike+'</i>'+
                    '</div>';
                if(data.length <= 0)
                    html = '<div class="null_array"><h2>Ничего не найдено!</h2> </div>';
                $('.all_repos').html(html);
            }
        });

    });


});
