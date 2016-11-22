var exports = {
    template: 'message.html',
    data :  function(){
        return {
            status : {},
        }
    },

    methods : {
        update : function(done){
            var that = this;
            $.getJSON('/cgi/message/status', function(rs){
                that.$set('status', rs.data);
                console.log(rs.data);
                if(typeof done == 'function'){
                    done();
                }
            });
        },
    },

    activate: function (done) {
        this.update(done);
    },

    route: {
        activate: function (transition) {
            this.update(function(){
                transition.next();
            });
        }
    }
}