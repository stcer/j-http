
var Chat = new JChat('ws://192.168.0.175:9503');

Chat.data.on('message.add', function(){
    // for vue dom
    setTimeout(function(){
        var elBox = $('#messages');
        elBox.scrollTop(elBox[0].scrollHeight - elBox.height() + 250);
    }, 100);
});

Vue.component('chat', {
    template : '#chatTemplate',

    data : function(){
        return Chat.data.data;
    },

    methods: {
        execute : function() {
            Chat.data.addMessage({
                'user' : this.user.user,
                'msg' : this.cmd,
                toUser : this.toUser
            }, 'me');

            // send to server
            Chat.conn.send({
                cmd : 'message',
                data : this.cmd,
                toUser : this.toUser.fd
            }, true);

            this.cmd = '';
        },

        rename : function(){
            // send to server
            Chat.conn.send({
                cmd : 'rename',
                name : this.cmd
            }, true);

            this.user.user = this.cmd;
            this.cmd = '';
        },

        selUser : function(user){
            this.toUser = user;
            console.log(user);
        }
    }
});

new Vue({el: '#app'});
