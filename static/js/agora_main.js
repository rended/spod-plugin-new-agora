AGORAMAIN = {
    _selected_Room:''
};

AGORAMAIN.init = function(agora_id)
{
    AGORAMAIN._selected_Room = agora_id;

    $("#agora_room_container").perfectScrollbar();
    $(".right_scroller_container").perfectScrollbar();


    $(".agora_room").on('click', AGORAMAIN.handleAgoraRoomSelection);
    $(".tab").on('click', AGORAMAIN.handleAgoraRoomTab);

    $("#agora_header_add").on('click', function(){
        previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomCreator', {} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
    });

    $(".add_suggested_dataset").on('click', function(){});

    $("#agora_enter_button").on('click', function(){
        console.log("ENTER INTO " + AGORAMAIN._selected_Room);
        window.open("/agora/" + AGORAMAIN._selected_Room,"_self");
    });
};

AGORAMAIN.handleAgoraRoomTab = function()
{
    $(".tab").removeClass("selected");
    $(this).addClass("selected");
};

AGORAMAIN.handleAgoraRoomSelection = function(e)
{
    var room_id = e.currentTarget.id.replace("agora_room_", "agora_room_detail_");
    AGORAMAIN._selected_Room = e.currentTarget.id.replace("agora_room_", "");

    $(".agora_room").removeClass("selected");
    $(".box").removeClass("selected");

    $(".detail_selected_agora").removeClass("detail_selected_agora");
    $("#"+room_id).addClass("detail_selected_agora");


    var box = $(this).find(".box")[0];
    $(this).addClass("selected");
    $(box).addClass("selected");
};

AGORAMAIN.addNewRoom = function(event)
{
    console.log(event);
};