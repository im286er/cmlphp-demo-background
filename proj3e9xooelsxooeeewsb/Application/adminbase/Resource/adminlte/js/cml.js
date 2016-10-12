/**
 * 定义cml模块
 *
 */
define('cml', ['jquery', 'vue', 'layer'], function($, Vue, layer) {
    layer.config({
        path: window.laydatepath + '../layer/' //layer.js所在的目录，可以是绝对目录，也可以是相对目录
    });

    cml = {
        loadingIndex : 0,
        currentDataPageUrl : '',

        initPage: function(totalPage, pageUrl, searchUrl, totalCount) {
            require(['laypage','laydate', 'vue', 'cml'], function(laypage, laydate,Vue) {
                laydate.skin('default');
                laypage.dir = laypagestyle;

                window.list = new Vue({
                    el: ".data_form_box",
                    data: {
                        list: [],
                        totalCount : parseInt(totalCount)
                    },
                    created : function() {
                        $('.template').show();
                    },
                    methods : {
                        add : function(title, form_url, save_url, event) {console.log(event);
                            cml.form.add(title, form_url, save_url);
                        },
                        edit : function(title, form_url, save_url) {
                            cml.form.edit(title, form_url, save_url);
                        },
                        del : function(url, id) {
                            cml.form.del(url, id);
                        },
                        disable : function(url) {
                            cml.form.disable(url);
                        }
                    }
                });

                laypage({
                    cont: $('.show_page'), //容器。值支持id名、原生dom对象，jquery对象,
                    pages: totalPage, //总页数
                    curr : 1,
                    skin: '#337ab7', //加载内置皮肤，也可以直接赋值16进制颜色值，如:#c00
                    groups: 5,//连续显示分页数
                    jump: function(e) { //触发分页后的回调
                        cml.loadAjaxPage(pageUrl + $('.search_form').serialize() +'&page=' + e.curr);
                    }
                });

                $('.btn-search').click(function(e) {
                    e.preventDefault();
                    window.location.href = searchUrl + $('.search_form').serialize();
                });
            });
        },

        loadAjaxPage: function(url) {
            cml.currentDataPageUrl = url;
            cml.loadUrl(url, 'json', function (res) {
                window.list.$data.list = res.data;
                setTimeout(function() {
                    window.scrollTo(0, 0);
                }, 10);
            });
        },

        /**
         * 加载页面并弹窗
         *
         * @param url 要加载的页面的url
         * @param title 弹窗的标题
         * @param func 弹窗里确定按钮执行的操作
         */
        getDataShowPop: function (url, title, func) {
            this.loadUrl(url, 'html',function(data) {
                cml.showPopBox(data, 1, title, func);
            });
        },

        /**
         * 加载一个页面
         * @param url
         * @param type html /json
         * @param func
         */
        loadUrl : function(url, type, func) {
            cml.showLoading();
            $.ajax({
                url:url,
                dataType : type,
                success  : function(data) {
                    cml.closeLoading();
                    func(data);
                },
                error  : function (XMLHttpRequest, textStatus, errorThrown) {
                    cml.closeLoading();
                    func(XMLHttpRequest.responseText);
                }
            });
        },

        showTip : function(tip, func) {
          cml.showPopBox(tip, 2, '提示', func);
        },

        /**
         * 显示提示信息
         * @param content
         * @param type
         * @param title
         * @param func
         */
        showPopBox: function (content, type, title, func) {
            if (typeof(type) == 'undefined') {
                type = 1;
            }

            if (typeof(func) == 'undefined') {
                okfunc = function (index) {
                    cml.closePopBox(index);
                };
            } else if(!isNaN(func)) {
                okfunc = function(index){
                    setTimeout(function() {
                        cml.closePopBox(index);
                    }, func);
                }
            } else {
                okfunc = function(index) {
                    $res = func(index);
                    if (typeof($res) == 'undefined' || $res) {
                        cml.closePopBox(index);
                    }
                }
            }

            if (type==1) {
                layer.open({
                    type: 1,
                    maxmin:true,
                    area: ['auto', 'auto'],
                    title : title,
                    content: content,
                    btn : ['确认', '取消'],
                    yes : okfunc,
                    cancel : function(index){
                        cml.closePopBox(index)
                    }
                });
            } else {
                layer.alert(content, function(index){
                    okfunc(index);
                });
            }
        },

	    /**
	     * 关闭弹窗
         */
        closePopBox : function(index) {
           layer.close(index);
        },

        /**
         * 保存或更新数据后重新载入当前iframe
         */
        reloadCurrentIframe : function(url, data) {
            window.location.reload();
        },

        /**
         * 显示确认框
         *
         * @param tip 确认文字
         * @param func 点击确认执行的函数
         */
        showConfirm : function(tip, func) {
            cml.showPopBox(tip, 2, '确认?', func);
        },
        /**
         * 显示Loading
         */
        showLoading: function () {
            cml.loadingIndex = layer.load(0, {shade: 0.15});
        },
        /**
         * 关闭loading
         */
        closeLoading: function () {
            cml.loadingIndex > 0 && layer.close(cml.loadingIndex);
            cml.loadingIndex = 0;
        },

        //显示提示
        showMsg : function(msg, autoCloseTime) {
            if(typeof (autoCloseTime) == 'undefined') {
                autoCloseTime = 10000;
            }
            cml.showPopBox(msg, 2, '提示', autoCloseTime, false, false);
        },

        form: {
            /**
             * 添加数据
             * @param title 表单标题
             * @param url 加载表单的url
             * @param purl 保存表单数据的url
             */
            add: function (title, url, purl) {
                if (typeof(event) != "undefined") {
                    if (typeof(event.preventDefault) == 'function') {
                        event.preventDefault();
                    } else {
                        window.event.returnValue = false;
                    }
                }

                cml.getDataShowPop(url, title, function (index) {
                    var form = $('form.data_forum');
                    if (form.valid == undefined || form.valid()) {
                        cml.showLoading();
                        $.ajax({
                            url:purl,
                            type : 'post',
                            dataType : 'json',
                            data : form.serialize(),
                            success  : function(data) {
                                cml.closeLoading();
                                if (data.code == 0) {
                                    cml.showTip(data.msg, function() {
                                        cml.loadAjaxPage(cml.currentDataPageUrl);
                                        if (typeof (window.list) != 'undefined') {
                                            window.list.$data.totalCount +=1;
                                        } else {
                                            cml.reloadCurrentIframe();
                                        }
                                        cml.closePopBox(index);
                                    });
                                } else {
                                    cml.showTip(data.msg);
                                }
                            },
                            error  : function (XMLHttpRequest, textStatus, errorThrown) {
                                cml.closeLoading();
                                cml.showTip(XMLHttpRequest.responseText);
                            }
                        });
                    }
                    return false;
                });
            },

            /**
             * 修改数据
             * @param title 表单标题
             * @param url 加载表单的url
             * @param purl 保存表单数据的url
             */
            edit: function (title, url, purl) {
                if (typeof(event) != "undefined") {
                    if (typeof(event.preventDefault) == 'function') {
                        event.preventDefault();
                    } else {
                        window.event.returnValue = false;
                    }
                }

                cml.getDataShowPop(url, title, function (index) {
                    var form = $('form.data_forum');
                    if (form.valid == undefined || form.valid()) {
                        cml.showLoading();
                        $.ajax({
                            url:purl,
                            type : 'post',
                            dataType : 'json',
                            data : form.serialize(),
                            success  : function(data) {
                                cml.closeLoading();
                                if (data.code == 0) {
                                    cml.showTip(data.msg, function() {
                                        if(purl.replace('.html', '').substr(-12) != 'saveSelfInfo') {
                                            cml.loadAjaxPage(cml.currentDataPageUrl);
                                        }
                                        cml.closePopBox(index);
                                    });
                                } else {
                                    cml.showTip(data.msg);
                                }
                            },
                            error  : function (XMLHttpRequest, textStatus, errorThrown) {
                                cml.closeLoading();
                                cml.showTip(XMLHttpRequest.responseText);
                            }
                        });
                    }
                    return false;
                });
            },

            /**
             * 删除数据的url
             *
             * @param url
             * @param id
             */
            del: function (url, id) {
                if (typeof(event) != "undefined") {
                    if (typeof(event.preventDefault) == 'function') {
                        event.preventDefault();
                    } else {
                        window.event.returnValue = false;
                    }
                }

                cml.showConfirm('确定要删除ID为'+ id + '的记录么？', function () {
                    cml.loadUrl(url, 'json', function(data) {
                        cml.showTip(data.msg, function () {
                            if (typeof (window.list) != 'undefined') {
                                cml.loadAjaxPage(cml.currentDataPageUrl);
                                window.list.$data.totalCount -=1;
                            } else {
                                cml.reloadCurrentIframe();
                            }
                        });
                    });
                });
            },

            /**
             * 禁用解禁的url
             *
             * @param url
             */
            disable: function (url) {
                cml.showConfirm('确定要操作么？', function () {
                    cml.closePopBox(2);
                    cml.loadUrl(url, 'json', function(data) {
                        cml.showTip(data.msg, function (index) {
                            cml.loadAjaxPage(cml.currentDataPageUrl);
                        });
                    });
                });
            }
        }
    };
    return cml;
});