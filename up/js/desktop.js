/**
 * Created by Yuri2 on 2017/7/10.
 */
window.Win10 = {  // 云桌面整体设置
    _version: 'v1.1.2.4',
    _debug: true,
    _bgs: {
        main: '',
        mobile: '',
    },
    _countTask: 0,
    _newMsgCount: 0,
    _animated_classes: [],
    _animated_liveness: 0,
    _switchMenuTooHurry: false,
    _lang: 'unknown',
    _previousTime: 0,
    _iframeOnClick: {
        resolution: 200,
        iframes: [],
        interval: null,
        Iframe: function() {
            this.element = arguments[0];
            this.cb = arguments[1];
            this.hasTracked = false;
        },
        track: function(element, cb) {
            this.iframes.push(new this.Iframe(element, cb));
            if (!this.interval) {
                var _this = this;
                this.interval = setInterval(function() { _this.checkClick(); }, this.resolution);
            }
        },
        checkClick: function() {
            if (document.activeElement) {
                var activeElement = document.activeElement;
                for (var i in this.iframes) {
                    var eid = undefined;
                    if ((eid = this.iframes[i].element.id) && !document.getElementById(eid)) {
                        delete this.iframes[i];
                        continue;
                    }
                    if (activeElement === this.iframes[i].element) { // user is in this Iframe
                        if (this.iframes[i].hasTracked === false) {
                            this.iframes[i].cb.apply(window, []);
                            this.iframes[i].hasTracked = true;
                        }
                    } else {
                        this.iframes[i].hasTracked = false;
                    }
                }
            }
        }
    },
    _iframe_click_lock_children: {},
    _renderBar: function() {
        //调整任务栏实例的宽度
        if (this._countTask <= 0) { return; } //防止除以0
        var btns = $("#win10_btn_group_middle>.btn");
        btns.css('width', ('calc(' + (1 / this._countTask * 100) + '% - 1px )'))
    },
    _handleReady: [],
    _hideShortcut: function() {
        var that = $("#win10 #win10-shortcuts .shortcut");
        that.removeClass('animated flipInX');
        that.addClass('animated flipOutX');
    },
    _showShortcut: function() {
        var that = $("#win10 #win10-shortcuts .shortcut");
        that.removeClass('animated flipOutX');
        that.addClass('animated flipInX');
    },
    _checkBgUrls: function() {
        var loaders = $('#win10>.img-loader');
        var flag = false;
        if (Win10.isSmallScreen()) {
            if (Win10._bgs.mobile) {
                loaders.each(function() {
                    var loader = $(this);
                    if (loader.attr('src') === Win10._bgs.mobile && loader.hasClass('loaded')) {
                        Win10._setBackgroundImg(Win10._bgs.mobile);
                        flag = true;
                    }
                });
                if (!flag) {
                    //没找到加载完毕的图片
                    var img = $('<img class="img-loader" src="' + Win10._bgs.mobile + '" />');
                    $('#win10').append(img);
                    Win10._onImgComplete(img[0], function() {
                        img.addClass('loaded');
                        Win10._setBackgroundImg(Win10._bgs.mobile);
                    })
                }
            }
        } else {
            if (Win10._bgs.main) {
                loaders.each(function() {
                    var loader = $(this);
                    if (loader.attr('src') === Win10._bgs.main && loader.hasClass('loaded')) {
                        Win10._setBackgroundImg(Win10._bgs.main);
                        flag = true;
                    }
                });
                if (!flag) {
                    //没找到加载完毕的图片
                    var img = $('<img class="img-loader" src="' + Win10._bgs.main + '" />');
                    $('#win10').append(img);
                    Win10._onImgComplete(img[0], function() {
                        img.addClass('loaded');
                        Win10._setBackgroundImg(Win10._bgs.main);
                    })
                }
            }
        }

    },
    _startAnimate: function() {
        setInterval(function() {
            var classes_lenth = Win10._animated_classes.length;
            var animated_liveness = Win10._animated_liveness;
            if (animated_liveness === 0 || classes_lenth === 0 || !$("#win10-menu").hasClass('opened')) { return; }
            $('#win10-menu>.blocks>.menu_group>.block').each(function() {
                if (!$(this).hasClass('onAnimate') && Math.random() <= animated_liveness) {
                    var that = $(this);
                    var class_animate = Win10._animated_classes[Math.floor((Math.random() * classes_lenth))];
                    that.addClass('onAnimate');
                    setTimeout(function() {
                        that.addClass(class_animate);
                        setTimeout(function() {
                            that.removeClass('onAnimate');
                            that.removeClass(class_animate);
                        }, 3000);
                    }, Math.random() * 2 * 1000)
                }
            })
        }, 1000);
    },
    _onImgComplete: function(img, callback) {
        if (!img) { return; }
        var timer = setInterval(function() {
            if (img.complete) {
                callback(img);
                clearInterval(timer);
            }
        }, 50)
    },
    _setBackgroundImg: function(img) {
        $('#win10').css('background-image', 'url(' + img + ')')
    },
    _settop: function(layero) {
        if (!isNaN(layero)) {
            layero = this.getLayeroByIndex(layero);
        }
        //置顶窗口
        var max_zindex = 0;
        $(".win10-open-iframe").each(function() {
            z = parseInt($(this).css('z-index'));
            $(this).css('z-index', z - 1);
            if (z > max_zindex) { max_zindex = z; }
        });
        layero.css('z-index', max_zindex + 1);
    },
    _checkTop: function() {
        var max_index = 0,
            max_z = 0,
            btn = null;
        $("#win10_btn_group_middle .btn.show").each(function() {
            var index = $(this).attr('index');
            var layero = Win10.getLayeroByIndex(index);
            var z = layero.css('z-index');
            if (z > max_z) {
                max_index = index;
                max_z = z;
                btn = $(this);
            }
        });
        this._settop(max_index);
        $("#win10_btn_group_middle .btn").removeClass('active');
        if (btn) {
            btn.addClass('active');
        }
    },

    _renderContextMenu: function(x, y, menu, trigger) {


        this._removeContextMenu();
        if (menu === true) { return; }
        var dom = $("<div class='win10-context-menu'><ul class='floorOne'></ul></div>");
        $('#win10').append(dom);
        var ul = dom.find('ul.floorOne');
        for (var i = 0; i < menu.length; i++) {
            var item = menu[i];
            if (item === '|') {
                ul.append($('<hr/>'));
                continue;
            }
            if (typeof(item) === 'string') {
                ul.append($('<li>' + item + '</li>'));
                continue;
            }
            if (typeof(item) === 'object' && item[2] == undefined) {
                var sub = $('<li>' + item[0] + '</li>');
                ul.append(sub);
                sub.click(trigger, item[1]);
                continue;
            }
            if (typeof(item) === 'object' && item[2] === 'folderMenu') {
                var fm = item[1]();
                console.log(fm)
                var sub = $('<li class="menuTwo">' + item[0] + '</li>');
                ul.append(sub);
                // sub.click(trigger, fm);
                sub.mouseenter(trigger, function() {
                    var domTwo = $("<div class='win10-context-menu-two'><ul class='floorTwo'></ul></div>");
                    $('.menuTwo').append(domTwo);
                    var ult = domTwo.find('ul.floorTwo');
                    fm.forEach(ele => {
                        var ssub = $('<li class="" onclick="addIntoFolder(\'' + ele[1] + '\')">' + ele[0] + '</li>');
                        console.log(ele[1])
                        ult.append(ssub);
                        // ssub.click(addIntoFolder);
                    });
                });
                sub.mouseleave(trigger, function() {
                    var d = sub.find('div.win10-context-menu-two');
                    d.remove();
                });
                continue;
            }
        }
        //修正坐标
        if (x + 150 > document.body.clientWidth) { x -= 150 }
        if (y + dom.height() > document.body.clientHeight) { y -= dom.height() }
        dom.css({
            top: y,
            left: x,
        });
    },
    _ajax: function(aurl, apar, aasync) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                type: "post",
                url: aurl,
                data: JSON.stringify(apar),
                contentType: "application/json",
                dataType: "json",
                success: function(d) {
                    if (d.code == '0') {
                        resolve(d.data);
                    } else {
                        reject(new Error(d.message));
                    }
                },
                error: function(e) {
                    Win10.toLogin(e)
                }
            });
        });
    },
    _removeContextMenu: function() {
        $('.win10-context-menu').remove();
    },
    _closeWin: function(index) {
        $("#win10_" + index).remove();
        layer.close(index);
        Win10._checkTop();
        Win10._countTask--; //回退countTask数
        Win10._renderBar();
    },
    _fixWindowsHeightAndWidth: function() {
        //此处代码修正全屏切换引起的子窗体尺寸超出屏幕
        var opens = $('.win10-open-iframe');
        var clientHeight = document.body.clientHeight;
        var clientWidth = document.body.clientWidth;
        opens.each(function() {
            var layero_opened = $(this);
            var height = layero_opened.css('height');
            height = parseInt(height.replace('px', ''));
            if (height + 40 >= clientHeight) {
                layero_opened.css('height', clientHeight - 120);
                layero_opened.find('.layui-layer-content').css('height', clientHeight - 93);
                layero_opened.find('.layui-layer-content iframe').css('height', clientHeight - 93);
            }
            //修复窗体增大时，窗体高度不增大的问题
            if (clientHeight - height > 50) {
                layero_opened.css('height', clientHeight - 120);
                layero_opened.find('.layui-layer-content').css('height', clientHeight - 93);
                layero_opened.find('.layui-layer-content iframe').css('height', clientHeight - 93);
            }
            //此处代码修复切换时宽度不随之变化的问题   数据是根据情况设置的,可修改
            var width = layero_opened.css('width');
            width = parseInt(width.replace('px', ''));
            if (width + 30 >= clientWidth) {
                layero_opened.css('width', clientWidth - 220);
                // layero_opened.find('.layui-layer-content').css('width', clientWidth - 49);
                // layero_opened.find('.layui-layer-content iframe').css('width', clientWidth - 49);
            }
            if (clientWidth - width > 50) {
                layero_opened.css('width', clientWidth - 220);
                // layero_opened.find('.layui-layer-content').css('width', clientWidth - 49);
                // layero_opened.find('.layui-layer-content iframe').css('width', clientWidth - 49);
            }
        })
    },

    /**
     * 原 win10_bind_open_windows 子窗口事件自动绑定插件
     * @author:vG
     * @修订:Yuri2
     * @version:2.0.1
     * 说明: 所有#win10下的元素加入类win10-open-window即可自动绑定openUrl函数，无须用onclick手动绑定
     */
    _bind_open_windows: function() {
        // 注册事件委派 打开url窗口
        $('#win10').on('click', '.win10-open-window', function() {

            //>> 获取当前点击的对象
            $this = $(this);
            //>> 判断url地址是否为空 如果为空 不予处理
            if ($this.data('url') !== "") {
                //>> 获取弹窗标题
                var title = $this.data('title') || '',
                    areaAndOffset;
                //>> 判断是否有标题图片
                var bg = $this.data('icon-bg') ? $this.data('icon-bg') : '';
                if ($this.data('icon-image')) {
                    //>> 加入到标题中
                    title = '<img class="icon ' + bg + '" src="' + $this.data('icon-image') + '"/>' + title;
                }
                if ($this.data('icon-font')) {
                    //>> 加入到标题中
                    title = '<i class="fa fa-fw fa-' + $this.data('icon-font') + ' icon ' + bg + '"></i>' + title;
                }
                if (!title && $this.children('.icon').length === 1 && $this.children('.title').length === 1) {
                    title = $this.children('.icon').prop("outerHTML") + $this.children('.title').html();
                }
                //>> 判断是否需要 设置 区域宽度高度
                if ($this.data('area-offset')) {
                    areaAndOffset = $this.data('area-offset');
                    //>> 判断是否有分隔符
                    if (areaAndOffset.indexOf(',') !== -1) {
                        areaAndOffset = eval(areaAndOffset);
                    }
                }
                //>> 调用win10打开url方法
                Win10.openUrl($this.data('url'), title, areaAndOffset);
            }

        })
    },
    _open_new_windows: function(data, isOpen) {
        if (data.url !== "") {
            //>> 获取弹窗标题
            var title = data.title || '',
                areaAndOffset;
            //>> 判断是否有标题图片
            var bg = data.iconBg ? data.iconBg : '';
            if (data.iconImage) {
                //>> 加入到标题中
                title = '<img class="icon ' + bg + '" src="' + data.iconImage + '"/>' + title;
            }
            if (data.iconFont) {
                //>> 加入到标题中
                title = '<i class="fa fa-fw fa-' + data.iconFont + ' icon ' + bg + '"></i>' + title;
            }
            if (!title && $this.children('.icon').length === 1 && $this.children('.title').length === 1) {
                title = $this.children('.icon').prop("outerHTML") + $this.children('.title').html();
            }
            //>> 判断是否需要 设置 区域宽度高度
            if (data.areaOffset) {
                areaAndOffset = data.areaOffset;
                //>> 判断是否有分隔符
                if (areaAndOffset.indexOf(',') !== -1) {
                    areaAndOffset = eval(areaAndOffset);
                }
            }
            //>> 调用win10打开url方法
            // console.log(title)

            if (title.split(">").length == 1) {
                title = "<img/>" + title
            }
            var t = title.split(">")

            localStorage.setItem("cp", JSON.stringify(t[1]));
            if (title.includes("作业"))
                title = t[1];
            if (isOpen == true) {
                Win10.openUrl(data.url, title, areaAndOffset);
            }
        }
    },
    _init: function() {

        //获取语言
        this._lang = (navigator.language || navigator.browserLanguage).toLowerCase();
        var d = new Date();
        this._previousTime = d.getTime();
        $("#win10_btn_win").click(function() {
            Win10.commandCenterClose();
            Win10.menuToggle();
        });
        // Win10.commandCenterOpen();
        $("#win10_btn_command").click(function() {
            Win10.menuClose();
            Win10.commandCenterToggle();
        });
        $("#win10 .desktop").click(function() {
            Win10.menuClose();
            Win10.commandCenterClose();
        });
        $('#win10').on('click', ".msg .btn_close_msg", function() {
            var msg = $(this).parent();
            $(msg).addClass('animated slideOutRight');
            setTimeout(function() {
                msg.remove()
            }, 500)
        });
        $('#win10_btn_command_center_clean_all').click(function() {
            var msgs = $('#win10_command_center .msg');
            msgs.addClass('animated slideOutRight');
            setTimeout(function() {
                msgs.remove()
            }, 1500);
            setTimeout(function() {
                Win10.commandCenterClose();
            }, 1000);
        });
        $("#win10_btn_show_desktop").click(function() {
            $("#win10 .desktop").click();
            Win10.hideWins();
        });
        $("#win10-menu-switcher").click(function() {
            if (Win10._switchMenuTooHurry) { return; }
            Win10._switchMenuTooHurry = true;
            var class_name = 'win10-menu-hidden';
            var list = $("#win10-menu>.list");
            var blocks = $("#win10-menu>.blocks");
            var toggleSlide = function(obj) {
                if (obj.hasClass(class_name)) {
                    obj.addClass('animated slideInLeft');
                    obj.removeClass('animated slideOutLeft');
                    obj.removeClass(class_name);
                } else {
                    setTimeout(function() {
                        obj.addClass(class_name);
                    }, 450);
                    obj.addClass('animated slideOutLeft');
                    obj.removeClass('animated slideInLeft');
                }
            };
            toggleSlide(list);
            toggleSlide(blocks);
            setTimeout(function() {
                Win10._switchMenuTooHurry = false;
            }, 520)
        });
        $("#win10_btn_group_middle").click(function() {
            $("#win10 .desktop").click();
        });
        $(document).on('click', '.win10-btn-refresh', function() {
            var index = $(this).attr('index');
            var iframe = Win10.getLayeroByIndex(index).find('iframe');
            iframe.attr('src', iframe.attr('src'));
        });
        $(document).on('mousedown', '.win10-open-iframe', function() {
            var layero = $(this);
            Win10._settop(layero);
            Win10._checkTop();
        });
        $('#win10_btn_group_middle').on('click', '.btn_close', function() {
            var index = $(this).parent().attr('index');
            Win10._closeWin(index);
        });
        $('#win10-menu .list').on('click', '.item', function() {
            var e = $(this);
            if (e.hasClass('has-sub-down')) {
                $('#win10-menu .list .item.has-sub-up').toggleClass('has-sub-down').toggleClass('has-sub-up');
                $("#win10-menu .list .sub-item").slideUp();
            }
            if (e.next().hasClass('sub-item')) {
                e.toggleClass('has-sub-down').toggleClass('has-sub-up');
            }
            while (e.next().hasClass('sub-item')) {
                e.next().slideToggle();
                e = e.next();
            }
        });
        // $("#win10-btn-browser").click(function() {
        //     // var area = ['100%', (document.body.clientHeight - 40) + 'px'];
        //     // var offset = ['0', '0'];
        //     layer.prompt({
        //         title: Win10.lang('访问网址', 'Visit URL'),
        //         formType: 2,
        //         value: '',
        //         skin: 'win10-layer-open-browser',
        //         area: ['300px', '150px'],
        //         zIndex: 99999999999
        //     }, function(value, i) {
        //         layer.close(i);
        //         layer.msg(Win10.lang('请稍候...', 'Hold on please...'), { time: 1500 }, function() {
        //             Win10.openUrl(value, value);
        //         });
        //     });
        // });
        setInterval(function() {
            var myDate = new Date();
            var year = myDate.getFullYear();
            var month = myDate.getMonth() + 1;
            var date = myDate.getDate();
            var hours = myDate.getHours();
            var mins = myDate.getMinutes();
            if (mins < 10) { mins = '0' + mins }
            $("#win10_btn_time").html(hours + ':' + mins + '<br/>' + year + '/' + month + '/' + date);
        }, 1000);
        Win10.buildList(); //预处理左侧菜单
        Win10._startAnimate(); //动画处理
        Win10.renderShortcuts(); //渲染图标
        $("#win10-shortcuts").removeClass('shortcuts-hidden'); //显示图标
        Win10._showShortcut(); //显示图标
        Win10.renderMenuBlocks(); //渲染磁贴
        //窗口改大小，重新渲染
        $(window).resize(function() {
            Win10.renderShortcuts();
            Win10._checkBgUrls();
            if (!Win10.isSmallScreen()) Win10._fixWindowsHeightAndWidth(); //2017年11月14日修改，加入了if条件
        });
        //细节
        $(document).on('focus', ".win10-layer-open-browser textarea", function() {
            $(this).attr('spellcheck', 'false');
        });
        $(document).on('keyup', ".win10-layer-open-browser textarea", function(e) {
            if (e.keyCode === 13) {
                $(this).parent().parent().find('.layui-layer-btn0').click();
            }
        });
        //点击清空右键菜单
        $(document).click(function(event) {
            if (!event.button)
                Win10._removeContextMenu();
        });
        //禁用右键的右键
        $(document).on('contextmenu', '.win10-context-menu', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        $(document).on('mousedown', '.win10-ui-ap', function() {
            $this = $(this);

            var currentpro = {
                title: $this.data('title'),
                iconBg: $this.data('icon-bg'),
                iconImage: $this.data('icon-image'),
                iconFont: $this.data('icon-font'),
                areaOffset: $this.data('area-offset'),
                url: $this.data('url'),
                projectPath: $this.data('path')
            }
            localStorage.setItem("currentpro", JSON.stringify(currentpro));
        });

        $(document).on('mousedown', '.win10-ui-drawer', function() {
            $this = $(this);
            var currentfolder = {
                id: $this.data('ids')
            }
            localStorage.setItem("currentfolder", JSON.stringify(currentfolder));
        });

        $(document).on('mousedown', '.win10-app-drawer', function() {
            $this = $(this);
            var currentfolder = {
                id: $this.data('ids')
            }
            console.log(currentfolder)
            localStorage.setItem("currentfolder", JSON.stringify(currentfolder));
        });

        $(document).on('dblclick', '.win10-ui-application', function(par) {
            var currentpro = JSON.parse(localStorage.getItem("currentpro"));
            Win10._open_new_windows(currentpro, true);
        });
        $(document).on('dblclick', '.win10-ui-url', function(par) {
            if (!checkBalance()) {
                layer.msg("账户余额不足。请充值！", {time: 3000});
                return;
            }
            var currentpro = JSON.parse(localStorage.getItem("currentpro"));
            url = currentpro.url.split(' ');
            if (url[1] == 'new_tab')
                window.open(url[0], "_blank");
            else
                Win10._open_new_windows(currentpro, true);
        });
        $(document).on('dblclick', '.win10-ui-project', function(par) {
            var d1 = new Date();
            var currentTime = d1.getTime();
            if (currentTime - this._previousTime < 500)
                return;
            var currentpro = JSON.parse(localStorage.getItem("currentpro"));
            currentpro.url = "./form.php?projectPath=" + currentpro.projectPath;
            Win10._open_new_windows(currentpro, true);

            // Win10.runProject();
            this._previousTime = currentTime;
        });
        //设置默认右键菜单
        // Win10.setContextMenu('#win10', true);

        Win10.setContextMenus(); //自定义右键


        //处理消息图标闪烁
        setInterval(function() {
            var btn = $("#win10-msg-nof.on-new-msg");
            if (btn.length > 0) {
                btn.toggleClass('fa-commenting-o');
            }
        }, 600);

        //绑定快捷键
        $("body").keyup(function(e) {
            if (e.ctrlKey) {
                switch (e.keyCode) {
                    case 37: //left
                        $("#win10_btn_win").click();
                        break;
                    case 38: //up
                        Win10.showWins();
                        break;
                    case 39: //right
                        $("#win10_btn_command").click();
                        break;
                    case 40: //down
                        Win10.hideWins();
                        break;
                }
            }
        });




        /**
         * WIN10-UI v1.1.2.2 桌面舞台支持补丁
         * WIN10-UI v1.1.2.2之后的版本不需要此补丁
         * @usage 直接引用即可（需要jquery）
         * @author Yuri2
         */
        if ($("#win10-desktop-scene").length < 1) {
            $("#win10-shortcuts").css({
                position: 'absolute',
                left: 0,
                top: 0,
                'z-index': 100,
            });
            $("#win10 .desktop").append("<div id='win10-desktop-scene' style='width: 100%;height: calc(100% - 40px);position: absolute;left: 0;top: 0; z-index: 0;background-color: transparent;'></div>")
        }

        //属性绑定
        // Win10._bind_open_windows();
    },
    setContextMenus: function(params) {
        Win10.setContextMenu('#win10', [
            ['<i class="fa fa-fw fa-folder-o"></i> ' + Win10.lang('新建分组', 'New Project Folder'), function(par) {
                Win10.newFolder("new", par)
            }]
        ]);

        Win10.setContextMenu('.shortcut.win10-ui-url', [
            ['<i class="fa fa-fw fa-play"></i> ' + Win10.lang('运行', 'Run'), function(par) {
                if (!checkBalance()) {
                    layer.msg("账户余额不足。请充值！", {time: 3000});
                    return;
                }
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                url = currentpro.url.split(' ');
                if (url[1] == 'new_tab')
                    window.open(url[0], "_blank");
                else
                    Win10._open_new_windows(currentpro, true);
            }]
        ]);

        Win10.setContextMenu('.shortcut.win10-ui-application', [
            ['<i class="fa fa-fw fa-folder-open"></i> ' + Win10.lang('打开', 'New Instance'), function(par) {
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                Win10._open_new_windows(currentpro, true);
            }],
            ['<i class="fa fa-fw fa-list-ul"></i> ' + Win10.lang('相关实例', 'Related Instances'), function() {
                localStorage.setItem("getProjectByApp", true);
                var currentpro = {
                    url: "./project.php"
                };
                Win10._open_new_windows(currentpro, true);
            }],
            ['<i class="fa fa-fw fa-folder-open"></i> ' + Win10.lang('数据文件', 'Related Files'), function(par) {
                base = $this.children('.title').html();
                projPath = base + '.yaml';
                Win10.openUrl('./folder.php?dir=' + encodeURI(projPath), '文件: ' + base,
                              [['80%','80%'], ['100px','100px']])
            }],
            ['<i class="fa fa-fw fa-list"></i> ' + Win10.lang('相关作业', 'Related Jobs'), function() {
                localStorage.setItem("getWorksOfProject", "app");
                base = $this.children('.title').html();
                localStorage.setItem("cp", '"' + base + '"');
                Win10.openUrl('./works.php', '作业: ' + base)
            }],
            ['<i class="fa fa-fw fa-times"></i>' + Win10.lang('杀掉相关作业','Kill Related Jobs'), function() {
                var currentpro = $this.children('.title').html();
                kill_jobs(currentpro, "app");
            }],
            ['<i class="fa fa-fw fa-trash"></i>' + Win10.lang('删除结束作业数据文件','Delete Ended Job Data File'), function() {
                var currentpro = $this.children('.title').html();
                delete_files(currentpro, "app");
            }]
        ]);
        Win10.setContextMenu('.shortcut.win10-ui-project', [
            ['<i class="fa fa-fw fa-wrench"></i> ' + Win10.lang('属性', 'Attribute'), function(par) {
                var currentpro = {}
                currentpro.url = "./form.php?projectPath=" + par.data.dataset.path;
                Win10._open_new_windows(currentpro, true);

            }],
            ['<i class="fa fa-fw fa-play"></i> ' + Win10.lang('运行', 'Run'), function() {
                Win10.runProject();
            }],
            ['<i class="fa fa-fw fa-folder-open"></i> ' + Win10.lang('数据文件', 'Related Files'), function(par) {
                var projPath = par.data.dataset.path;
                var base = projPath.substring(projPath.lastIndexOf('/') + 1);
                base = base.substring(0, base.lastIndexOf('.'));
                Win10.openUrl('./folder.php?dir=' + encodeURI(projPath), '文件: ' + base,
                              [['80%','80%'], ['100px','100px']])
            }],
            ['<i class="fa fa-fw fa-list"></i> ' + Win10.lang('相关作业', 'Related Jobs'), function() {
                localStorage.setItem("getWorksOfProject", "project");
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                Win10._open_new_windows(currentpro, false);
                var str = currentpro.projectPath;
                var base = str.substring(str.lastIndexOf('/') + 1);
                base = base.substring(0, base.lastIndexOf('.'));
                Win10.openUrl('./works.php', '作业: ' + base)
            }],
            ['<i class="fa fa-fw fa-times"></i>' + Win10.lang('杀掉相关作业','Kill Related Jobs'), function() {
                var currentpro = JSON.parse(localStorage.getItem("currentpro"));
                kill_jobs(currentpro.projectPath, "project");
            }],
            ['<i class="fa fa-fw fa-trash"></i> ' + Win10.lang('删除', 'Delete'), function() {
                Win10.deletePtoject();
            }], '|', ['<i class="fa fa-fw fa-sign-in"></i> ' + Win10.lang('移动到分组', 'Move to folder'), function() {
                var arr = [];
                var par = {};
                Win10._ajax("php/queryProjectCategory.php", par).then(res => {  // 查询实例分组类别接口
                    var d = res;
                    d.forEach(ele => {
                        let temp = [ele.name, ele.id];
                        arr.push(temp);
                    });
                }).catch(err => {
                    layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
                })

                return arr
            }, 'folderMenu']
        ]);
        Win10.setContextMenu('.win10-ui-drawer', [
            ['<i class="fa fa-fw fa-pencil-square-o"></i> ' + Win10.lang('重命名', 'Rename'), function(par) {
                Win10.newFolder("rename", par)
            }],
            ['<i class="fa fa-fw fa-trash"></i> ' + Win10.lang('删除分组', 'Delete folder'), function(par) {
                Win10.deleteFolder(par.data.dataset.ids);
            }]
        ]);
        Win10.setContextMenu('.win10-app-drawer', [
            ['<i class="fa fa-fw fa-folder-o"></i> ' + Win10.lang('应用类，不可改', 'Application Category, Unchangeable'), function(par) {
            }]
        ]);
    },
    runProject: function(params) {
        layer.msg(Win10.lang('作业启动中...', 'Job starting...'), { time: 1500 });
        var currentpro = JSON.parse(localStorage.getItem("currentpro"));
        Win10._open_new_windows(currentpro, false);
        var interval;
        var parin = {
            "projectPath": currentpro.projectPath
        };
        var vncid;
        var project = currentpro.projectPath.split(/\//).pop();
        project = project.split('.')[0];
        Win10._ajax("php/getProject.php", parin).then(res => {
            var d = res;
            runp(d.gui);
        }).catch(err => {
            layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
        })

        function fromVnc(url, data) {
            var par = { "jobId": vncid };
            Win10._ajax("php/aip.php?action=queryJobVncUrl", par).then(res => {
                var urlc = vncurlConvert(res);
                if (urlc == 'no vnc url' || urlc == 'url wait') {
                    if (urlc == 'no vnc url')
                        clearInterval(interval);
                } else if (urlc.indexOf("https") != -1) {
                    window.open(urlc)
                    clearInterval(interval)
                } else {
                    Win10.openUrl(urlc, project + ' ' + vncid, [
                        ['95%', '95%'],
                        ['10px', '10px']
                    ]);
                    clearInterval(interval)
                }
            }).catch(err => {
                layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
            })

        }

        function runp(gui) {
            var runPrjIn = {};
            runPrjIn.projectPath = parin.projectPath;
            runPrjIn.geometry = String(Math.floor(window.innerWidth * 19 / 20)) + "x" +
                String(Math.floor(window.innerHeight * 19 / 20) - 65);
            $.ajax({
                type: "post",
                url: "php/runProject.php",
                data: JSON.stringify(runPrjIn),
                dataType: "json",
                success: function(r) {
                    if (r.code == "0") {
                        vncid = r.data;
                        var href = {};
                        href.url = "./detail.php?id=" + r.data;
                        href.title = "作业 " + r.data;
                        Win10._open_new_windows(href, true);
                        if (gui) {
                            interval = setInterval(function() {
                                fromVnc();
                            }, 3000);
                            // fromVnc();
                        }
                    }

                    if (r.code == "2005") {
                        console.log(r)
                        var href = {};
                        href.url = "./detail.php?id=error&emsg=" +
                          encodeURIComponent(r.message);
                        href.title = "作业递交错误";
                        console.log(href)
                        Win10._open_new_windows(href, true);

                    }
                },
                error: function(err) {
                    Win10.toLogin(e)
                }
            });
        }
    },
    deletePtoject: function(p) {
        var currentpro = JSON.parse(localStorage.getItem("currentpro"));
        var parin;
        if (p) {
            parin = p;
        } else {
            parin = {
                "projectPath": currentpro.projectPath
            };
        }

        layer.confirm(Win10.lang('确认删除该实例吗?<p>实例相关作业将会被杀掉！<p>实例相关数据将会删除！',
        'Are you sure to delete this instance?<p>All instance related jobs will be killed!'), { icon: 3, title: Win10.lang('提示', 'Warning') }, function(index) {
            Win10._ajax("php/removeProject.php", parin).then(res => {  // 删除实例接口
                var d = res;
                layer.msg(Win10.lang('删除成功!', 'Project deleted!'), { time: 3000 });
                layer.close(index);
                Win10.getIndex(); //渲染图标  
                var pindex = localStorage.getItem("pindex");
                if (!pindex) {
                    pindex = index;
                    localStorage.setItem("pindex", pindex);
                }
                // refresh project list
                var iframe = Win10.getLayeroByIndex(pindex - 1).find('iframe');
                iframe.attr('src', iframe.attr('src'));
            }).catch(err => {
                layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
            })
        });
    },
    deleteFolder: function(params) {
        layer.confirm(Win10.lang('确认删除该分组吗?', 'Are you sure to delete this folder?'), { icon: 3, title: Win10.lang('提示', 'Warning') }, function(index) {
            var par = {
                ids: params
            };
            Win10._ajax("php/deleteProjectCategory.php", par).then(res => {  // 删除实例分组接口
                var d = res;
                layer.msg(Win10.lang('删除成功!', 'Folder deleted!'), { time: 3000 });
                layer.close(index);
                Win10.getIndex(); //渲染图标  
            }).catch(err => {
                layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
            })
        });
    },
    deleteFromFolder: function() {

        var currentpro = JSON.parse(localStorage.getItem("currentpro"));
        var currentfolder = JSON.parse(localStorage.getItem("currentfolder"));
        layer.confirm(Win10.lang('确认移出分组吗?', 'Are you sure to delete this project from folder?'), { icon: 3, title: Win10.lang('提示', 'Warning') }, function(index) {
            var par = {
                categoryId: currentfolder.id,
                projectPath: currentpro.projectPath
            };
            Win10._ajax("php/removeProjectFromProjectCategory.php", par).then(res => {  // 从实例分组中移除分组接口
                var d = res;
                layer.msg(Win10.lang('移出分组成功!', 'Deleted!'), { time: 3000 });
                layer.close(index);
                getoutFromFolder(index);
                Win10.getIndex(); //渲染图标    
            }).catch(err => {
                layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
            })
        });
    },
    newFolder: function(s, p) {
        var t = ""
        if (s == "new") {
            t = "新建分组"
        } else {
            t = "重命名分组"
        }
        var nc = layer.open({
            type: 1,
            closeBtn: 1, //不显示关闭按钮
            anim: 2,
            skin: 'layui-layer-molv',
            title: t,
            shadeClose: true, //开启遮罩关闭
            area: ['500px', '140px'], //宽高
            content: '<div style="padding: 10px;font-size: 12px">' +
                '<p><span class="myInfo1">分组名称: </span><input id="categoryName" placeholder="1-15位中文、英文、数字或下划线" maxlength="15" class="pwdinput"> *</p>' +
                // '<p><span class="myInfo1">描述: </span><input id="categoryDescription" class="pwdinput"> *</p>' +
                '<button class="confirm">确认</button>' +
                '<button class="toback">取消</button>' +
                '</div>'
        });
        $('.confirm').click(function(par) {
            var reg = /^[\u4E00-\u9FA5A-Za-z0-9_]{1,15}$/
                // if (!$('#categoryName').val() || $('#categoryName').val() == '' || !$('#categoryDescription').val() || $('#categoryDescription').val() == '') {
            if (!$('#categoryName').val() || $('#categoryName').val() == '') {
                layer.msg(Win10.lang('输入不能为空!', 'Input cannot be blank!'), { time: 1500 });
                return;
            }
            if (!reg.test($('#categoryName').val())) {
                layer.msg(Win10.lang('只能输入1-15位中文、英文、数字或下划线!', 'Only 1-15 digits of Chinese, English, numbers or underscores!'), { time: 1500 });
                return;
            }
            console.log(s)
            console.log(p)
            if (s == "new") {
                var pa = {
                    // description: $('#categoryDescription').val(),
                    name: $('#categoryName').val()
                };
                Win10._ajax("php/createProjectCategory.php", pa).then(res => {  // 新建分组接口
                    var d = res;
                    layer.msg(Win10.lang('新建分组成功!', 'Created successfully!'), { time: 3000 });
                    layer.close(nc);
                    Win10.getIndex();
                }).catch(err => {
                    layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
                })
            } else {
                var pa = {
                    id: p.data.dataset.ids,
                    name: $('#categoryName').val()
                };
                Win10._ajax("php/updateProjectCategory.php", pa).then(res => {  // 更新分组名称接口
                    var d = res;
                    layer.msg(Win10.lang('重命名成功!', 'Rename successful!'), { time: 3000 });
                    layer.close(nc);
                    Win10.getIndex();
                }).catch(err => {
                    layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
                })
            }
        });
        $('.toback').click(function(par) {
            layer.close(nc);
        })
    },
    setBgUrl: function(bgs) {
        this._bgs = bgs;
        this._checkBgUrls();
    },
    menuClose: function() {
        $("#win10-menu").removeClass('opened');
        $("#win10-menu").addClass('hidden');
        this._showShortcut();
        $(".win10-open-iframe").removeClass('hide');
    },
    menuOpen: function() {
        $("#win10-menu").addClass('opened');
        $("#win10-menu").removeClass('hidden');
        this._hideShortcut();
        // $(".win10-open-iframe").addClass('hide');
    },
    menuToggle: function() {
        if (!$("#win10-menu").hasClass('opened')) {
            this.menuOpen();
        } else {
            this.menuClose();
        }
    },
    commandCenterClose: function() {
        $("#win10_command_center").addClass('hidden_right');
        /* this._showShortcut();
        $(".win10-open-iframe").removeClass('hide'); */
    },
    commandCenterOpen: function() {
        $("#win10_command_center").removeClass('hidden_right');
        /* this._hideShortcut();
        $(".win10-open-iframe").addClass('hide'); */
        $("#win10-msg-nof").removeClass('on-new-msg fa-commenting-o');
    },
    renderShortcuts: function() {
        var h = parseInt($("#win10 #win10-shortcuts")[0].offsetHeight / 100);
        var x = 0,
            y = 0;
        $("#win10 #win10-shortcuts .shortcut").each(function() {
            $(this).css({
                left: x * 82 + 10,
                top: y * 100 + 10,
            });
            y++;
            if (y >= h) {
                y = 0;
                x++;
            }
        });

        Win10.setContextMenus(); //自定义右键
    },
    renderMenuBlocks: function() {
        var cell_width = 44;
        var groups = $("#win10-menu .menu_group");
        groups.each(function() {
            var group = $(this);
            var blocks = group.children('.block');
            var max_height = 0;
            blocks.each(function() {
                var that = $(this);
                var loc = that.attr('loc').split(',');
                var size = that.attr('size').split(',');
                var top = (loc[1] - 1) * cell_width + 40;
                var height = size[1] * cell_width;
                var full_height = top + height;
                if (full_height > max_height) { max_height = full_height }
                that.css({
                    top: top,
                    left: (loc[0] - 1) * cell_width,
                    width: size[0] * cell_width,
                    height: height,
                })

            });
            group.css('height', max_height);
        });
    },
    commandCenterToggle: function() {
        if ($("#win10_command_center").hasClass('hidden_right')) {
            this.commandCenterOpen();
        } else {
            this.commandCenterClose();
        }
    },
    newMsg: function(title, content, handle_click) {
        var e = $('<div class="msg">' +
            '<div class="title">' + title + '</div>' +
            '<div class="content">' + content + '</div>' +
            '<span class="btn_close_msg fa fa-close"></span>' +
            '</div>');
        $("#win10_command_center .msgs").prepend(e);
        e.find('.content:first,.title:first').click(function() {
            if (handle_click) {
                handle_click(e);
            }
        });
        layer.tips(Win10.lang('新消息:', 'New message:') + title, '#win10_btn_command', {
            tips: [1, '#3c6a4a'],
            time: 3000
        });
        if ($("#win10_command_center").hasClass('hidden_right')) {
            $("#win10-msg-nof").addClass('on-new-msg');
        }
    },
    getLayeroByIndex: function(index) {
        return $('#' + 'layui-layer' + index)
    },
    isSmallScreen: function(size) {
        if (!size) {
            size = 768
        }
        var width = document.body.clientWidth;
        return width < size;
    },
    enableFullScreen: function() {
        var docElm = document.documentElement;
        //W3C
        if (docElm.requestFullscreen) {
            docElm.requestFullscreen();
        }
        //FireFox
        else if (docElm.mozRequestFullScreen) {
            docElm.mozRequestFullScreen();
        }
        //Chrome等
        else if (docElm.webkitRequestFullScreen) {
            docElm.webkitRequestFullScreen();
        }
        //IE11
        else if (docElm.msRequestFullscreen) {
            document.body.msRequestFullscreen();
        }
    },
    disableFullScreen: function() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    },
    buildList: function() {
        $("#win10-menu .list .sub-item").slideUp();
        $("#win10-menu .list .item").each(function() {
            if ($(this).next().hasClass('sub-item')) {
                $(this).addClass('has-sub-down');
                $(this).removeClass('has-sub-up');
            }
        })
    },
    openUrl: function(url, title, areaAndOffset) {
        var dontopen = false;
        var index1 = 0;
        var name = title;
        if (title.includes('<')) {
            var t = title.split('>');
            name = t[1];
        }
        if (this._countTask > 0) {
            $("#win10_btn_group_middle .btn").each(function() {
                if ($(this).attr('name') == name) {
                    var index = $(this).attr('index');
                    var layero = Win10.getLayeroByIndex(index);
                    dontopen = true;
                    index1 = index;
                    $(this).addClass('show');
                    $('#win10_btn_group_middle .btn.active').removeClass('active');
                    $(this).addClass('active');
                    Win10._settop(layero);
                    layero.show();
                    Win10._renderBar();
                }
            });
        }
        if (dontopen) {
            this.menuClose();
            this.commandCenterClose();
            return index1;
        }
        if (this._countTask > 30) {
            layer.msg("您打开的弹窗太多了，请关闭一些");
            return false;
        } else {
            this._countTask++;
        }
        if (!url) { url = '404' }
        url = url.replace(/(^\s*)|(\s*$)/g, "");
        var preg = /^(https?:\/\/|\.\.?\/|\/\/?)/;
        if (!preg.test(url)) {
            url = 'http://' + url;
        }
        if (!url) {
            url = '//yuri2.cn';
        }
        if (!title) {
            title = url;
        }
        var area, offset;
        if (this.isSmallScreen() || areaAndOffset === 'max') {
            area = ['100%', (document.body.clientHeight - 40) + 'px'];
            offset = ['0', '0'];
        } else if (typeof areaAndOffset === 'object') {
            area = areaAndOffset[0];
            offset = areaAndOffset[1];
        } else {
            area = ['80%', '80%'];
            var topset, leftset;
            topset = parseInt($(window).height());
            topset = (topset - (topset * 0.8)) / 2 - 41;
            leftset = parseInt($(window).width());
            leftset = (leftset - (leftset * 0.8)) / 2 - 120;
            offset = [Math.round((this._countTask % 10 * 20) + topset) + 'px', Math.round((this._countTask % 10 * 20 + 100) + leftset) + 'px'];
        }
        var index = layer.open({
            type: 2,
            shadeClose: true,
            shade: false,
            maxmin: true, //开启最大化最小化按钮
            title: title,
            content: url,
            area: area,
            offset: offset,
            isOutAnim: false,
            skin: 'win10-open-iframe',
            cancel: function(index, layero) {
                $("#win10_" + index).remove();
                Win10._checkTop();
                Win10._countTask--; //回退countTask数
                Win10._renderBar();
            },
            min: function(layero) {
                layero.hide();
                $("#win10_" + index).removeClass('show');
                Win10._checkTop();
                return false;
            },
            full: function(layero) {
                layero.find('.layui-layer-min').css('display', 'inline-block');
            },
        });
        $('#win10_btn_group_middle .btn.active').removeClass('active');
        var btn = $('<div id="win10_' + index + '" index="' + index + '" class="btn show active" name="' + name + '"><div class="btn_title">' + title + '</div><div class="btn_close fa fa-close"></div></div>');
        var layero_opened = Win10.getLayeroByIndex(index);
        layero_opened.css('z-index', Win10._countTask + 813);
        Win10._settop(layero_opened);
        // layero_opened.find('.layui-layer-setwin').prepend('<a class="win10-btn-refresh" index="' + index + '" title="' + Win10.lang('刷新', 'Refresh') + '" href="javascript:void(0)"><span class="fa fa-refresh"></span></a>');
        layero_opened.find('.layui-layer-setwin .layui-layer-max').click(function() {
            setTimeout(function() {
                var height = layero_opened.css('height');
                height = parseInt(height.replace('px', ''));
                if (height >= document.body.clientHeight) {
                    layero_opened.css('height', height - 40);
                    layero_opened.find('.layui-layer-content').css('height', height - 83);
                    layero_opened.find('.layui-layer-content iframe').css('height', height - 83);
                }
            }, 300);

        });
        $("#win10_btn_group_middle").append(btn);
        Win10._renderBar();
        btn.click(function() {
            var index = $(this).attr('index');
            var layero = Win10.getLayeroByIndex(index);
            var settop = function() {
                //置顶窗口
                var max_zindex = 0;
                $(".win10-open-iframe").each(function() {
                    z = parseInt($(this).css('z-index'));
                    $(this).css('z-index', z - 1);
                    if (z > max_zindex) { max_zindex = z; }
                });
                layero.css('z-index', max_zindex + 1);
            };
            if ($(this).hasClass('show')) {
                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    $(this).removeClass('show');
                    Win10._checkTop();
                    layero.hide();
                } else {
                    $('#win10_btn_group_middle .btn.active').removeClass('active');
                    $(this).addClass('active');
                    Win10._settop(layero);
                }
            } else {
                $(this).addClass('show');
                $('#win10_btn_group_middle .btn.active').removeClass('active');
                $(this).addClass('active');
                Win10._settop(layero);
                layero.show();
            }
        });


        Win10._iframeOnClick.track(layero_opened.find('iframe:first')[0], function() {
            if (Object.getOwnPropertyNames(Win10._iframe_click_lock_children).length === 0) {
                Win10._settop(layero_opened);
                Win10._checkTop();
            } else {
                ;
            }
        });

        this.menuClose();
        this.commandCenterClose();
        return index;
    },
    closeAll: function() {
        $(".win10-open-iframe").remove();
        $("#win10_btn_group_middle").html("");
        Win10._countTask = 0;
        Win10._renderBar();
    },
    setAnimated: function(animated_classes, animated_liveness) {
        this._animated_classes = animated_classes;
        this._animated_liveness = animated_liveness;
    },
    exit: function() {
        layer.confirm(Win10.lang('确认退出吗?', 'Are you sure to log out?'), { icon: 3, title: Win10.lang('提示', 'Warning') }, function(index) {
            document.body.onbeforeunload = function() {
                $.ajax({
                    type: "post",
                    url: "php/logout.php",  // 登出接口
                    contentType: "application/json",
                    dataType: "html",
                    success: function(d) {
                        console.log(d);
                        if (d.code == 0) {
                            localStorage.removeItem("username");
                            localStorage.removeItem("userinfo");
                        } else {
                            layer.msg(Win10.lang(d.message, d.message), { time: 3000 })
                        }
                    },
                    error: function(msg) {
                        alert(msg);
                    }
                });
            };
            window.location.href = "./index.php";
            window.close();
            layer.close(index);
        });

    },
    lang: function(cn, en) {
        return this._lang === 'zh-cn' || this._lang === 'zh-tw' ? cn : en;
    },
    myAccount: function() {

        var info = JSON.parse(localStorage.getItem("userinfo"));

        console.log(info)
        var lx = layer.open({
            type: 1,
            closeBtn: 1, //不显示关闭按钮
            anim: 2,
            skin: 'layui-layer-molv',
            title: '我的帐户',
            shadeClose: true, //开启遮罩关闭
            area: ['500px', '400px'], //宽高
            content: '<div style="padding: 10px;font-size: 12px">' +
                '<p><span class="myInfo1">用户名: </span><span class="myInfo2">' + info.username + '</span></p>' +
                '<p><span class="myInfo1">昵称: </span><span class="myInfo2">' + info.nickname + '</span></p>' +
                '<p><span class="myInfo1">电话: </span><span class="myInfo2">' + info.phone + '</span></p>' +
                '<p><span class="myInfo1">企业/单位电子邮箱: </span><span class="myInfo2">' + info.email + '</span></p>' +
                // '<p><span class="myInfo1">管理员用户名: </span><span class="myInfo2">SkyForm</span></p>' +
                // '<p><span class="myInfo1">用户: </span><span class="myInfo2">SkyForm</span></p>' +
                '<p class="changepwd">更换密码</p>' +
                '<p><span class="myInfo1">旧密码: </span><input type="password" id="oldpwd" class="pwdinput" disabled> *</p>' +
                '<p><span class="myInfo1">新密码: </span><input type="password" id="newpwd" class="pwdinput" disabled> *</p>' +
                '<p><span class="myInfo1">重复新密码: </span><input type="password" id="newpwdre" class="pwdinput" disabled> *</p>' +
                '<button class="confirm" disabled>确认</button>' +
                '<button class="toback">取消</button>' +
                '</div>'
        });
        this.menuClose();
        this.commandCenterClose();
        $('.confirm').click(function(par) {
            if (!$('#oldpwd').val() || $('#oldpwd').val() == '' || !$('#newpwd').val() || $('#newpwd').val() == '' || !$('#newpwdre').val() || $('#newpwdre').val() == '') {
                layer.msg(Win10.lang('输入不能为空!', 'Input cannot be blank!'), { time: 3000 });
                return;
            }
            if ($('#newpwd').val() == $('#newpwdre').val()) {
                var info = JSON.parse(localStorage.getItem("userinfo"));
                var par = {
                    id: info.id,
                    oldPassword: $('#oldpwd').val(),
                    password: $('#newpwd').val()
                };
                Win10._ajax("#", par).then(res => {
                    var d = res;
                    layer.msg(Win10.lang('密码修改成功!', 'Password modified!'), { time: 3000 });
                    layer.close(lx);
                }).catch(err => {
                    layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
                })

            } else {
                layer.msg(Win10.lang('新密码输入不一致, 请重新输入!', 'The new passwords is inconsistent. Please enter again!'), { time: 3000 })
            }
        });

        $('.toback').click(function(par) {
            layer.close(lx);
        })
    },
    setContextMenu: function(jq_dom, menu) {
        if (typeof(jq_dom) === 'string') {
            jq_dom = $(jq_dom);
        }
        jq_dom.unbind('contextmenu');
        jq_dom.on('contextmenu', function(e) {
            if (menu) {
                Win10._renderContextMenu(e.clientX, e.clientY, menu, this);
                if (e.cancelable) {
                    // 判断默认行为是否已经被禁用
                    if (!e.defaultPrevented) {
                        e.preventDefault();
                    }
                }
                e.stopPropagation();
            }
        });
    },
    hideWins: function() {
        $('#win10_btn_group_middle>.btn.show').each(function() {
            var index = $(this).attr('index');
            var layero = Win10.getLayeroByIndex(index);
            $(this).removeClass('show');
            $(this).removeClass('active');
            layero.hide();
        })
    },
    showWins: function() {
        $('#win10_btn_group_middle>.btn').each(function() {
            var index = $(this).attr('index');
            var layero = Win10.getLayeroByIndex(index);
            $(this).addClass('show');
            layero.show();
        });
        Win10._checkTop();
    },
    getDesktopScene: function() {
        return $("#win10-desktop-scene");
    },
    onReady: function(handle) {
        Win10._handleReady.push(handle);
    },
    toLogin: function(e) {
        window.location.href = "./index.php";
    },
    toReload: function() {
        location.href = location.href
    },
    childLayer: function(zn, en) {
        layer.msg(Win10.lang(zn, en), { time: 3000 });
    },
    getIndex: function() {
        var n = JSON.parse(localStorage.getItem("username"));
        var l;
        if (!n) {
            window.location.href = "./index.php";
        }
        var l = layer.msg(Win10.lang('正在加载, 请稍候...', 'Loading, please wait...'), { time: 150000 });
        $.ajax({
            type: "post",
            url: "php/queryAppAndProject.php",  // 得到所有图标，并分为应用和实例，分别传值给win10-ui-app和win10-ui-project，方便后续shortcut-drawer.js进一步处理
            data: JSON.stringify({ name: n }),
            dataType: "json",
            success: function(r) {
                if (r.code == 0) {
                    layer.close(l);
                    var d = r.data;
                    if (d) {
                        var str = "";
                        for (var i = 0; i < d.length; i++) {
                            if (d[i].type == 'APP') {
                                str += '<div class="shortcut ' + (d[i].url? 'win10-ui-url':'win10-ui-application') +
                                    ' win10-ui-ap" data-url="' + (d[i].url?d[i].url:'./form.php') + '" draggable="false">' +
                                    '<img class="icon" src="data:' + d[i].icon + '"  draggable="false"  />' +
                                    '<div class="title">' + d[i].name + '</div></div>';
                            } else if (d[i].type == 'APPCAT') {
                                var imgs = ''
                                for (let z = 0; z < d[i].appIcons.length; z++) {
                                    if (z > 3) {
                                        return
                                    }
                                    let y = z + 1;
                                    imgs += '<img class="iconCate' + y + '" src="data:' + d[i].appIcons[z] + '" id="' + d[i].id + '" draggable="false"/>'
                                }
                                str += '<div class="shortcut win10-appcat win10-app-drawer" id="' + d[i].id + '" data-ids="' + d[i].id + '">' +
                                    '<div class="iconCateBg" id="' + d[i].id + '"></div>' + imgs +
                                    '<div class="title" id="' + d[i].id + '">' + d[i].name + '</div>' +
                                    '<div class="win10-drawer-box"></div></div>'

                            } else if (d[i].type == 'PROJECT') {
                                str += '<div class="shortcut win10-ui-project win10-ui-ap" data-path="' + d[i].yamlPath + '" draggable="true">' +
                                    '<img class="icon" id="' + d[i].name + '" src="data:' + d[i].icon + '" draggable="false" />' +
                                    '<img class="icon_quick" src="./img/skyform.ico"  draggable="false" />' +
                                    '<div class="title">' + d[i].name + '</div></div>';
                            } else {
                                var imgs = ''
                                for (let z = 0; z < d[i].subProjectIcons.length; z++) {
                                    if (z > 3) {
                                        return
                                    }
                                    let y = z + 1;
                                    imgs += '<img class="iconCate' + y + '" src="data:' + d[i].subProjectIcons[z] + '" id="' + d[i].id + '" draggable="false" ondragover="allowDrop(event)" />'
                                }
                                str += '<div class="shortcut win10-drawer win10-ui-drawer" id="' + d[i].id + '" data-ids="' + d[i].id + '" ondrop="drop(event)" ondragover="allowDrop(event)">' +
                                    '<div class="iconCateBg" id="' + d[i].id + '" ondragover="allowDrop(event)"></div>' + imgs +
                                    '<div class="title" id="' + d[i].id + '" ondragover="allowDrop(event)">' + d[i].name + '</div>' +
                                    '<div class="win10-drawer-box"></div></div>'

                            } 
                        }
                        $("#win10-shortcuts").html(str);
                        Win10.renderShortcuts();
                    }
                } else {
                    layer.close(l);
                    layer.msg(Win10.lang('加载失败!', '对不起, 加载失败!'), { time: 1500 });
                }
            },
            error: function(e) {
                layer.close(l);
                Win10.toLogin(e)
            }
        });

    }

};


$(function() {
    Win10._init();
    for (var i in Win10._handleReady) {
        var handle = Win10._handleReady[i];
        handle();
    }
});

function getName(str)
{
    var base;
    if ((n = str.lastIndexOf('/')) != -1) {
        base = str.substring(n + 1);
        return base.substring(0, base.lastIndexOf('.'));
    } else
        return str.replace(/\"/g, '');
}

function kill_jobs(str, type)
{
    var base = getName(str);
    var parin;
    if (type == "project")
        parin = {project: base};
    else
        parin = {app: base};
    
    $.ajax({
        type: "post",
        url: "php/aip.php?action=queryJob",
        data: JSON.stringify(parin),
        dataType: "json",
        success: function(r) {
            if (r.code == "0") {
                var jobs = [];
                if (r.data.length > 0) {
                    r.data.forEach(function(j) {
                        if (j.statusString != 'EXIT' && j.statusString != 'FINISH' &&
                            j.statusString != 'ZOMBIE' && j.statusString != 'UNKNOWN')
                            jobs.push(j.jobID.jobID);
                    });
                    if (jobs.length > 0)
                        layer.confirm(Win10.lang('确认杀掉作业:' + jobs.join(',') + '?',
                                 'Confirming killing jobs:' + jobs.join(',') + '?'),
                                 {icon: 3, title: '提示'}, function(index) {
                        $.ajax({
                            type: "post",
                            url: "php/aip.php?action=killJob",
                            data: JSON.stringify({jobId: jobs.join(' ')}),
                            dataType: "json",
                            success: function(r) {
                                layer.msg(Win10.lang('执行杀死相关作业', 'Executing kill jobs'),
                                     {time: 1500});
                            },
                            error: function(e) {
                                Win10.toLogin(e);
                            }
                        });
                    });
                    else
                        layer.msg(Win10.lang('没有相关作业', 'No related job found'),
                              {time: 2000});
                } else {
                    layer.msg(Win10.lang('没有相关作业', 'No related job found'),
                              {time: 2000});
                }
            } else {
                layer.msg(r.message, {time: 5000});
            }
        },
        error: function(e) {
            Win10.toLogin(e);
        }
    });
}

function delete_files(str, type)
{
    var base = getName(str);
    layer.confirm(Win10.lang('确认删除相关作业数据？',
        'Are you sure to delete all job data?'), { icon: 3, title: Win10.lang('提示', 'Warning')},
        function(index) {
            Win10._ajax("php/removeFinishedJobFiles.php", {app: base}).then(res => {  // 删除相关作业数据，已结束的作业
                var d = res;
                layer.msg(Win10.lang('删除成功!', 'Project deleted!'), { time: 3000 });
                layer.close(index);
            }).catch(err => {
                layer.msg(Win10.lang(err.message, err.message), { time: 3000 })
            })
      });
}
