<?php

namespace Kenepa\TranslationManager\Http\Middleware;

use Closure;
use DB;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        if (! $request->hasSession() ||   !$request->session()->get('language')) {
            $siteCode = $this->extractSiteKeyFromUrl($request);

            //サイトごとにデフォルト言語を設定する
            $default_locale = 'ja';
            if ($siteCode) {
                $default_locale = $this->getDefaultLocaleFromDatabase($siteCode);
            }
            $locale = $default_locale ?? config('app.fallback_locale', 'en');
            $request->session()->put('language', $locale);
        }
        else{
            $locale = config('app.locale') ?? config('app.fallback_locale', 'en');
        }

        App::setLocale($request->session()->get('language', $locale));

        return $next($request);
    }

    /**
     * URLからサイトキーを抽出
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    private function extractSiteKeyFromUrl($request)
    {
        $path = $request->path();

        // パスを / で分割して最初のセグメントを取得
        $segments = explode('/', $path);

        // 最初のセグメントがキーと仮定（空でない場合）
        if (!empty($segments[0]) && $segments[0] !== '/') {
            return $segments[0];
        }

        return null;
    }
    /**
     * データベースからデフォルトロケールを取得
     *
     * @param  string  $siteCode
     * @return string
     */
    private function getDefaultLocaleFromDatabase($siteCode)
    {
        try {
            // サイト設定テーブルから言語設定を取得（テーブル名は適切に変更してください）
            $site = DB::table('sites')
                ->where('site_code', $siteCode)
                ->first();

            if ($site && isset($site->default_locale)) {
                return $site->default_locale;
            }

            // 別のテーブル構造の例
            // $config = DB::table('site_configs')
            //     ->where('key', $siteKey)
            //     ->where('config_name', 'default_locale')
            //     ->first();
            //
            // if ($config) {
            //     return $config->config_value;
            // }

        } catch (\Exception $e) {
            // ログ出力などのエラーハンドリング
            \Log::error('Failed to get default locale from database: ' . $e->getMessage());
        }

        // デフォルト値を返す
        return 'ja';
    }
}
