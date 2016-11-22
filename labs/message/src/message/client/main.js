/**
 * 中心组件组织数据给各个组件
 * 子组件完成部分功能的渲染与数据计算
 */
var exports;
var SyncComponent = {
    loaded : {},
    src : "/component/",

    create : function(name){
        if(this.loaded[name]){
           return this.loaded[name];
        }

        var that = this;
        Vue.component(name, function(resolve) {
            var scriptUrl = that.src + name + ".js";
            $.get(scriptUrl, function(script) {
                if(!/html$/.test(exports.template)) {
                    resolve(exports);
                } else {
                    var tplUrl = that.src + "template/" + name + ".html";
                    $.get(tplUrl, function(template) {
                        exports.template = template;
                        resolve(exports);
                    }, 'text');
                }
            }, 'script');
        });

        this.loaded[name] = Vue.component(name);
        return this.loaded[name];
    }
};

var Components = [
    { label : 'home', api : 'welcome' },
    { label : '订阅者', api : 'customer' },
    { label : '状态', api : 'status'},
    { label : '消息', api : 'message' },
    { label : '管理', api : 'manager' }
];

// 根组件
var App = Vue.extend({
    data: function() {
        return { apis: Components }
    }
});

// 定义路由
var router = new VueRouter();

router.on('/', {
    component : SyncComponent.create("welcome")
});

Components.forEach(function(item){
    router.on('/' + item.api, {
        component : SyncComponent.create(item.api)
    });
});

// start app
router.start(App, '#app');