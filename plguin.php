<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Hello World
 * 
 * @package GoogleRecaptcha 
 * @author qining
 * @version 0.0.1
 * @link https://www.ddnpc.com
 */
class GoogleRecaptcha_Plugin implements Typecho_Plugin_Interface
{
    public static $china_url = "https://www.recaptcha.net";
    public static $url = "https://www.google.com";
    public static $config;
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */

    public static function activate()
    {
        Typecho_Plugin::factory('admin/header.php')->header = array(__CLASS__, 'loadjs');
        Typecho_Plugin::factory('Widget_User')->login = array(__CLASS__, 'loginAction');
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
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $siteKey = new Typecho_Widget_Helper_Form_Element_Text('siteKey', NULL, '', _t('前往 <a href="https://www.google.com/recaptcha/admin">Google reCAPTCHA</a> 添加站点并获取 Site Key & Secret key<br>Site Key'));
        $secretKey = new Typecho_Widget_Helper_Form_Element_Text('secretKey', NULL, '','Secret Key');
        $jsChina = new Typecho_Widget_Helper_Form_Element_Radio('jsChina', array('1'=>_t("使用 recaptcha.net 镜像,推荐国内站点使用"),'0' =>_t("使用 Goole.com 镜像")), '1','JS 加载地址选择');
        $resChina = new Typecho_Widget_Helper_Form_Element_Radio('resChina', array('1'=>_t("使用 recaptcha.net 镜像,推荐国内站点使用"),'0'=>_t("使用 Goole.com 镜像")), '1','Service 验证地址选择');
        $form->addInput($siteKey);
        $form->addInput($secretKey);
        $form->addInput($jsChina);
        $form->addInput($resChina);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 在登陆界面插入JS并绑定事件
     * 
     * @access public
     * @return void
     */
    public static function loadjs($header)
    {
        if(!Typecho_Widget::widget('Widget_User')->hasLogin() && __TYPECHO_ADMIN__){
            $options = Helper::options();
            SELF::$config = $options->plugin('GoogleRecaptcha');
            if(empty(SELF::$config->siteKey) || empty(SELF::$config->secretKey)){
                return $header;
            }
            $url = SELF::$china_url;
            if(SELF::$config->jsChina === '0'){
                $url = SELF::$url;
            }
            $header.='
<script>
var GoogleRepactSiteKey = "' . SELF::$config->siteKey . '";
</script>
<script src="' . Typecho_Common::url('/GoogleRecaptcha/js/bindrecaptcha.js?v=0.0.1',$options->pluginUrl) . '"></script>
<script src="'.Typecho_Common::url('recaptcha/api.js',$url).'"></script>';
        }
        return $header;
    }

    /**
     *  response 校验
     */
    public static function loginAction($name, $password, $temporarily = false, $expire = 0){
        $user = Typecho_Widget::widget('Widget_User');
        $options = Helper::options();
        SELF::$config = $options->plugin('GoogleRecaptcha');
        if(!empty(SELF::$config->siteKey) && !empty(SELF::$config->secretKey)){
            $response = $user->request->from('g-recaptcha-response');
            $url = SELF::$china_url;
            if(SELF::$config->resChina === '0'){
                $url = SELF::$url;
            }
            if(empty($response) || empty($response['g-recaptcha-response']) || SELF::siteverify($url,SELF::$config->secretKey,$response['g-recaptcha-response']) !== TRUE){
                $user->widget('Widget_Notice')->set(_t('验证码无效'), 'error');
                $user->response->goBack();
            }
        }
        //以原方法验证账户密码.
        Typecho_Plugin::deactivate('GoogleRecaptcha');
        $result = $user->login($name, $password, $temporarily, $expire);
        return $result;
    }

    public static function siteverify($url,$secret,$response){
        $url = Typecho_Common::url('recaptcha/api/siteverify',$url);
        $ch = curl_init();
        curl_setopt_array($ch,array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => TRUE, 
            CURLOPT_SSL_VERIFYPEER  => FALSE,
            CURLOPT_SSL_VERIFYHOST  => FALSE,
        ));
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query(array(
            'secret' => $secret,
            'response' => $response,
        )));
        $data = curl_exec($ch);
        $res = json_decode($data,TRUE);
        if($res['success'] === TRUE){
            return TRUE;
        }
        return false;
    } 
}
