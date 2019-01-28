<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * DPlayer for typecho
 *
 * @package DPlayer
 * @author Volio
 * @version 1.0.1
 * @link http://github.com/volio/DPlayer-for-typecho
 */
class DPlayer_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('DPlayer_Plugin', 'playerparse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('DPlayer_Plugin', 'playerparse');
        Typecho_Plugin::factory('Widget_Archive')->header = array('DPlayer_Plugin', 'playerHeader');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('DPlayer_Plugin', 'playerFooter');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('DPlayer_Plugin', 'addButton');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('DPlayer_Plugin', 'addButton');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
    }

    /**
     * 插入顶部代码
     */
    public static function playerHeader()
    {
        $url = Helper::options()->pluginUrl . '/DPlayer';
        echo <<<EOF
<link rel="stylesheet" type="text/css" href="$url/dist/DPlayer.min.css" />
<script>var dPlayers = [];var dPlayerOptions = [];</script>
EOF;
    }

    /**
     * 插入底部代码
     */
    public static function playerFooter()
    {
        $url = Helper::options()->pluginUrl . '/DPlayer';
        if (Typecho_Widget::widget('Widget_Options')->plugin('DPlayer')->hls) {
            echo "<script type=\"text/javascript\" src=\"$url/plugin/hls.min.js\"></script>\n";
        }
        if (Typecho_Widget::widget('Widget_Options')->plugin('DPlayer')->flv) {
            echo "<script type=\"text/javascript\" src=\"$url/plugin/flv.min.js\"></script>\n";
        }
        echo <<<EOF
<script type="text/javascript" src="$url/dist/DPlayer.min.js"></script>
<script type="text/javascript" src="$url/dist/init-dplayer.js"></script>
EOF;
    }

    /**
     * 内容标签替换
     *
     * @param string $content
     * @return string
     */
    public static function playerparse($content, $widget, $lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;
        if ($widget instanceof Widget_Archive) {
            if (false === strpos($content, '[')) {
                return $content;
            }
            $pattern = self::get_shortcode_regex(array('dplayer'));
            $content = preg_replace_callback("/$pattern/", array('DPlayer_Plugin', 'parseCallback'), $content);
        }
        return $content;
    }

    /**
     * 回调解析
     * @param unknown $matches
     * @return string
     */
    public static function parseCallback($matches)
    {
        /*
            $mathes array
            * 1 - An extra [ to allow for escaping shortcodes with double [[]]
             * 2 - The shortcode name
             * 3 - The shortcode argument list
             * 4 - The self closing /
             * 5 - The content of a shortcode when it wraps some content.
             * 6 - An extra ] to allow for escaping shortcodes with double [[]]
         */
        // allow [[player]] syntax for escaping the tag
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        //还原转义后的html
        //[dplayer title=&quot;Test Abc&quot; artist=&quot;haha&quot; id=&quot;1234543&quot;/]
        $attr = htmlspecialchars_decode($matches[3]);
        //[dplayer]标签的属性，类型为array
        $atts = self::shortcode_parse_atts($attr);
        //播放器id
        $id = md5($_SERVER['HTTP_HOST'] . $atts['url']);

        //播放器设置
        $theme = Typecho_Widget::widget('Widget_Options')->plugin('DPlayer')->theme;
        $api = Typecho_Widget::widget('Widget_Options')->plugin('DPlayer')->api;
        if (!$theme) $theme = '#FADFA3';

        //输出代码
        $playerCode = '<div id="player' . $id . '" class="dplayer">';
        $playerCode .= "</div>\n";

        $video = array(
            'url' => isset($atts['url']) ? $atts['url'] : '',
            'pic' => isset($atts['pic']) ? $atts['pic'] : '',
            'type' => isset($atts['type']) ? $atts['type'] : 'auto',
            'thumbnails' => isset($atts['thumbnails']) ? $atts['thumbnails'] : '',
        );
        //弹幕部分配置文件
        $subtitle = array(
            'url' => isset($atts['subtitleurl']) ? $atts['subtitleurl'] : '',
            'type' => isset($atts['subtitletype']) ? $atts['subtitletype'] : 'webvtt',
            'fontSize' => isset($atts['subtitlefontsize']) ? $atts['subtitlefontsize'] : '25px',
            'bottom' => isset($atts['subtitlebottom']) ? $atts['subtitlebottom'] : '10%',
            'color' => isset($atts['subtitlecolor']) ? $atts['subtitlecolor'] : '#b7daff',
        );
        $danmaku = array(
            'id' => md5($id),
            'api' => $api,
            'maximum' => isset($atts['maximum']) ? $atts['maximum'] : 1000,
            'addition' => isset($atts['addition']) ? array($atts['addition']) : null,
            'user' => isset($atts['user']) ? $atts['user'] : 'DIYgod',
            'bottom' => isset($atts['bottom']) ? $atts['bottom'] : '15%',
            'unlimited' => true,
        );

        //播放器默认属性
        $data = array(
            'id' => $id,
            'live' => false,
            'autoplay' => false,
            'theme' => isset($atts['theme']) ? $atts['theme'] : '#FADFA3',
            'loop' => false,
            'screenshot' => false,
            'hotkey' => true,
            'preload' => 'metadata',
            'lang' => isset($atts['lang']) ? $atts['lang'] : 'zh-cn',
            'logo' => isset($atts['logo']) ? $atts['logo'] : null,
            'volume' => isset($atts['volume']) ? $atts['volume'] : 0.7,
            'mutex' => true,
        );
        $data['video'] = $video;
        $data['danmaku'] = (isset($atts['danmu']) && $atts['danmu'] != 'false') ? $danmaku : null;
        $data['subtitle'] = isset($atts['subtitleurl']) ? $subtitle : null;
        $data['autoplay'] = (isset($atts['autoplay']) && $atts['autoplay'] == 'true') ? true : false;
        $data['theme'] = isset($atts['theme']) ? $atts['theme'] : $theme;
        //加入头部数组
        $js = json_encode($data);
        $playerCode .= <<<EOF
<script>dPlayerOptions.push({$js});</script>
EOF;
        return $playerCode;
    }

    public static function addButton() 
    {
        $dir = Helper::options()->pluginUrl.'/DPlayer/dist/editor.js';
        echo "<script type=\"text/javascript\" src=\"{$dir}\"></script>";
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $theme = new Typecho_Widget_Helper_Form_Element_Text(
            'theme', null, '#FADFA3',
            _t('默认主题颜色'), _t('播放器默认的主题颜色，如 #372e21、#75c、red、blue，该设定会被[dplayer]标签中的theme属性覆盖，默认为 #FADFA3'));
        $api = new Typecho_Widget_Helper_Form_Element_Text(
            'api', null, 'https://api.prprpr.me/dplayer/v3/',
            _t('弹幕服务器地址'), _t('用于保存视频弹幕，默认为 https://api.prprpr.me/dplayer/v3/'));
        $hls = new Typecho_Widget_Helper_Form_Element_Radio('hls', array('0' => _t('不开启HLS支持'), '1' => _t('开启HLS支持')), '0', _t('HLS支持'), _t("开启后可解析 m3u8 格式视频"));
        $flv = new Typecho_Widget_Helper_Form_Element_Radio('flv', array('0' => _t('不开启FLV支持'), '1' => _t('开启FLV支持')), '0', _t('FLV支持'), _t("开启后可解析 flv 格式视频"));
        $form->addInput($theme);
        $form->addInput($api);
        $form->addInput($hls);
        $form->addInput($flv);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * Retrieve all attributes from the shortcodes tag.
     *
     * The attributes list has the attribute name as the key and the value of the
     * attribute as the value in the key/value pair. This allows for easier
     * retrieval of the attributes, since all attributes have to be known.
     *
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php
     * @since 2.5.0
     *
     * @param string $text
     * @return array|string List of attribute values.
     *                      Returns empty array if trim( $text ) == '""'.
     *                      Returns empty string if trim( $text ) == ''.
     *                      All other matches are checked for not empty().
     */
    private static function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }

            // Reject any unclosed HTML elements
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * The regular expression combines the shortcode tags in the regular expression
     * in a regex class.
     *
     * The regular expression contains 6 different sub matches to help with parsing.
     *
     * 1 - An extra [ to allow for escaping shortcodes with double [[]]
     * 2 - The shortcode name
     * 3 - The shortcode argument list
     * 4 - The self closing /
     * 5 - The content of a shortcode when it wraps some content.
     * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     *
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php
     * @since 2.5.0
     *
     *
     * @param array $tagnames List of shortcodes to find. Optional. Defaults to all registered shortcodes.
     * @return string The shortcode search regular expression
     */
    private static function get_shortcode_regex($tagnames = null)
    {
        $tagregexp = join('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
}