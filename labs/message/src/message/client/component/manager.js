var exports = {
    template: 'manager.html',
    data :  function(){
        return {
            code : 0,
            msg : ''
        }
    },

    methods : {
        reload : function(){
            this.message = '';
            var that = this;
            $.getJSON('/cgi/manager/reload', function(rs){
                console.log(rs.message);
                that.$set('msg', rs.message);
                that.$set('code', rs.code);
            });
        },

        close : function(){
            this.msg = "还是不要关闭的好,关闭了就不能陪你玩了";
            this.code = 500;
        }
    },

    computed: {
        showMessage : function () {
            // `this` points to the vm instance
            return this.msg.length > 0;
        },

        isError : function(){
            return this.code != 200;
        },

        classObject : function() {
            return {
                'alert' : true,
                'alert-success' : !this.isError,
                'alert-danger' : this.isError
            }
        }
    },

    activate: function (done) {
        done();
    }
};