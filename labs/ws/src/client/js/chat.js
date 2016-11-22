var JChat =  function(address) {
    this.conn = new JSocket(address, this);
    this.data = new JChatData(this);
    this.commands = new JCommands(this);
};

JChat.prototype = {
    onOpen : function(e) {
        this.conn.send({cmd : 'allOnline'});
    },

    onMessage : function(data, e) {
        var callback = this.commands[data.cmd];
        if(typeof callback == 'function'){
            callback.call(this.commands, data);
        } else {
            this.data.addMessage({
                user : 'error',
                msg : 'Invalid response'
            });
        }
    },

    onClose : function(e) {
        this.data.addMessage('closed', 'cmd');
    },

    addCmd : function(cmd, callback){
        if(typeof cmd == 'object'){
            $.extend(this.commands, cmd);
        } else {
            this.commands[cmd] = callback;
        }
    }
};

var JCommands = function (chat) {
    this.data = chat.data;
};

JCommands.prototype = {
    message: function (data) {
        this.data.addMessage(data);
    },

    over: function () {
        this.data.addMessage("execute over", 'cmd');
    },

    allOnline: function (data) {
        for (var i in data.data) {
            this.data.addOnline(data.data[i]);
        }
    },

    online: function (data) {
        var user = data.user;
        this.data.addOnline(user);
        this.data.addMessage({
            'msg': user.user + ' login'
        });
    },

    offline: function (data) {
        var user = data.user;
        this.data.offline(user);
        this.data.addMessage({
            'msg': user.user + ' logout',
        });
    },

    rename: function (data) {
        this.data.rename(data);
        this.data.addMessage({
            'msg': data.msg,
        });
    },

    login: function (data) {
        var user = data.user;
        user.me = true;

        this.data.login(user);
        this.data.addOnline(user);

        this.data.addMessage({
            'msg': "登录成功, 用户名为:" + user.user,
        }, 'cmd');
    }
};