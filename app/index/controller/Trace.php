<?php
declare(strict_types=1);

namespace app\index\controller;

use app\admin\model\mes\TraceCodeModel;
use app\admin\model\mes\ReportMediaModel;
use think\facade\View;
use think\facade\Lang;
use think\response\Json;
use think\Response;

class Trace
{
    /** 按 cookie 设置语言并加载当前控制器语言包 */
    private function ensureLang(): void
    {
        $cookieVar = config('lang.cookie_var', 'think_lang');
        $cookieVal = request()->cookie($cookieVar, '');
        if ($cookieVal !== '' && $cookieVal !== null) {
            $allow = config('lang.allow_lang_list', []);
            if (is_array($allow) && in_array($cookieVal, $allow, true)) {
                Lang::setLangSet($cookieVal);
            }
        }
        $langSet = Lang::getLangSet();
        $ctrl = (new \ReflectionClass($this))->getShortName();
        $path = app()->getAppPath() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . $ctrl . '.php';
        if (is_file($path)) {
            Lang::load($path);
        }
    }

    private function fetchWithLayout(string $template): string
    {
        $this->ensureLang();
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    private function normalizeMediaUrl($val): string
    {
        if (is_array($val)) {
            $flat = [];
            foreach ($val as $v) {
                if (is_array($v)) {
                    foreach ($v as $vv) {
                        if ($vv !== '' && $vv !== null && $vv !== false) {
                            $flat[] = (string) $vv;
                        }
                    }
                } elseif ($v !== '' && $v !== null && $v !== false) {
                    $flat[] = (string) $v;
                }
            }
            if ($flat) {
                return $flat[0];
            }
            return '';
        }

        $url = trim((string) $val);
        if ($url === '') {
            return '';
        }

        $decoded = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
        if ($decoded !== '' && $decoded !== $url) {
            $url = $decoded;
        }

        $url = trim($url, " \t\n\r\0\x0B\"'");

        $first = $url[0] ?? '';
        if ($first === '[' || $first === '{') {
            $tmp = json_decode($url, true);
            if (is_array($tmp)) {
                $flat = [];
                foreach ($tmp as $v) {
                    if (is_array($v)) {
                        foreach ($v as $vv) {
                            if ($vv !== '' && $vv !== null && $vv !== false) {
                                $flat[] = (string) $vv;
                            }
                        }
                    } elseif ($v !== '' && $v !== null && $v !== false) {
                        $flat[] = (string) $v;
                    }
                }
                if ($flat) {
                    return $flat[0];
                }
            } elseif (is_string($tmp) && $tmp !== '') {
                return trim($tmp);
            }
        }

        if (preg_match('@https?://[^\s"\'<>]+@', $url, $m)) {
            return $m[0];
        }

        return $url;
    }

    public function query(): Json
    {
        $code = trim((string) request()->get('code'));
        if ($code === '') {
            return json([
                'code' => 0,
                'msg'  => '追溯码不能为空',
                'data' => [],
            ]);
        }

        $trace = TraceCodeModel::with(['order', 'model.product', 'process', 'report.allocation'])
            ->where('trace_code', $code)
            ->where('status', 1)
            ->find();

        if (!$trace) {
            return json([
                'code' => 0,
                'msg'  => '追溯码不存在或已失效',
                'data' => [],
            ]);
        }

        $trace->scan_count += 1;
        $trace->last_scan_time = time();
        $trace->save();

        return json([
            'code' => 1,
            'msg'  => '查询成功',
            'data' => $trace->toArray(),
        ]);
    }

    public function detail(): string|Response
    {
        $idParam = trim((string) request()->get('id', ''));
        $codeParam = trim((string) request()->get('code', ''));

        $query = TraceCodeModel::with(['order', 'model.product', 'process', 'report.allocation']);
        if ($idParam !== '') {
            if (ctype_digit($idParam)) {
                $query->where('id', (int) $idParam);
            } else {
                $query->where('trace_code', $idParam);
            }
        } elseif ($codeParam !== '') {
            $query->where('trace_code', $codeParam);
        } else {
            View::assign('trace', null);
            View::assign('images', []);
            View::assign('videos', []);
            View::assign('title', '追溯详情');
            return View::fetch('trace/detail');
        }

        $trace = $query->where('status', 1)->find();
        $images = [];
        $videos = [];

        if ($trace && $trace->report_id) {
            $mediaList = ReportMediaModel::where('report_id', (int) $trace->report_id)->select()->toArray();
            foreach ($mediaList as $m) {
                $type = $m['type'] ?? 'image';
                $url = $this->normalizeMediaUrl($m['url'] ?? '');
                if ($url === '') {
                    continue;
                }
                if ($type === 'video') {
                    $videos[] = $url;
                } else {
                    $images[] = $url;
                }
            }
        }

        View::assign('trace', $trace ? $trace->toArray() : null);
        View::assign('images', $images);
        View::assign('videos', $videos);
        View::assign('title', '追溯详情');

        return View::fetch('trace/detail');
    }
}
