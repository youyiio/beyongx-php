//表格中的全选操作
$('.ajax-check-all').click(function(event) {
    var checkboxs = $(this).parents('table').find('input.ajax-check');
    var allChecked = $(this).prop('checked');
    if (allChecked) {
        checkboxs.prop('checked', 'checked');
    } else {
        checkboxs.prop('checked', false);
    }
});
//补充动态绑定，ajax-check-all的点击事件（hack, footable导致无法点击)
$(document).on("click", ".ajax-check-all", function () {
    var checkboxs = $(this).parents('table').find('input.ajax-check');
    var allChecked = $(this).prop('checked');
    if (allChecked) {
        checkboxs.prop('checked', 'checked');
    } else {
        checkboxs.prop('checked', false);
    }
});
//获取表格中checked的值的集合
function getAjaxCheckedValues() {
    var jsCheck = $('.ajax-check:checked');
    var value = [];
    if (jsCheck.length < 1) {
        //alert('请选择列表!');
        return false;
    }

    $.each(jsCheck, function(i, v) {
        value.push($(v).val());
    });
    return value;
}

//消息通知设置
// toastr.options = {
//     "closeButton": false, //关闭按钮
//     "debug": false, //调试开关
//     "progressBar": true, //进度条
//     "preventDuplicates": true, //防止重复
//     "positionClass": "toast-top-center", //顶部中间
//     "onclick": null, //点击回调
//     "showDuration": "400", //显示时间
//     "hideDuration": "600", //隐藏时间
//     "timeOut": "1000", //缓冲时间
//     "extendedTimeOut": "1000",
//     "showEasing": "swing", //显示动画
//     "hideEasing": "linear", //隐藏动画
//     "showMethod": "fadeIn", //显示方式
//     "hideMethod": "fadeOut" //隐藏方式
// };
//使用layui消息通知设置
var layer;
layui.use(['layer'], function(){
    layer = layui.layer;
});

/**
 * form ajax 表单异步提交,直接替换button[submit]的默认行为
 *用法：<form> class增加ajax-form
 * */
$(function() {
    if (typeof rules == 'undefined') {
        $("form.ajax-form").each(function () {
            $(this).validate({
                errorElement : 'div',
                errorClass : 'help-block',
                //自定义错误消息放到哪里
                errorPlacement : function(error, element) {
                    //element.next().remove();//删除显示图标
                    //element.after('<span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>');
                    element.closest('.form-group').append(error);//显示错误消息提示
                },
                //给未通过验证的元素进行处理
                highlight : function(element) {
                    $(element).closest('.form-group').addClass('has-error has-feedback');
                },
                //验证通过的处理
                success : function(label) {
                    var el=label.closest('.form-group').find("input");
                    //el.next().remove();//与errorPlacement相似
                    //el.after('<span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>');
                    label.closest('.form-group').removeClass('has-error').addClass("has-feedback has-success");
                    label.remove();
                },
                submitHandler : function(form) {
                    ajaxFormSubmitFunc(form);
                }
            });
        });

    } else {
        $("form.ajax-form").each(function() {
            $(this).validate(rules);
        })
    }
});
var ajaxFormSubmitFunc = function(form){
    //e.preventDefault();

    var _this = $(form);
    _this.prop('disabled',true);
    var input = _this.serialize();
    var url = _this.attr('action');
    var method = _this.attr('method') || 'POST';
    var btn = _this.find('button[type="submit"]');
    var successCallback = _this.data('successCallback');
    var errorCallback = _this.data('errorCallback');
    btn.prop('disabled',true).find('i').remove();
    btn.prepend('<i class="fa fa-spinner fa-spin"></i>');

    var loading = layer.load(1);
    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        data: input,
    })
    .done(function(res) {
        //console.log("success");
        if (res.code) {
            layer.msg(res.msg);

            btn.find('i').remove();
            btn.prepend('<i class="fa fa-check"></i>');
            if (typeof successCallback != 'undefined' && isExitsFunction(successCallback)) {
                var callback = eval(successCallback);
                callback(_this,res);
            } else {
                if (res.url) {
                    if (res.url == 'reload') {
                        setTimeout(function(){
                            location.reload();
                        },1200);
                    } else {
                        setTimeout(function(){
                            location.href=res.url;
                        },1200);
                    }
                }
            }
        } else {
            layer.msg(res.msg, function(){});
            btn.prop('disabled',false).find('i').remove();
            if (typeof errorCallback != 'undefined' && isExitsFunction(errorCallback)) {
                var callback = eval(errorCallback);
                callback(_this,res);
            }
        }
    })
    .fail(function() {
        //console.log("error");
    })
    .always(function() {
        //console.log("complete");
        _this.prop('disabled', false);
        btn.prop('disabled', false).find('i').remove();

        layer.close(loading);
    });

};

/**
 * 链接跳转: 按钮模拟a 连接操作，如打开页面或弹窗加载内容
 * 用法：<button> ,class添加 link-btn;
 *    属性data-action为url，data-title提示标题；data-subtitle提示子标题; data-target为行为，_bank打开新页面,_modal弹出模态窗口
 */
$(document).on('click', '.link-btn', function(e) {

    var _this = $(this);
    var modalType = _this.data('modal-type');
    var action = _this.data('action');
    var title = _this.data('title');
    var subTitle = _this.data('subtitle');
    var target = _this.data('target');
    var modalSize = _this.data('modal-size');
    //弹出modal,异步显示
    if (target == '_blank' || target == '_modal') {
        var modalId = modalType == "form" ? "#formModal" : "#viewModal";
        var options = [

        ];

        $(modalId).find('.modal-title').html(title);
        if (subTitle == undefined || subTitle == "") {
            $(modalId).find('.modal-title').next('small').hide();
        }
        if (modalSize != undefined && modalSize != "") {
            $(modalId).find('.modal-dialog').addClass(modalSize);
        }

        //打开窗口
        $(modalId).modal(options);
        $(modalId).find(".modal-body").load(action, null, function(response, status) {
            if (status != 'success') {
                //console.log(response);
            } else {
                $(modalId).find('.modal-body .full-height-scroll').slimscroll({
                    height: '100%'
                });
            }
        });

        //隐藏窗口后恢复
        $(modalId).on('hidden.bs.modal', function(e) {
            $(modalId).find('.modal-title').html('标题');
            $(modalId).find('.modal-title').next('small').html('子标题，说明备注').show();
            $(modalId).find('.modal-body').html('<div class="sk-spinner sk-spinner-wave">\r' +
                '<div class="sk-rect1"></div>\r' +
                '<div class="sk-rect2"></div>\r' +
                '<div class="sk-rect3"></div>\r' +
                '<div class="sk-rect4"></div>\r' +
                '<div class="sk-rect5"></div>\r' +
                '</div>');

            //移除按钮事件;
            $(modalId).find('.modal-footer button[type=submit]').unbind('click');
        });
    } else if (target == undefined || target == '_self') {
        window.location.href = $(this).data('action');
    } else {
        window.location.href = $(this).data('action');
    }

});

/**
 * 异步交互：按钮点击动作,直接ajax请求,不弹出对话框确认
 * 用法：<button> class添加ajax-btn, 属性data-action请求url
 *
 */
$(document).on('click','.ajax-btn',function(e){
    e.preventDefault();
    var _this = $(this);
    var url = _this.data('action');
    var successCallback = _this.data('successCallback');
    var errorCallback = _this.data('errorCallback');
    _this.prop('disabled','disabled');
    $.get(url, function(data) {
        if (data.code) {
            layer.msg(data.msg);
            if (typeof successCallback != 'undefined' && isExitsFunction(successCallback)) {
                var callback = eval(successCallback);
                callback(_this,data);
            } else {
                setTimeout(function(){
                    location.reload();
                },1500);
            }
        } else {
            layer.msg(data.msg, function(){});
            _this.prop('disabled',false);
            if (typeof errorCallback != 'undefined' && isExitsFunction(errorCallback)) {
                errorCallback(_this,data);
            }
        }
    });
});

/**
 * 异步交互：按钮点击事件，弹出确认对话框，确认后ajax操作
 * 用法：<button>class添加ajax-btn-warning, 属性data-action为请求url
 * data-title提示标题，data-text为提示内容
 */

$(document).on('click','.ajax-btn-warning',function(e){
    e.preventDefault();
    var _this = $(this);
    var title = _this.data('title')!=undefined?_this.data('title'):'提示';
    var text = _this.data('text')!=undefined?_this.data('text'):'确认要执行此操作吗?';
    layer.confirm(text, {
        title: title,
        btn: ['确认', '取消']
    }, function() {
        layer.closeAll('dialog');

        var url = _this.data('action');
        var successCallback = _this.data('successCallback');
        var errorCallback = _this.data('errorCallback');
        _this.prop('disabled','disabled');
        $.get(url, function(data) {
            if (data.code) {
                layer.msg(data.msg);
                if (typeof successCallback != 'undefined' && isExitsFunction(successCallback)) {
                    var callback = eval(successCallback);
                    callback(_this,data);
                } else {
                    setTimeout(function(){
                        location.reload();
                    },1500);
                }
            } else {
                layer.msg(data.msg, function(){});
                _this.prop('disabled',false);
                if (typeof errorCallback != 'undefined' && isExitsFunction(errorCallback)) {
                    errorCallback(_this,data);
                }
            }
        });
    }, function() {
        layer.closeAll('dialog');
    });
});

/**
 * 异步交互：a链接
 *用法: <a> class添加ajax-a, 属性href为目标链接
 */
$(document).on('click','.ajax-a',function(e){
    e.preventDefault();
    var _this = $(this);
    if (_this.hasClass('disabled')) {
        return false;
    }
    _this.addClass('disabled');
    var url = _this.attr('href');
    var successCallback = _this.data('successCallback');
    var errorCallback = _this.data('errorCallback');
    $.get(url, function(data) {
        if (data.code) {
            layer.msg(data.msg);
            if (typeof successCallback != 'undefined' && isExitsFunction(successCallback)) {
                var callback = eval(successCallback);
                callback(_this,data);
            } else {
                setTimeout(function(){
                    location.reload();
                },1500);
            }
        } else {
            layer.msg(data.msg, function(){});
            _this.removeClass('disabled');
            if (typeof errorCallback != 'undefined' && isExitsFunction(errorCallback)) {
                errorCallback(_this,data);
            }
        }
    });
});

/**
 * iframe内点击a.J_addMenuTab 打开新tab页显示内容
 * <a class="J_addMenuTab" href=""></a>
 * <button class="J_addMenuTab" data-menu-name="" data-url=""></button>
 */
$(document).on('click', '.J_addMenuTab', function(e) {
    e.preventDefault();

    var _this = $(this);
    var url = _this.attr('href');
    url = typeof url == 'undefined' ? _this.data('url') : url;
    var menuName = _this.data('menu-name');//不支持menuName驼峰定义
    menuName = typeof menuName == 'undefined' ? _this.text() : menuName;

    var dataUrl = $('.J_menuTab.active', parent.document).data('id');
    //如果当前地址已经与新打开的地方一至的话，重载当前列表
    if (dataUrl == url) {
        window.location.href = url;
    } else {
        parent.addMenuTab(url, menuName, 0);
    }
});

//更新验证码
function changeCode() {
    $('#code').click();
}

//是否存在指定函数
function isExitsFunction(funcName) {
    try {
        if (typeof(eval(funcName)) == "function") {
            return true;
        }
    } catch(e) {}
    return false;
}
//是否存在指定变量
function isExitsVariable(variableName) {
    try {
        if (typeof(variableName) == "undefined") {
            //alert("value is undefined");
            return false;
        } else {
            //alert("value is true");
            return true;
        }
    } catch(e) {}
    return false;
}