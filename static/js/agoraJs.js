function agoraJs(elem, entityId, endpoint, level, parentId) {
    this.elem = elem;
    this.entityId = entityId;
    this.endpoint = endpoint;
    this.level = level;
    this.parentId = parentId;
};

agoraJs.prototype = (function(){

    var _elem;
    var _entityId;
    var _endpoint;
    var _level;
    var _parentId;
    var _sentiment;

    var initialize_text_area = function(elem, entityId, endpoint) {
        _elem = elem;
        _entityId = entityId;
        _endpoint = endpoint;
        _level = 0;
        _parentId = _entityId;
        _sentiment = 0;

        _elem.keyup(return_handler);
    };

    var set_parentId = function(parentId){
        _parentId = parentId;
    };

    var set_sentiment = function(sentiment){
        _sentiment = sentiment;
    };

    var set_level = function (level) {
        _level = level;
    };

    var return_handler = function (e) {
        var key = e.which || e.keyCode;
        if (key === 13) { // 13 is enter
            handle_message(_elem.val());
        }
    };

    var submit = function () {
        handle_message(_elem.val());
    };

    var handle_message = function(message){
        console.log(message);

        var send_data = {
            comment: message,
            entityId: _entityId,
            parentId: _parentId,
            level: _level,
            sentiment: _sentiment
        };

        $.ajax({
            type: 'POST',
            url : _endpoint,
            data: send_data,
            dataType : 'JSON',
            success : on_request_success,
            error: on_request_error
        });
    };

    var on_request_success = function(raw_data){
        try {
            console.log(raw_data);
        } catch (e){
            console.log("Error in on_request_success");
        }
    };

    var on_request_error = function( XMLHttpRequest, textStatus, errorThrown ){
        OW.error(textStatus);
    };

    return {
        construct : agoraJs,

        init : function () {
            initialize_text_area(this.elem, this.entityId, this.endpoint);
        },

        submit : function () {
            submit();
        },

        set_parentId : function (parentId) {
            set_parentId(parentId);
        },

        set_sentiment : function (sentiment) {
            set_sentiment(sentiment);
        },

        set_level : function (level) {
            set_level(level);
        }
    };

})();