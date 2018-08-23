<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Sdk_LibraryInit extends Bitbull_Tooso_Block_Sdk
{
    const BLOCK_ID = 'tooso_sdk_init';
    const SCRIPT_ID = 'tooso-sdk-init';

    protected function _toHtml()
    {
        $this->_logger->debug('initializing sdk library');
        $initParams = $this->_helper->getInitParams();

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>'>
            window.toosoAsyncInit = function () {
                Tooso.init(<?=json_encode($initParams)?>);
            };
        </script>
        <?php

        $exampleInit = "";
        if ($this->_helperSpeechToText->includeExampleTemplate()) {
            $this->_logger->debug('Speech to Text: including speech to text template');
            $exampleInit .= $this->exampleTemplateSpeechToText($initParams);
        }

        return ob_get_clean().$exampleInit;
    }

    /**
     * Example template for speech to text
     *
     * @return string
     */
    protected function exampleTemplateSpeechToText($initParams){
        $inputSelector = '';
        if (isset($initParams->speech->input)) {
            $inputSelector = $initParams->speech->input;
        }else{
            if (isset($initParams->input)) {
                $inputSelector = $initParams->input;
            }else {
                return "<script>console.error('Cannot setup example template, search input not set');</script>";
            }
        }

        ob_start();
        ?>
        <style>
            #search{
                padding-right: 65px !important;
            }
            #search_mini_form .search-button-voice{
                right: 30px;
            }
            #search_mini_form .search-button-voice::before{
                background-position: center;
                background-size: 24px;
            }
            #search_mini_form .search-button-voice::before{
                background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjI0cHgiIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDQ3NS4wODUgNDc1LjA4NSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDc1LjA4NSA0NzUuMDg1OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPGc+CgkJPHBhdGggZD0iTTIzNy41NDEsMzI4Ljg5N2MyNS4xMjgsMCw0Ni42MzItOC45NDYsNjQuNTIzLTI2LjgzYzE3Ljg4OC0xNy44ODQsMjYuODMzLTM5LjM5OSwyNi44MzMtNjQuNTI1VjkxLjM2NSAgICBjMC0yNS4xMjYtOC45MzgtNDYuNjMyLTI2LjgzMy02NC41MjVDMjg0LjE3Myw4Ljk1MSwyNjIuNjY5LDAsMjM3LjU0MSwwYy0yNS4xMjUsMC00Ni42MzIsOC45NTEtNjQuNTI0LDI2Ljg0ICAgIGMtMTcuODkzLDE3Ljg5LTI2LjgzOCwzOS4zOTktMjYuODM4LDY0LjUyNXYxNDYuMTc3YzAsMjUuMTI1LDguOTQ5LDQ2LjY0MSwyNi44MzgsNjQuNTI1ICAgIEMxOTAuOTA2LDMxOS45NTEsMjEyLjQxNiwzMjguODk3LDIzNy41NDEsMzI4Ljg5N3oiIGZpbGw9IiM5OTk5OTkiLz4KCQk8cGF0aCBkPSJNMzk2LjU2MywxODguMTVjLTMuNjA2LTMuNjE3LTcuODk4LTUuNDI2LTEyLjg0Ny01LjQyNmMtNC45NDQsMC05LjIyNiwxLjgwOS0xMi44NDcsNS40MjYgICAgYy0zLjYxMywzLjYxNi01LjQyMSw3Ljg5OC01LjQyMSwxMi44NDV2MzYuNTQ3YzAsMzUuMjE0LTEyLjUxOCw2NS4zMzMtMzcuNTQ4LDkwLjM2MmMtMjUuMDIyLDI1LjAzLTU1LjE0NSwzNy41NDUtOTAuMzYsMzcuNTQ1ICAgIGMtMzUuMjE0LDAtNjUuMzM0LTEyLjUxNS05MC4zNjUtMzcuNTQ1Yy0yNS4wMjgtMjUuMDIyLTM3LjU0MS01NS4xNDctMzcuNTQxLTkwLjM2MnYtMzYuNTQ3YzAtNC45NDctMS44MDktOS4yMjktNS40MjQtMTIuODQ1ICAgIGMtMy42MTctMy42MTctNy44OTUtNS40MjYtMTIuODQ3LTUuNDI2Yy00Ljk1MiwwLTkuMjM1LDEuODA5LTEyLjg1LDUuNDI2Yy0zLjYxOCwzLjYxNi01LjQyNiw3Ljg5OC01LjQyNiwxMi44NDV2MzYuNTQ3ICAgIGMwLDQyLjA2NSwxNC4wNCw3OC42NTksNDIuMTEyLDEwOS43NzZjMjguMDczLDMxLjExOCw2Mi43NjIsNDguOTYxLDEwNC4wNjgsNTMuNTI2djM3LjY5MWgtNzMuMDg5ICAgIGMtNC45NDksMC05LjIzMSwxLjgxMS0xMi44NDcsNS40MjhjLTMuNjE3LDMuNjE0LTUuNDI2LDcuODk4LTUuNDI2LDEyLjg0N2MwLDQuOTQxLDEuODA5LDkuMjMzLDUuNDI2LDEyLjg0NyAgICBjMy42MTYsMy42MTQsNy44OTgsNS40MjgsMTIuODQ3LDUuNDI4aDE4Mi43MTljNC45NDgsMCw5LjIzNi0xLjgxMywxMi44NDctNS40MjhjMy42MjEtMy42MTMsNS40MzEtNy45MDUsNS40MzEtMTIuODQ3ICAgIGMwLTQuOTQ4LTEuODEtOS4yMzItNS40MzEtMTIuODQ3Yy0zLjYxLTMuNjE3LTcuODk4LTUuNDI4LTEyLjg0Ny01LjQyOGgtNzMuMDh2LTM3LjY5MSAgICBjNDEuMjk5LTQuNTY1LDc1Ljk4NS0yMi40MDgsMTA0LjA2MS01My41MjZjMjguMDc2LTMxLjExNyw0Mi4xMi02Ny43MTEsNDIuMTItMTA5Ljc3NnYtMzYuNTQ3ICAgIEM0MDEuOTk4LDE5Ni4wNDksNDAwLjE4NSwxOTEuNzcsMzk2LjU2MywxODguMTV6IiBmaWxsPSIjOTk5OTk5Ii8+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==);
            }
            @keyframes pulseAnimation {
                0%   { opacity:1; }
                50%  { opacity:0.3; }
                100% { opacity:1; }
            }
            @-o-keyframes pulseAnimation{
                0%   { opacity:1; }
                50%  { opacity:0.3; }
                100% { opacity:1; }
            }
            @-moz-keyframes pulseAnimation{
                0%   { opacity:1; }
                50%  { opacity:0.3; }
                100% { opacity:1; }
            }
            @-webkit-keyframes pulseAnimation{
                0%   { opacity:1; }
                50%  { opacity:0.3; }
                100% { opacity:1; }
            }
            #search_mini_form .search-button-voice-stop::before{
                background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjUxMnB4IiBoZWlnaHQ9IjUxMnB4IiB2aWV3Qm94PSIwIDAgNDc1LjA4NSA0NzUuMDg1IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0NzUuMDg1IDQ3NS4wODU7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPGc+Cgk8Zz4KCQk8cGF0aCBkPSJNMjM3LjU0MSwzMjguODk3YzI1LjEyOCwwLDQ2LjYzMi04Ljk0Niw2NC41MjMtMjYuODNjMTcuODg4LTE3Ljg4NCwyNi44MzMtMzkuMzk5LDI2LjgzMy02NC41MjVWOTEuMzY1ICAgIGMwLTI1LjEyNi04LjkzOC00Ni42MzItMjYuODMzLTY0LjUyNUMyODQuMTczLDguOTUxLDI2Mi42NjksMCwyMzcuNTQxLDBjLTI1LjEyNSwwLTQ2LjYzMiw4Ljk1MS02NC41MjQsMjYuODQgICAgYy0xNy44OTMsMTcuODktMjYuODM4LDM5LjM5OS0yNi44MzgsNjQuNTI1djE0Ni4xNzdjMCwyNS4xMjUsOC45NDksNDYuNjQxLDI2LjgzOCw2NC41MjUgICAgQzE5MC45MDYsMzE5Ljk1MSwyMTIuNDE2LDMyOC44OTcsMjM3LjU0MSwzMjguODk3eiIgZmlsbD0iI2Y0NmYyNSIvPgoJCTxwYXRoIGQ9Ik0zOTYuNTYzLDE4OC4xNWMtMy42MDYtMy42MTctNy44OTgtNS40MjYtMTIuODQ3LTUuNDI2Yy00Ljk0NCwwLTkuMjI2LDEuODA5LTEyLjg0Nyw1LjQyNiAgICBjLTMuNjEzLDMuNjE2LTUuNDIxLDcuODk4LTUuNDIxLDEyLjg0NXYzNi41NDdjMCwzNS4yMTQtMTIuNTE4LDY1LjMzMy0zNy41NDgsOTAuMzYyYy0yNS4wMjIsMjUuMDMtNTUuMTQ1LDM3LjU0NS05MC4zNiwzNy41NDUgICAgYy0zNS4yMTQsMC02NS4zMzQtMTIuNTE1LTkwLjM2NS0zNy41NDVjLTI1LjAyOC0yNS4wMjItMzcuNTQxLTU1LjE0Ny0zNy41NDEtOTAuMzYydi0zNi41NDdjMC00Ljk0Ny0xLjgwOS05LjIyOS01LjQyNC0xMi44NDUgICAgYy0zLjYxNy0zLjYxNy03Ljg5NS01LjQyNi0xMi44NDctNS40MjZjLTQuOTUyLDAtOS4yMzUsMS44MDktMTIuODUsNS40MjZjLTMuNjE4LDMuNjE2LTUuNDI2LDcuODk4LTUuNDI2LDEyLjg0NXYzNi41NDcgICAgYzAsNDIuMDY1LDE0LjA0LDc4LjY1OSw0Mi4xMTIsMTA5Ljc3NmMyOC4wNzMsMzEuMTE4LDYyLjc2Miw0OC45NjEsMTA0LjA2OCw1My41MjZ2MzcuNjkxaC03My4wODkgICAgYy00Ljk0OSwwLTkuMjMxLDEuODExLTEyLjg0Nyw1LjQyOGMtMy42MTcsMy42MTQtNS40MjYsNy44OTgtNS40MjYsMTIuODQ3YzAsNC45NDEsMS44MDksOS4yMzMsNS40MjYsMTIuODQ3ICAgIGMzLjYxNiwzLjYxNCw3Ljg5OCw1LjQyOCwxMi44NDcsNS40MjhoMTgyLjcxOWM0Ljk0OCwwLDkuMjM2LTEuODEzLDEyLjg0Ny01LjQyOGMzLjYyMS0zLjYxMyw1LjQzMS03LjkwNSw1LjQzMS0xMi44NDcgICAgYzAtNC45NDgtMS44MS05LjIzMi01LjQzMS0xMi44NDdjLTMuNjEtMy42MTctNy44OTgtNS40MjgtMTIuODQ3LTUuNDI4aC03My4wOHYtMzcuNjkxICAgIGM0MS4yOTktNC41NjUsNzUuOTg1LTIyLjQwOCwxMDQuMDYxLTUzLjUyNmMyOC4wNzYtMzEuMTE3LDQyLjEyLTY3LjcxMSw0Mi4xMi0xMDkuNzc2di0zNi41NDcgICAgQzQwMS45OTgsMTk2LjA0OSw0MDAuMTg1LDE5MS43NywzOTYuNTYzLDE4OC4xNXoiIGZpbGw9IiNmNDZmMjUiLz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K);
                -webkit-animation: pulseAnimation 1s infinite;
                -moz-animation: pulseAnimation 1s infinite;
                -o-animation: pulseAnimation 1s infinite;
                animation: pulseAnimation 1s infinite;
            }
        </style>
        <script id='<?=self::SCRIPT_ID?>-example'>
            if(window.jQuery){
                jQuery(document).ready(function ($) {
                    var searchInput = $('<?=$inputSelector?>');
                    if(searchInput){
                        var buttonStart = $("<button class='button search-button search-button-voice' type='button'>Speech</button>");
                        searchInput.parent().append(buttonStart);

                        var buttonStop = $("<button class='button search-button search-button-voice search-button-voice-stop' type='button'>Stop</button>");
                        buttonStop.hide();
                        searchInput.parent().append(buttonStop);

                        buttonStart.click(function () {
                            Tooso.speech.start({
                                onStart: function () {
                                    console.log('>>> onStart');
                                    buttonStart.hide()
                                    buttonStop.show()
                                    searchInput.val("")
                                },
                                onText: function (e) {
                                    console.log('>>> onText', e.text);
                                    searchInput.val(searchInput.val()+e.text)
                                },
                                onError: function (error) {
                                    console.log('>>> onError', error);
                                    buttonStop.hide()
                                    buttonStart.show()
                                },
                                onEnd: function () {
                                    console.log('>>> onEnd');
                                    buttonStop.hide()
                                    buttonStart.show()
                                    if (searchInput.val().trim() != ""){
                                        searchInput.closest('form').submit();
                                    }
                                },
                            })
                        })

                        buttonStop.click(function () {
                            Tooso.speech.stop()
                        })
                    }else{
                        console.error("Tooso: SpeechToText search input not found");
                    }
                });
            }else{
                console.error("Tooso: SpeechToText script require jQuery");
            }


        </script>
        <?php
        return ob_get_clean();
    }
}
