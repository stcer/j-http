var exports = {
    template: 'status.html',
    data :  function(){
        return {
            status : []
        }
    },

    methods : {
        update : function(done){
            var that = this;
            $.getJSON('/cgi/manager/status', function(rs){
                that.$set('status', rs.rs);
                if(typeof done == 'function') {
                    done();
                } else {
                    that.$set('msg', "flush success");
                }
            });
        }
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