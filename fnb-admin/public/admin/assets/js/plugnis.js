function loadInitFileStyle() {
    (function ($) {
            var nextId = 0;
            var Filestyle = function (element, options) {
                this.options = options;
                this.$elementFilestyle = [];
                this.$element = $(element)
            };
            Filestyle.prototype = {
                clear: function () {
                    this.$element.val("");
                    this.$elementFilestyle.find(":text").val("");
                    this.$elementFilestyle.find(".badge").remove()
                },
                destroy: function () {
                    this.$element.removeAttr("style").removeData("filestyle");
                    this.$elementFilestyle.remove()
                },
                disabled: function (value) {
                    if (value === true) {
                        if (!this.options.disabled) {
                            this.$element.attr("disabled", "true");
                            this.$elementFilestyle.find("label").attr("disabled", "true");
                            this.options.disabled = true
                        }
                    } else {
                        if (value === false) {
                            if (this.options.disabled) {
                                this.$element.removeAttr("disabled");
                                this.$elementFilestyle.find("label").removeAttr("disabled");
                                this.options.disabled = false
                            }
                        } else {
                            return this.options.disabled
                        }
                    }
                },
                buttonBefore: function (value) {
                    if (value === true) {
                        if (!this.options.buttonBefore) {
                            this.options.buttonBefore = true;
                            if (this.options.input) {
                                this.$elementFilestyle.remove();
                                this.constructor();
                                this.pushNameFiles()
                            }
                        }
                    } else {
                        if (value === false) {
                            if (this.options.buttonBefore) {
                                this.options.buttonBefore = false;
                                if (this.options.input) {
                                    this.$elementFilestyle.remove();
                                    this.constructor();
                                    this.pushNameFiles()
                                }
                            }
                        } else {
                            return this.options.buttonBefore
                        }
                    }
                },
                icon: function (value) {
                    if (value === true) {
                        if (!this.options.icon) {
                            this.options.icon = true;
                            this.$elementFilestyle.find("label").prepend(this.htmlIcon())
                        }
                    } else {
                        if (value === false) {
                            if (this.options.icon) {
                                this.options.icon = false;
                                this.$elementFilestyle.find(".icon-span-filestyle").remove()
                            }
                        } else {
                            return this.options.icon
                        }
                    }
                },
                input: function (value) {
                    if (value === true) {
                        if (!this.options.input) {
                            this.options.input = true;
                            if (this.options.buttonBefore) {
                                this.$elementFilestyle.append(this.htmlInput())
                            } else {
                                this.$elementFilestyle.prepend(this.htmlInput())
                            }
                            this.$elementFilestyle.find(".badge").remove();
                            this.pushNameFiles();
                            this.$elementFilestyle.find(".group-span-filestyle").addClass("input-group-btn")
                        }
                    } else {
                        if (value === false) {
                            if (this.options.input) {
                                this.options.input = false;
                                this.$elementFilestyle.find(":text").remove();
                                var files = this.pushNameFiles();
                                if (files.length > 0 && this.options.badge) {
                                    this.$elementFilestyle.find("label").append(' <span class="badge">' + files.length + "</span>")
                                }
                                this.$elementFilestyle.find(".group-span-filestyle").removeClass("input-group-btn")
                            }
                        } else {
                            return this.options.input
                        }
                    }
                },
                size: function (value) {
                    if (value !== undefined) {
                        var btn = this.$elementFilestyle.find("label")
                            , input = this.$elementFilestyle.find("input");
                        btn.removeClass("btn-lg btn-sm");
                        input.removeClass("input-lg input-sm");
                        if (value != "nr") {
                            btn.addClass("btn-" + value);
                            input.addClass("input-" + value)
                        }
                    } else {
                        return this.options.size
                    }
                },
                placeholder: function (value) {
                    if (value !== undefined) {
                        this.options.placeholder = value;
                        this.$elementFilestyle.find("input").attr("placeholder", value)
                    } else {
                        return this.options.placeholder
                    }
                },
                buttonText: function (value) {
                    if (value !== undefined) {
                        this.options.buttonText = value;
                        this.$elementFilestyle.find("label .buttonText").html(this.options.buttonText)
                    } else {
                        return this.options.buttonText
                    }
                },
                buttonName: function (value) {
                    if (value !== undefined) {
                        this.options.buttonName = value;
                        this.$elementFilestyle.find("label").attr({
                            "class": "btn " + this.options.buttonName
                        })
                    } else {
                        return this.options.buttonName
                    }
                },
                iconName: function (value) {
                    if (value !== undefined) {
                        this.$elementFilestyle.find(".icon-span-filestyle").attr({
                            "class": "icon-span-filestyle " + this.options.iconName
                        })
                    } else {
                        return this.options.iconName
                    }
                },
                htmlIcon: function () {
                    if (this.options.icon) {
                        return '<span class="icon-span-filestyle ' + this.options.iconName + '"></span> '
                    } else {
                        return ""
                    }
                },
                htmlInput: function () {
                    if (this.options.input) {
                        return '<input type="text" class="form-control ' + (this.options.size == "nr" ? "" : "input-" + this.options.size) + '" placeholder="' + this.options.placeholder + '" disabled> '
                    } else {
                        return ""
                    }
                },
                pushNameFiles: function () {
                    var content = ""
                        , files = [];
                    if (this.$element[0].files === undefined) {
                        files[0] = {
                            name: this.$element[0] && this.$element[0].value
                        }
                    } else {
                        files = this.$element[0].files
                    }
                    for (var i = 0; i < files.length; i++) {
                        content += files[i].name.split("\\").pop() + ", "
                    }
                    if (content !== "") {
                        this.$elementFilestyle.find(":text").val(content.replace(/\, $/g, ""))
                    } else {
                        this.$elementFilestyle.find(":text").val("")
                    }
                    return files
                },
                constructor: function () {
                    var _self = this, html = "", id = _self.$element.attr("id"), files = [], btn = "", $label;
                    if (id === "" || !id) {
                        id = "filestyle-" + nextId;
                        _self.$element.attr({
                            id: id
                        });
                        nextId++
                    }
                    btn = '<span class="group-span-filestyle ' + (_self.options.input ? "input-group-btn" : "") + '"><label for="' + id + '" class="btn ' + _self.options.buttonName + " " + (_self.options.size == "nr" ? "" : "btn-" + _self.options.size) + '" ' + (_self.options.disabled ? 'disabled="true"' : "") + ">" + _self.htmlIcon() + '<span class="buttonText">' + _self.options.buttonText + "</span></label></span>";
                    html = _self.options.buttonBefore ? btn + _self.htmlInput() : _self.htmlInput() + btn;
                    _self.$elementFilestyle = $('<div class="bootstrap-filestyle input-group">' + html + "</div>");
                    _self.$elementFilestyle.find(".group-span-filestyle").attr("tabindex", "0").keypress(function (e) {
                        if (e.keyCode === 13 || e.charCode === 32) {
                            _self.$elementFilestyle.find("label").click();
                            return false
                        }
                    });
                    _self.$element.css({
                        position: "absolute",
                        clip: "rect(0px 0px 0px 0px)"
                    }).attr("tabindex", "-1").after(_self.$elementFilestyle);
                    if (_self.options.disabled) {
                        _self.$element.attr("disabled", "true")
                    }
                    _self.$element.change(function () {
                        var files = _self.pushNameFiles();
                        if (_self.options.input == false && _self.options.badge) {
                            if (_self.$elementFilestyle.find(".badge").length == 0) {
                                _self.$elementFilestyle.find("label").append(' <span class="badge">' + files.length + "</span>")
                            } else {
                                if (files.length == 0) {
                                    _self.$elementFilestyle.find(".badge").remove()
                                } else {
                                    _self.$elementFilestyle.find(".badge").html(files.length)
                                }
                            }
                        } else {
                            _self.$elementFilestyle.find(".badge").remove()
                        }
                    });
                    if (window.navigator.userAgent.search(/firefox/i) > -1) {
                        _self.$elementFilestyle.find("label").click(function () {
                            _self.$element.click();
                            return false
                        })
                    }
                }
            };
            var old = $.fn.filestyle;
            $.fn.filestyle = function (option, value) {
                var get = ""
                    , element = this.each(function () {
                    if ($(this).attr("type") === "file") {
                        var $this = $(this)
                            , data = $this.data("filestyle")
                            ,
                            options = $.extend({}, $.fn.filestyle.defaults, option, typeof option === "object" && option);
                        if (!data) {
                            $this.data("filestyle", (data = new Filestyle(this, options)));
                            data.constructor()
                        }
                        if (typeof option === "string") {
                            get = data[option](value)
                        }
                    }
                });
                if (typeof get !== undefined) {
                    return get
                } else {
                    return element
                }
            }
            ;
            $.fn.filestyle.defaults = {
                buttonText: "Choose file",
                iconName: "glyphicon glyphicon-folder-open",
                buttonName: "btn-default",
                size: "nr",
                input: true,
                badge: true,
                icon: true,
                buttonBefore: false,
                disabled: false,
                placeholder: ""
            };
            $.fn.filestyle.noConflict = function () {
                $.fn.filestyle = old;
                return this
            }
            ;
            $(function () {
                $(".filestyle").each(function () {
                    var $this = $(this)
                        , options = {
                        input: $this.attr("data-input") === "false" ? false : true,
                        icon: $this.attr("data-icon") === "false" ? false : true,
                        buttonBefore: $this.attr("data-buttonBefore") === "true" ? true : false,
                        disabled: $this.attr("data-disabled") === "true" ? true : false,
                        size: $this.attr("data-size"),
                        buttonText: $this.attr("data-buttonText"),
                        buttonName: $this.attr("data-buttonName"),
                        iconName: $this.attr("data-iconName"),
                        badge: $this.attr("data-badge") === "false" ? false : true,
                        placeholder: $this.attr("data-placeholder")
                    };
                    $this.filestyle(options)
                })
            })
        }
    )(window.jQuery);

    (function($) {
        "use strict";
        var Components = function() {};
        //initializing tooltip
        Components.prototype.initTooltipPlugin = function() {
            $.fn.tooltip && $('[data-toggle="tooltip"]').tooltip()
        },

            //initializing popover
            Components.prototype.initPopoverPlugin = function() {
                $.fn.popover && $('[data-toggle="popover"]').popover()
            },

            //initializing custom modal
            Components.prototype.initCustomModalPlugin = function() {
                $('[data-plugin="custommodal"]').on('click', function(e) {
                    Custombox.open({
                        target: $(this).attr("href"),
                        effect: $(this).attr("data-animation"),
                        overlaySpeed: $(this).attr("data-overlaySpeed"),
                        overlayColor: $(this).attr("data-overlayColor")
                    });
                    e.preventDefault();
                });
            },

            //initializing nicescroll
            Components.prototype.initNiceScrollPlugin = function() {
                //You can change the color of scroll bar here
                $.fn.niceScroll &&  $(".nicescroll").niceScroll({ cursorcolor: '#98a6ad',cursorwidth:'6px', cursorborderradius: '5px'});
            },

            //initializing Slimscroll
            Components.prototype.initSlimScrollPlugin = function() {
                //You can change the color of scroll bar here
                $.fn.niceScroll &&  $(".slimscroll-noti").slimScroll({ position: 'right',size: "5px", color: '#98a6ad',height: '350px',wheelStep: 10});
            },

            //range slider
            Components.prototype.initRangeSlider = function() {
                $.fn.slider && $('[data-plugin="range-slider"]').slider({});
            },

            /* -------------
             * Form related controls
             */
            //switch

            Components.prototype.initSwitchery = function() {
                $('[data-plugin="switchery"]').each(function (idx, obj) {
                    $(this).closest('div').find('.switchery').remove();
                    new Switchery($(this)[0], $(this).data());
                });
            },
            //multiselect
            Components.prototype.initMultiSelect = function() {
                if($('[data-plugin="multiselect"]').length > 0)
                    $('[data-plugin="multiselect"]').multiSelect($(this).data());
            },

            /* -------------
            * small charts related widgets
            */
            //peity charts
            Components.prototype.initPeityCharts = function() {
                $('[data-plugin="peity-pie"]').each(function(idx, obj) {
                    var colors = $(this).attr('data-colors')?$(this).attr('data-colors').split(","):[];
                    var width = $(this).attr('data-width')?$(this).attr('data-width'):20; //default is 20
                    var height = $(this).attr('data-height')?$(this).attr('data-height'):20; //default is 20
                    $(this).peity("pie", {
                        fill: colors,
                        width: width,
                        height: height
                    });
                });
                //donut
                $('[data-plugin="peity-donut"]').each(function(idx, obj) {
                    var colors = $(this).attr('data-colors')?$(this).attr('data-colors').split(","):[];
                    var width = $(this).attr('data-width')?$(this).attr('data-width'):20; //default is 20
                    var height = $(this).attr('data-height')?$(this).attr('data-height'):20; //default is 20
                    $(this).peity("donut", {
                        fill: colors,
                        width: width,
                        height: height
                    });
                });

                $('[data-plugin="peity-donut-alt"]').each(function(idx, obj) {
                    $(this).peity("donut");
                });

                // line
                $('[data-plugin="peity-line"]').each(function(idx, obj) {
                    $(this).peity("line", $(this).data());
                });

                // bar
                $('[data-plugin="peity-bar"]').each(function(idx, obj) {
                    var colors = $(this).attr('data-colors')?$(this).attr('data-colors').split(","):[];
                    var width = $(this).attr('data-width')?$(this).attr('data-width'):20; //default is 20
                    var height = $(this).attr('data-height')?$(this).attr('data-height'):20; //default is 20
                    $(this).peity("bar", {
                        fill: colors,
                        width: width,
                        height: height
                    });
                });
            },

            Components.prototype.initCounterUp = function() {
                var delay = $(this).attr('data-delay')?$(this).attr('data-delay'):100; //default is 100
                var time = $(this).attr('data-time')?$(this).attr('data-time'):1200; //default is 1200
                $('[data-plugin="counterup"]').each(function(idx, obj) {
                    $(this).counterUp({
                        delay: 100,
                        time: 1200
                    });
                });
            },



            //initilizing
            Components.prototype.init = function() {
                var $this = this;
                this.initTooltipPlugin(),
                    this.initPopoverPlugin(),
                    this.initNiceScrollPlugin(),
                    this.initSlimScrollPlugin(),
                    this.initCustomModalPlugin(),
                    this.initRangeSlider(),
                    this.initSwitchery(),
                    this.initMultiSelect(),
                    this.initPeityCharts(),
                    this.initCounterUp(),
                    //creating portles
                    $.Portlet.init();
            },

            $.Components = new Components, $.Components.Constructor = Components

    })(window.jQuery);

   (function($) {
        "use strict";
        $.Components.init();
    })(window.jQuery);
}


var editor_config = {
    path_absolute: "/",
    selector: 'textarea.editor',
    height: 300,
    relative_urls: false,
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table directionality",
        "emoticons template paste textpattern",
        "textcolor"
    ],
    style_formats: [
        {title: 'Headers', items: [
                {title: 'Header 1', format: 'h1'},
                {title: 'Header 2', format: 'h2'},
                {title: 'Header 3', format: 'h3'},
                {title: 'Header 4', format: 'h4'},
                {title: 'Header 5', format: 'h5'},
                {title: 'Header 6', format: 'h6'}
            ]},
        {title: 'Inline', items: [
                {title: 'Bold', icon: 'bold', format: 'bold'},
                {title: 'Italic', icon: 'italic', format: 'italic'},
                {title: 'Underline', icon: 'underline', format: 'underline'},
                {title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough'},
                {title: 'Superscript', icon: 'superscript', format: 'superscript'},
                {title: 'Subscript', icon: 'subscript', format: 'subscript'},
                {title: 'Code', icon: 'code', format: 'code'}
            ]},
        {title: 'Blocks', items: [
                {title: 'Paragraph', format: 'p'},
                {title: 'Blockquote', format: 'blockquote'},
                {title: 'Div', format: 'div'},
                {title: 'Pre', format: 'pre'},
            ]},
        {title: 'Alignment', items: [
                {title: 'Left',  format: 'alignleft'},
                {title: 'Center',format: 'aligncenter'},
                {title: 'Right', format: 'alignright'},
                {title: 'Justify', format: 'alignjustify'}
            ]}
    ],
    toolbar: "insertfile undo redo | styleselect fontsizeselect fontselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media forecolor backcolor",
    file_browser_callback : function(field_name, url, type, win) {
        var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
        var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

        var cmsURL = editor_config.path_absolute + 'laravel-filemanager?field_name=' + field_name;
        if (type == 'image') {
            cmsURL = cmsURL + "&type=Images";
        } else {
            cmsURL = cmsURL + "&type=Files";
        }

        tinyMCE.activeEditor.windowManager.open({
            file : cmsURL,
            title : 'Filemanager',
            width : x * 0.8,
            height : y * 0.8,
            resizable : "yes",
            close_previous : "no"
        });
    },
    setup: function(ed) {
        ed.on('change', function(e) {
            tinyMCE.triggerSave();
        });
    }
};

tinymce.init(editor_config);
//slick
$(document).ready(function() {
    $('.slick-responsive').slick({
        dots: false,
        slidesToShow: 7,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 1400,
                settings: {
                    arrows: true,
                    centerPadding: '40px',
                    slidesToShow: 6,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 1200,
                settings: {
                    arrows: true,
                    centerPadding: '40px',
                    slidesToShow: 4,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 768,
                settings: {
                    arrows: true,
                    centerPadding: '40px',
                    slidesToShow: 3,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: true,
                    centerPadding: '40px',
                    slidesToShow: 1,
                    slidesToScroll: 1,
                }
            }
        ]
    });

})


function initDropzone(element,form,option){
    var dropzone;
    if ($(`${element}`).length) {
        dropzone = new Dropzone(`${form}`, option);
    }
    return dropzone;
}

