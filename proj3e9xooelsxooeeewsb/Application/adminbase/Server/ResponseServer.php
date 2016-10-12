<?php namespace adminbase\Server;

use Cml\Http\Response;
use Cml\Server;
use Cml\View;

class ResponseServer extends Server
{
    /**
     * 显示js Alert 并跳转到上一步|指定地址
     *
     * @param string $tip alert的提示信息
     * @param mixed $url 要跳转的地址
     */
    public static function showAlertAndGoBack($tip, $url = false)
    {
        $url = $url ? "window.location.href='".Response::url($url ,false)."';" : 'window.history.back(-1);';
        $str = <<<str
				<script type="text/javascript">
					alert('{$tip}');
					{$url}
				</script>
str;
        exit($str);
    }

    /**
     * 渲染json输出
     *
     * @param int $code
     * @param string $msg
     * @param array $data
     */
    public static function renderJson($code, $msg = '未登录', $data = array())
    {
        View::getEngine('Json')
            ->assign('code', $code)
            ->assign('msg', $msg)
            ->assign('data', $data)
            ->display();
    }

    /**
     * js跳转
     *
     * @param string $url
     */
    public static function jsJump($url)
    {
        $url = Response::url($url, false);
        echo <<<str
            <script>
                window.location.href='{$url}';
            </script>
str;
        exit();
    }
}