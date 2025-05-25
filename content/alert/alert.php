<?php
function alertPlugin() {

    $plugin_path = ROOT_PATH . "content/alert/";
    $info = include_once "{$plugin_path}setting.php";

    echo <<<html

<!-- 弹窗 -->
<script>
    (function($) {
            $.fn.extend({
                leanModal: function(options) {
                    var defaults = {
                        top: 100,
                        overlay: 0.5,
                        closeButton: null
                    };
                    var overlay = $("<div id='lean_overlay'></div>");
                    $("body").append(overlay);
                    options = $.extend(defaults, options);
                    return this.each(function() {
                        var o = options;
                        $(this).click(function(e) {
                            var modal_id = $(this).attr("href");
                            $("#lean_overlay").click(function() {
                                close_modal(modal_id)
                            });
                            $(o.closeButton).click(function() {
                                close_modal(modal_id)
                            });
                            var modal_height = $(modal_id).outerHeight();
                            var modal_width = $(modal_id).outerWidth();
                            $("#lean_overlay").css({
                                "display": "block",
                                opacity: 0
                            });
                            $("#lean_overlay").fadeTo(200, o.overlay);
                            $(modal_id).css({
                                "display": "block",
                                "position": "fixed",
                                "opacity": 0,
                                "z-index": 11000,
                                "left": 50 + "%",
                                "margin-left": -(modal_width / 2) + "px",
                                "top": o.top + "px"
                            });
                            $(modal_id).fadeTo(200, 1);
                            e.preventDefault()
                        })
                    });
                    function close_modal(modal_id) {
                        $("#lean_overlay").fadeOut(200);
                        $(modal_id).css({
                            "display": "none"
                        })
                    }
                }
            })
        }
    )(jQuery);



</script>

<style>

    #lean_overlay {
        position: fixed;
        z-index: 10000;
        top: 0px;
        left: 0px;
        height:100%;
        width:100%;
        background: #000;
        display: none;
    }
    #alert-html{
        background: #fff;
        display: none;
        position: fixed;
        opacity: 1;
        z-index: 11000;
        left: 50%;
        width: 800px;
        top: 10%!important;
        height: auto;
        border-radius: 6px;
    }
    #alert-html .content img{
        max-width: 100%;
    }
    #alert-html .content{
        min-height: unset;
        word-break: break-all;
    }

    @media screen and (max-width: 700px) {

        /* 在这里写CSS样式，当屏幕宽度小于700px时生效 */
        #alert-html{
            background: #fff;
            display: none;
            position: fixed;
            opacity: 1;
            z-index: 11000;
            left: 50%;
            max-width: 96%;
            top: 10%!important;
            border-radius: 6px;
        }
    }


    .modal_close{
        text-align: center;
        background: #495057;
        color: #fff;
        padding: 14px;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        cursor: pointer;
    }


</style>
<div id="alert-html">
    <div class="content" style="padding: 15px 25px; overflow: auto;">{$info['content']}</div>

    <div class="modal_close" href="#">关闭</div>
</div>

<script>

    var bodyHeight = document.body.clientHeight;
    var alertHeight = bodyHeight - bodyHeight * 0.3;
    $('#alert-html > .content').css('max-height', alertHeight + 'px');
</script>

<a id="alert-btn" rel="alertModal" name="signup" href="#alert-html"></a>

<script>
    $('a[rel*=alertModal]').leanModal({ top : 200, closeButton: ".modal_close" });
    $('#alert-btn').click()
</script>

html;


}

addAction('indexFoot', 'alertPlugin');



?>