
Vue.directive('disable', {
    bind: function () {
        this.el.disabled = this.vm.active && this.vm.active.id > 0;
    },

    update : function(value){
        this.el.disabled = value > 0;
    }
});

Vue.filter('enableName', function (value) {
    return value > 0 ? "有效" : "无效";
});

var exports = {
    template: 'customer.html',
    data :  function(){
        return {
            order: -1,
            customer : [],
            active : [],
            message : '',
            isError : false
        }
    },

    methods : {
        update : function(done){
            var that = this;
            $.getJSON('/cgi/customer/list', function(rs){
                that.$set('customer', rs);
                if(typeof done == 'function'){
                    done();
                }
            });
        },

        edit : function(x, add){
            var c = add
                ? {
                    id : null,
                    events : null,
                    url : 'http://',
                    tryTimes : '',
                    enable : 1
                    }
                : x;
            this.$set('active', c);
            $('#myModal').modal();
        },

        add : function(){
            this.edit(null, true);
        },

        reverse : function() {
            this.$set("order", this.order * -1)
            console.log(this.order);
        },

        save : function(){
            var that = this;
            $.getJSON('/cgi/customer/save', this.active, function(rs){
                if(rs.code == 200) {
                    that.update();
                    that.$set('isError', false);
                    that.$set('message', "操作成功");
                    $('#myModal').modal('hide');
                } else {
                    that.$set('isError', true);
                    that.$set("message", rs.message);
                }
            });
        }
    },

    computed: {
        showMessage : function () {
            // `this` points to the vm instance
            return !this.isError && this.message.length > 0;
        },

        showError : function () {
            // `this` points to the vm instance
            return this.isError;
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
        this.update(done);
    },

    ready : function(){
        console.log("ready");
    },

    route: {
        activate: function (transition) {
            this.update(function(){
                transition.next();
            });
        }
    }
}