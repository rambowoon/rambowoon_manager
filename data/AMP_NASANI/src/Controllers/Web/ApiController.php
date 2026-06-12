<?php

namespace NASANICORE\Controllers\Web;

use Illuminate\Http\Request;
use NASANICORE\Controllers\Controller;
use NASANICORE\Core\Support\Facades\Func;
use NASANICORE\Models\NewslettersModel;

class ApiController extends Controller
{
    public function index(Request $request, $method = '')
    {
        return match ($method) {
            'newsletter-post-amp' => $this->NewsletterPostAMP($request),
            default => response()->json([
                'status' => 'error',
                'message' => __('web.method_khong_ton_tai')
            ]),
        };
    }

    public function NewsletterPostAMP(Request $request)
    {
        $isAmp = $request->input('is_amp') == 1 || $request->has('__amp_source_origin');
        $successTurnstile = false;
        $testTurnstile = false;

        if (!$isAmp) {
            $responseTurnstile = $request->input('cf-turnstile-response');
            $resultTurnstile = Func::checkTurnstile($responseTurnstile);
            $successTurnstile = (!empty($resultTurnstile['success'])) ? $resultTurnstile['success'] : false;
            $testTurnstile = (!empty($resultTurnstile['test'])) ? $resultTurnstile['test'] : false;
        }

        $dataNewsletter = (!empty($request->dataNewsletter)) ? $request->dataNewsletter : null;
        if ($isAmp || $successTurnstile || $testTurnstile) {
            foreach ($dataNewsletter as $column => $value) {
                $data[$column] = htmlspecialchars(Func::sanitize($value));
            }
            $data['subject'] = "Đăng ký nhận báo giá";
            $data['confirm_status'] = 1;
            $data['status'] = '1';
            $data['type'] = $data['type'];
            $data['date_created'] = time();
            $itemSave = NewslettersModel::create($data);
            if (!empty($itemSave)) {
                if ($request->has('__amp_source_origin')) {
                    $sourceOrigin = $request->input('__amp_source_origin') ?? $request->header('Origin');
                    $response = response()->json([
                        'message' => __('web.dangky_nhantin_thanhcong')
                    ]);
                    if ($sourceOrigin) {
                        $response->header('AMP-Access-Control-Allow-Source-Origin', $sourceOrigin);
                        $response->header('Access-Control-Allow-Credentials', 'true');
                    }
                    return $response;
                }
                return transfer(__('web.dangky_nhantin_thanhcong'), true, linkReferer());
            } else {
                if ($request->has('__amp_source_origin')) {
                    $sourceOrigin = $request->input('__amp_source_origin') ?? $request->header('Origin');
                    $response = response()->json([
                        'message' => __('web.dangky_nhantin_thatbai')
                    ], 400);
                    if ($sourceOrigin) {
                        $response->header('AMP-Access-Control-Allow-Source-Origin', $sourceOrigin);
                        $response->header('Access-Control-Allow-Credentials', 'true');
                    }
                    return $response;
                }
                return transfer(__('web.dangky_nhantin_thatbai'), false, linkReferer());
            }
        } else {
            return transfer(__('web.xacminh_thatbai'), false, linkReferer());
        }
    }
}
