<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * DPlayer for typecho
 *
 * @package DPlayer
 * @author Volio
 * @version 1.0.0
 * @link http://github.com/volio/DPlayer-for-typecho
 */

class DPlayer_Plugin implements Typecho_Plugin_Interface{

    protected static $playerID = 0;
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate(){
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('DPlayer_Plugin','playerparse');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('DPlayer_Plugin','playerparse');
        Typecho_Plugin::factory('Widget_Archive')->header = array('DPlayer_Plugin','playerCss');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('DPlayer_Plugin','playerJs');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 插入顶部css
     */
    public static function playerCss(){
        $url = Helper::options()->pluginUrl.'/DPlayer/DPlayer/dist';
        echo '<link rel="stylesheet" type="text/css" href="'.$url.'/DPlayer.min.css" />
        <script>var dPlayers = [];var dPlayerOptions = [];</script>';
    }

    /**
     * 插入底部js
     */
    public static function playerJs(){
        $url = Helper::options()->pluginUrl.'/DPlayer/DPlayer/dist';
        echo <<<EOF
<script type="text/javascript" src="$url/DPlayer.min.js"></script>
<script>
var len = dPlayerOptions.length;
for(var i=0;i<len;i++){
	dPlayers[i] = new DPlayer({
		element: document.getElementById('player' + dPlayerOptions[i]['id']),
            autoplay: dPlayerOptions[i]['autoplay'],
            video: dPlayerOptions[i]['video'],
            theme: dPlayerOptions[i]['theme']
	        });
	dPlayers[i].init();
}
</script>
EOF;
    }

    /**
     * 内容标签替换
     *
     * @param string $content
     * @return string
     */
    public static function playerparse($content,$widget,$lastResult)
    {
        $content = empty($lastResult) ? $content : $lastResult;
        if ($widget instanceof Widget_Archive) {
            if ( false === strpos( $content, '[' ) ) {
                return $content;
            }
            $pattern = self::get_shortcode_regex( array('dplayer') );
            $content = preg_replace_callback("/$pattern/",array('DPlayer_Plugin','parseCallback'), $content);
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
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }
        //播放器id
        $id = self::getUniqueId();
        //还原转义后的html
        //[dplayer title=&quot;Test Abc&quot; artist=&quot;haha&quot; id=&quot;1234543&quot;/]
        $attr = htmlspecialchars_decode($matches[3]);
        //[dplayer]标签的属性，类型为array
        $atts = self::shortcode_parse_atts($attr);

        $result = array(
            'url' => $atts['url']?$atts['url']:'',
            'pic' => $atts['pic']?$atts['pic']:''
        );
        if (empty($result)) return '';
        $theme = '#FADFA3';
        //播放器默认属性
        $data = array(
            'id' => $id ,
            'autoplay' => false,
            'theme' => $theme
        );
        //自动播放
        $data['autoplay'] = (bool)$data['autoplay'] && $data['autoplay'] !== 'false';
        //输出代码
        $playerCode =  '<div id="player'.$id.'" class="dplayer">';
        $playerCode .= "</div>\n";
        $data['video'] = $result;
        //加入头部数组
        $js = json_encode($data);
        $playerCode .= <<<EOF
<script>dPlayerOptions.push({$js});</script>
EOF;
        return $playerCode;
    }

    /**
     * 获取一个唯一的id以区分各个播放器实例
     * @return number
     */
    public static function getUniqueId()
    {
        return self::$playerID++;
    }

    public static function config(Typecho_Widget_Helper_Form $form){}

    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

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
    private static function shortcode_parse_atts($text) {
        $atts = array();
        $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
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
            foreach( $atts as &$value ) {
                if ( false !== strpos( $value, '<' ) ) {
                    if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
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
    private static function get_shortcode_regex( $tagnames = null ) {
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
}