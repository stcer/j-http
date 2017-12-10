
var JChatData = (function(){
    var now = function() {
        var date = new Date();
        return ''
            // + date.getFullYear() + "-"
            // + (date.getMonth() + 1) + "-"
            // + date.getDate()
            // + ' '
            + date.getHours() + ':'
            + date.getMinutes() + ':'
            + date.getSeconds()
        ;
    };

    var F = function(chat){
    };

    $.extend(F.prototype, (new JEvent()), {
        data : {
            cmd : '',
            messages : [],
            toUser : {user: '所有用户', fd : 0},
            isSend : false,
            online : [],
            user: {
                ip : 'unknown',
                fd: 'unknown',
                user: 'Me'
            }
        },

        addMessage : function(msg, type) {
            if(typeof msg != 'object'){
                msg = {
                    msg : msg
                }
            }
            msg.type = type;
            if(!msg.date){
                msg.date = now();
            }

            if(!msg.user){
                msg.user = 'System';
            }
            this.data.messages.push(msg);
            this.trigger('message.add', msg);
        },

        isSend : function(f) {
            this.data.isSend = f;
        },

        addOnline : function(user){
            user.offline = false;
            this.data.online.push(user);
        },

        offline : function(user) {
            for(var i in this.data.online){
                if(this.data.online[i].fd === user.fd){
                    this.data.online[i].offline = true;
                    break;
                }
            }
        },

        rename : function(data) {
            for(var i in this.data.online){
                if(this.data.online[i].fd === data.fd){
                    this.data.online[i].user = data.user;
                    break;
                }
            }
        },

        login : function(data){
            this.data.user = data;
        }
    });

    return F;
})();
