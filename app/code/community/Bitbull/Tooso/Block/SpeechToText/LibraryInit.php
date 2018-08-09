<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_SpeechToText_LibraryInit extends Bitbull_Tooso_Block_SpeechToText
{
    const BLOCK_ID = 'tooso_tooso_speech_to_text_init';
    const SCRIPT_ID = 'tooso-tooso-speech-to-text-init';

    protected function _toHtml()
    {
        $this->_logger->debug('initializing speech to text library');
        $initParams = $this->_helper->getInitParams();

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>'>
            window.toosoAsyncInit = function () {
                Tooso.init(<?=json_encode($initParams)?>);
            };
        </script>
        <?php
        $example = "";
        if ($this->_helper->includeExampleTemplate()) {
            $example = $this->exampleTemplate();
        }
        return ob_get_clean().$example;
    }

    protected function exampleTemplate(){
        $initParams = $this->_helper->getInitParams();
        $inputSelector = $initParams['speech']['input'];

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
            }
            #search_mini_form .search-button-voice::before{
                background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjI0cHgiIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDQ3NS4wODUgNDc1LjA4NSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDc1LjA4NSA0NzUuMDg1OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPGc+CgkJPHBhdGggZD0iTTIzNy41NDEsMzI4Ljg5N2MyNS4xMjgsMCw0Ni42MzItOC45NDYsNjQuNTIzLTI2LjgzYzE3Ljg4OC0xNy44ODQsMjYuODMzLTM5LjM5OSwyNi44MzMtNjQuNTI1VjkxLjM2NSAgICBjMC0yNS4xMjYtOC45MzgtNDYuNjMyLTI2LjgzMy02NC41MjVDMjg0LjE3Myw4Ljk1MSwyNjIuNjY5LDAsMjM3LjU0MSwwYy0yNS4xMjUsMC00Ni42MzIsOC45NTEtNjQuNTI0LDI2Ljg0ICAgIGMtMTcuODkzLDE3Ljg5LTI2LjgzOCwzOS4zOTktMjYuODM4LDY0LjUyNXYxNDYuMTc3YzAsMjUuMTI1LDguOTQ5LDQ2LjY0MSwyNi44MzgsNjQuNTI1ICAgIEMxOTAuOTA2LDMxOS45NTEsMjEyLjQxNiwzMjguODk3LDIzNy41NDEsMzI4Ljg5N3oiIGZpbGw9IiM5OTk5OTkiLz4KCQk8cGF0aCBkPSJNMzk2LjU2MywxODguMTVjLTMuNjA2LTMuNjE3LTcuODk4LTUuNDI2LTEyLjg0Ny01LjQyNmMtNC45NDQsMC05LjIyNiwxLjgwOS0xMi44NDcsNS40MjYgICAgYy0zLjYxMywzLjYxNi01LjQyMSw3Ljg5OC01LjQyMSwxMi44NDV2MzYuNTQ3YzAsMzUuMjE0LTEyLjUxOCw2NS4zMzMtMzcuNTQ4LDkwLjM2MmMtMjUuMDIyLDI1LjAzLTU1LjE0NSwzNy41NDUtOTAuMzYsMzcuNTQ1ICAgIGMtMzUuMjE0LDAtNjUuMzM0LTEyLjUxNS05MC4zNjUtMzcuNTQ1Yy0yNS4wMjgtMjUuMDIyLTM3LjU0MS01NS4xNDctMzcuNTQxLTkwLjM2MnYtMzYuNTQ3YzAtNC45NDctMS44MDktOS4yMjktNS40MjQtMTIuODQ1ICAgIGMtMy42MTctMy42MTctNy44OTUtNS40MjYtMTIuODQ3LTUuNDI2Yy00Ljk1MiwwLTkuMjM1LDEuODA5LTEyLjg1LDUuNDI2Yy0zLjYxOCwzLjYxNi01LjQyNiw3Ljg5OC01LjQyNiwxMi44NDV2MzYuNTQ3ICAgIGMwLDQyLjA2NSwxNC4wNCw3OC42NTksNDIuMTEyLDEwOS43NzZjMjguMDczLDMxLjExOCw2Mi43NjIsNDguOTYxLDEwNC4wNjgsNTMuNTI2djM3LjY5MWgtNzMuMDg5ICAgIGMtNC45NDksMC05LjIzMSwxLjgxMS0xMi44NDcsNS40MjhjLTMuNjE3LDMuNjE0LTUuNDI2LDcuODk4LTUuNDI2LDEyLjg0N2MwLDQuOTQxLDEuODA5LDkuMjMzLDUuNDI2LDEyLjg0NyAgICBjMy42MTYsMy42MTQsNy44OTgsNS40MjgsMTIuODQ3LDUuNDI4aDE4Mi43MTljNC45NDgsMCw5LjIzNi0xLjgxMywxMi44NDctNS40MjhjMy42MjEtMy42MTMsNS40MzEtNy45MDUsNS40MzEtMTIuODQ3ICAgIGMwLTQuOTQ4LTEuODEtOS4yMzItNS40MzEtMTIuODQ3Yy0zLjYxLTMuNjE3LTcuODk4LTUuNDI4LTEyLjg0Ny01LjQyOGgtNzMuMDh2LTM3LjY5MSAgICBjNDEuMjk5LTQuNTY1LDc1Ljk4NS0yMi40MDgsMTA0LjA2MS01My41MjZjMjguMDc2LTMxLjExNyw0Mi4xMi02Ny43MTEsNDIuMTItMTA5Ljc3NnYtMzYuNTQ3ICAgIEM0MDEuOTk4LDE5Ni4wNDksNDAwLjE4NSwxOTEuNzcsMzk2LjU2MywxODguMTV6IiBmaWxsPSIjOTk5OTk5Ii8+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==);
            }
            #search_mini_form .search-button-voice-stop::before{
                background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjI0cHgiIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDQ3NS4wOTIgNDc1LjA5MiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDc1LjA5MiA0NzUuMDkyOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPGc+CgkJPHBhdGggZD0iTTExMy45MjIsMjY5LjgwM2MtMi44NTYtMTEuNDE5LTQuMjgzLTIyLjE3Mi00LjI4My0zMi4yNnYtMzYuNTVjMC00Ljk0Ny0xLjgwOS05LjIyOS01LjQyNC0xMi44NDcgICAgYy0zLjYxNy0zLjYxNi03Ljg5OC01LjQyNC0xMi44NDctNS40MjRjLTQuOTUyLDAtOS4yMzUsMS44MDktMTIuODUxLDUuNDI0Yy0zLjYxNywzLjYxNy01LjQyNiw3LjktNS40MjYsMTIuODQ3djM2LjU0NyAgICBjMCwyMS4xMjksMy45OTksNDEuNDk0LDExLjk5Myw2MS4xMDZMMTEzLjkyMiwyNjkuODAzeiIgZmlsbD0iIzk5OTk5OSIvPgoJCTxwYXRoIGQ9Ik0yMzcuNTQ1LDMyOC44OTdjMjUuMTI2LDAsNDYuNjM4LTguOTQ2LDY0LjUyMS0yNi44M2MxNy44OTEtMTcuODg0LDI2LjgzNy0zOS4zOTksMjYuODM3LTY0LjUyNXYtMzYuNTQ3TDQzMS45NzIsOTcuOTI5ICAgIGMxLjkwMi0xLjkwMywyLjg1NC00LjA5MywyLjg1NC02LjU2N2MwLTIuNDc0LTAuOTUyLTQuNjY0LTIuODU0LTYuNTY3bC0yMy40MDctMjMuNDEzYy0xLjkxLTEuOTA2LTQuMDk3LTIuODU2LTYuNTctMi44NTYgICAgYy0yLjQ3MiwwLTQuNjYxLDAuOTUtNi41NjQsMi44NTZMNDMuMTE3LDQxMy42OThjLTEuOTAzLDEuOTAyLTIuODUyLDQuMDkzLTIuODUyLDYuNTYzYzAsMi40NzgsMC45NDksNC42NjgsMi44NTIsNi41NyAgICBsMjMuNDExLDIzLjQxMWMxLjkwNCwxLjkwMyw0LjA5NSwyLjg1MSw2LjU2NywyLjg1MWMyLjQ3NSwwLDQuNjY1LTAuOTQ3LDYuNTY3LTIuODUxbDcyLjUxOS03Mi41MTkgICAgYzIwLjkzMywxMi45NDksNDMuMjk5LDIwLjY1Niw2Ny4wOTMsMjMuMTI3djM3LjY5MWgtNzMuMDg5Yy00Ljk0OSwwLTkuMjM1LDEuODExLTEyLjg0Nyw1LjQyOCAgICBjLTMuNjE4LDMuNjEzLTUuNDMsNy44OTgtNS40MywxMi44NDdjMCw0Ljk0MSwxLjgxMiw5LjIzMyw1LjQzLDEyLjg0N2MzLjYxMiwzLjYxNCw3Ljg5OCw1LjQyOCwxMi44NDcsNS40MjhoMTgyLjcxOCAgICBjNC45NDgsMCw5LjIzMi0xLjgxMywxMi44NDctNS40MjhjMy42Mi0zLjYxMyw1LjQyOC03LjkwNSw1LjQyOC0xMi44NDdjMC00Ljk0OC0xLjgwOC05LjIzMy01LjQyOC0xMi44NDcgICAgYy0zLjYxNC0zLjYxNy03Ljg5OC01LjQyOC0xMi44NDctNS40MjhoLTczLjA4N3YtMzcuNjkxYzQxLjMwMi00LjU2NSw3NS45ODgtMjIuNDA4LDEwNC4wNjctNTMuNTI2ICAgIGMyOC4wNzItMzEuMTE3LDQyLjExLTY3LjcxMSw0Mi4xMS0xMDkuNzc2di0zNi41NTRjMC00Ljk0Ny0xLjgwOC05LjIyOS01LjQyMS0xMi44NDVjLTMuNjIxLTMuNjE3LTcuOTAyLTUuNDI2LTEyLjg1MS01LjQyNiAgICBjLTQuOTQ1LDAtOS4yMjksMS44MDktMTIuODQ3LDUuNDI2Yy0zLjYxNywzLjYxNi01LjQyNCw3Ljg5OC01LjQyNCwxMi44NDV2MzYuNTQ3YzAsMzUuMjE0LTEyLjUxOSw2NS4zMzMtMzcuNTQ1LDkwLjM1OSAgICBzLTU1LjE1MSwzNy41NDQtOTAuMzYyLDM3LjU0NGMtMjAuNTU3LDAtNDAuMDY1LTQuODQ5LTU4LjUyOS0xNC41NjFsMjcuNDA4LTI3LjQwMSAgICBDMjE2LjcwNywzMjcuMDk3LDIyNy4wNzksMzI4Ljg5NywyMzcuNTQ1LDMyOC44OTd6IiBmaWxsPSIjOTk5OTk5Ii8+CgkJPHBhdGggZD0iTTI5MC4yMjMsMTYuODQ5QzI3NC41MTgsNS42MTgsMjU2Ljk1OSwwLDIzNy41NDUsMGMtMjUuMTI1LDAtNDYuNjM1LDguOTUxLTY0LjUyNCwyNi44NCAgICBjLTE3Ljg5LDE3Ljg5LTI2LjgzNSwzOS4zOTktMjYuODM1LDY0LjUyNXYxNDYuMTc3TDMyMy40ODMsNjAuMjQ0QzMxNy4wMDgsNDIuNTQzLDMwNS45MjcsMjguMDc3LDI5MC4yMjMsMTYuODQ5eiIgZmlsbD0iIzk5OTk5OSIvPgoJPC9nPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=);
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