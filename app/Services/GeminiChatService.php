<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiChatService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected string $model;
    protected string $extraContext;
    protected string $adminTrainingPrompt;
    protected array $guides;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.assistant_model', 'gemini-2.5-flash-lite');
        $this->extraContext = trim((string) config('services.gemini.assistant_context', ''));
        $this->adminTrainingPrompt = trim((string) Setting::get('ai_assistant_prompt', ''));
        $this->guides = (array) config('assistant_guides.guides', []);
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    public function chat(string $message, ?string $previousResponseId = null, array $meta = []): array
    {
        $shouldRecommendCourses = $this->shouldRecommendCourses($message, $meta);
        $recommendations = $shouldRecommendCourses ? $this->recommendCourses($message) : [];
        $matchedGuides = $this->matchGuides($message, $meta);

        if (! $this->isConfigured()) {
            Log::warning('GeminiChatService falling back: GEMINI_API_KEY is empty.', [
                'env' => app()->environment(),
                'has_base_url' => $this->baseUrl !== '',
                'model' => $this->model,
            ]);

            return [
                'success' => true,
                'message' => $this->buildFallbackReply($message, $recommendations, 'missing_api_key', $shouldRecommendCourses, $matchedGuides),
                'response_id' => null,
                'recommended_courses' => $recommendations,
                'source' => 'fallback_local',
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $this->buildInstructions($matchedGuides)],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->buildUserPrompt($message, $meta, $recommendations, $shouldRecommendCourses)],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.45,
                'maxOutputTokens' => 500,
            ],
        ];

        try {
            $response = $this->client()->post(
                sprintf('/models/%s:generateContent?key=%s', $this->model, $this->apiKey),
                $payload
            );
        } catch (\Throwable $exception) {
            Log::error('GeminiChatService HTTP call failed.', [
                'model' => $this->model,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => true,
                'message' => $this->buildFallbackReply($message, $recommendations, 'api_error', $shouldRecommendCourses, $matchedGuides),
                'response_id' => null,
                'recommended_courses' => $recommendations,
                'source' => 'fallback_local',
            ];
        }

        if (! $response->successful()) {
            $details = $response->json() ?: $response->body();
            $status = $response->status();

            Log::warning('GeminiChatService API error.', [
                'model' => $this->model,
                'status' => $status,
                'details' => is_array($details) ? array_slice($details, 0, 5, true) : Str::limit((string) $details, 500),
            ]);

            return [
                'success' => true,
                'message' => $this->buildFallbackReply($message, $recommendations, 'api_error', $shouldRecommendCourses, $matchedGuides),
                'status' => $status,
                'details' => $details,
                'response_id' => null,
                'recommended_courses' => $recommendations,
                'source' => 'fallback_local',
            ];
        }

        $data = $response->json();
        $text = $this->extractOutputText($data);

        return [
            'success' => true,
            'message' => $text ?: $this->buildFallbackReply($message, $recommendations, 'empty_response', $shouldRecommendCourses, $matchedGuides),
            'response_id' => null,
            'recommended_courses' => $recommendations,
            'raw' => $data,
            'source' => 'gemini',
        ];
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    protected function buildInstructions(array $matchedGuides = []): string
    {
        $siteName = Setting::get('site_name', 'Khai Tri Education');
        $tagline = Setting::get('site_tagline', 'Nền tảng học tập trực tuyến');
        $contactEmail = Setting::get('contact_email', '');
        $contactPhone = Setting::get('contact_phone', '');
        $contactAddress = Setting::get('contact_address', '');

        $categories = CourseCategory::query()
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->take(12)
            ->values()
            ->all();

        $courses = Course::query()
            ->with('category')
            ->published()
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($course) {
                $price = $course->sale_price ?: $course->price;

                return sprintf(
                    '- %s | nhóm ngành: %s | hình thức: %s | giá: %s VND | trình độ: %s | thời lượng: %s',
                    $course->title,
                    $course->category->name ?? 'Chưa phân loại',
                    $course->learning_type ?? 'online',
                    number_format((float) $price, 0, ',', '.'),
                    $course->level ?: 'Chưa rõ',
                    $course->duration_label ?: 'Chưa rõ'
                );
            })
            ->implode("\n");

        $parts = [
            "Bạn là trợ lý ảo của {$siteName}.",
            'Mục tiêu chính: hỗ trợ người dùng về khóa học, học phí, đăng ký học, lịch học, module, video, quiz, chứng chỉ, ví, thanh toán và liên hệ hỗ trợ.',
            'Luôn trả lời bằng tiếng Việt có dấu, thân thiện, rõ ràng, tối đa 6 câu ngắn hoặc 5 gạch đầu dòng.',
            'Ưu tiên trả lời đúng trọng tâm câu hỏi hiện tại trước, sau đó mới gợi ý bước tiếp theo nếu cần.',
            'Không tự động chèn tên khóa học, không quảng bá khóa học và không gợi ý khóa học nếu người dùng không hỏi về khóa học, lộ trình học, học phí, đăng ký học hoặc tư vấn chọn môn phù hợp.',
            'Nếu người dùng hỏi về tài khoản, OTP, ví, nạp tiền, thanh toán, liên hệ, tin tức hoặc thao tác trên website, chỉ trả lời đúng chủ đề đó.',
            'Nếu người dùng đang ở trang chi tiết một khóa học và hỏi về khóa đang xem, hãy ưu tiên giải thích khóa đó. Không tự kéo thêm khóa học khác nếu người dùng không yêu cầu.',
            'Nếu thiếu dữ liệu để tư vấn khóa học, hãy hỏi lại tối đa 1 câu ngắn để làm rõ mục tiêu học, trình độ hiện tại hoặc hình thức học mong muốn.',
            'Chỉ dựa trên dữ liệu website. Nếu thiếu thông tin, nói rõ là chưa chắc và mời người dùng liên hệ bộ phận tư vấn.',
            'Nếu thật sự phù hợp, gợi ý tối đa 3 khóa học. Không bịa đặt học phí, lịch khai giảng hay khuyến mãi.',
            'Thông tin website:',
            "- Tên website: {$siteName}",
            "- Mô tả: {$tagline}",
            '- Email liên hệ: ' . ($contactEmail ?: 'Chưa cập nhật'),
            '- Số điện thoại: ' . ($contactPhone ?: 'Chưa cập nhật'),
            '- Địa chỉ: ' . ($contactAddress ?: 'Chưa cập nhật'),
            '- Nhóm ngành hiện có: ' . (! empty($categories) ? implode(', ', $categories) : 'Chưa cập nhật'),
            "- Một số khóa học đang hiển thị:\n" . ($courses ?: '- Chưa có dữ liệu khóa học published.'),
        ];

        $guidesBlock = $this->formatGuidesForPrompt($matchedGuides);
        if ($guidesBlock !== '') {
            $parts[] = $guidesBlock;
        }

        if ($this->adminTrainingPrompt !== '') {
            $parts[] = "Hướng dẫn bổ sung từ quản trị viên:\n{$this->adminTrainingPrompt}";
        }

        if ($this->extraContext !== '') {
            $parts[] = "Ngữ cảnh bổ sung từ biến môi trường:\n{$this->extraContext}";
        }

        return implode("\n\n", $parts);
    }

    protected function buildUserPrompt(string $message, array $meta = [], array $recommendations = [], bool $shouldRecommendCourses = false): string
    {
        $segments = [];

        if (! empty($meta['current_url'])) {
            $segments[] = 'Người dùng đang ở trang: ' . $meta['current_url'];
        }

        if (! empty($meta['page_title'])) {
            $segments[] = 'Tiêu đề trang hiện tại: ' . $meta['page_title'];
        }

        $segments[] = $shouldRecommendCourses
            ? 'Chế độ gợi ý khóa học: BẬT. Chỉ nhắc đến hoặc gợi ý khóa học nếu thật sự giúp trả lời đúng câu hỏi.'
            : 'Chế độ gợi ý khóa học: TẮT. Không gợi ý hay liệt kê khóa học, chỉ trả lời đúng vấn đề người dùng đang hỏi.';

        $history = collect($meta['history'] ?? [])
            ->filter(fn ($item) => filled($item['message'] ?? null))
            ->take(-8)
            ->map(function ($item) {
                $role = ($item['role'] ?? 'user') === 'assistant' ? 'Trợ lý' : 'Người dùng';

                return $role . ': ' . trim((string) $item['message']);
            })
            ->values()
            ->all();

        if (! empty($history)) {
            $segments[] = "Hội thoại gần đây:\n" . implode("\n", $history);
        }

        if (! empty($recommendations)) {
            $segments[] = "Khóa học nên ưu tiên gợi ý nếu phù hợp:\n" . collect($recommendations)
                ->map(fn ($course) => '- ' . $course['title'] . ' | ' . ($course['category'] ?: 'Chưa phân loại') . ' | ' . $course['price_label'])
                ->implode("\n");
        }

        $segments[] = 'Câu hỏi của người dùng: ' . trim($message);

        return implode("\n\n", $segments);
    }

    protected function extractOutputText(array $data): string
    {
        $parts = data_get($data, 'candidates.0.content.parts', []);

        foreach ($parts as $part) {
            $candidate = trim((string) ($part['text'] ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    protected function shouldRecommendCourses(string $message, array $meta = []): bool
    {
        $normalizedMessage = $this->normalizeText($message);

        if ($normalizedMessage === '') {
            return false;
        }

        $directCourseKeywords = [
            'khoa hoc',
            'tu van khoa hoc',
            'goi y khoa hoc',
            'chon khoa hoc',
            'dang ky hoc',
            'hoc phi',
            'lich hoc',
            'khai giang',
            'lo trinh hoc',
            'module',
            'lop hoc',
            'dot hoc',
            'giang vien',
            'chung chi',
            'online',
            'offline',
            'so sanh khoa hoc',
        ];

        $learningGoalKeywords = [
            'muon hoc',
            'nen hoc',
            'hoc gi',
            'bat dau tu dau',
            'cho nguoi moi',
            'co ban',
            'nang cao',
            'di lam',
            'thi chung chi',
            'muc tieu hoc',
        ];

        $supportOnlyKeywords = [
            'dang nhap',
            'dang ky tai khoan',
            'quen mat khau',
            'otp',
            'xac thuc',
            '2fa',
            'tin tuc',
            'lien he',
            'dia chi',
            'so dien thoai',
            'email',
            'wallet',
            'vi',
            'nap tien',
            'topup',
            'thanh toan',
            'hoa don',
            'tai khoan',
        ];

        $hasCourseIntent = Str::contains($normalizedMessage, $directCourseKeywords)
            || Str::contains($normalizedMessage, $learningGoalKeywords)
            || $this->matchesCatalogTerm($normalizedMessage);

        if (! $hasCourseIntent && Str::contains($normalizedMessage, $supportOnlyKeywords)) {
            return false;
        }

        return $hasCourseIntent;
    }

    protected function recommendCourses(string $message): array
    {
        $normalizedMessage = $this->normalizeText($message);
        $keywords = collect(explode(' ', $normalizedMessage))
            ->map(fn ($keyword) => trim($keyword))
            ->filter(fn ($keyword) => Str::length($keyword) >= 3)
            ->unique()
            ->values();

        $courses = Course::query()
            ->with('category')
            ->published()
            ->latest()
            ->take(24)
            ->get();

        if ($courses->isEmpty()) {
            return [];
        }

        $scored = $courses->map(function (Course $course) use ($normalizedMessage, $keywords) {
            $title = $this->normalizeText($course->title ?? '');
            $shortDescription = $this->normalizeText(strip_tags((string) $course->short_description));
            $description = $this->normalizeText(strip_tags((string) $course->description));
            $category = $this->normalizeText((string) optional($course->category)->name);
            $learningType = $this->normalizeText((string) $course->learning_type);
            $level = $this->normalizeText((string) $course->level);
            $haystack = implode(' ', [$title, $shortDescription, $description, $category, $learningType, $level]);

            $score = 0;
            $matched = [];

            if ($normalizedMessage !== '' && Str::contains($title, $normalizedMessage)) {
                $score += 8;
                $matched[] = 'tiêu đề trùng nhu cầu';
            }

            foreach ($keywords as $keyword) {
                if (Str::contains($title, $keyword)) {
                    $score += 4;
                    $matched[] = 'phù hợp từ khóa "' . $keyword . '"';
                } elseif (Str::contains($category, $keyword) || Str::contains($learningType, $keyword) || Str::contains($level, $keyword)) {
                    $score += 3;
                    $matched[] = 'gần với chủ đề "' . $keyword . '"';
                } elseif (Str::contains($haystack, $keyword)) {
                    $score += 1;
                }
            }

            if ($course->is_featured) {
                $score += 1;
            }

            if ($course->is_popular) {
                $score += 1;
            }

            return [
                'course' => $course,
                'score' => $score,
                'reason' => collect($matched)->unique()->take(2)->implode(', '),
            ];
        });

        $selected = $scored
            ->sortByDesc(fn ($item) => $item['score'])
            ->values()
            ->when(
                $scored->max('score') <= 0,
                fn (Collection $items) => $items->sortByDesc(fn ($item) => (($item['course']->is_featured ? 1 : 0) + ($item['course']->is_popular ? 1 : 0)))
            )
            ->take(3)
            ->values();

        return $selected->map(function ($item) {
            /** @var \App\Models\Course $course */
            $course = $item['course'];
            $price = $course->final_price;

            return [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->category->name ?? 'Chưa phân loại',
                'learning_type' => $course->learning_type ?: 'online',
                'level' => $course->level ?: 'Chưa rõ',
                'duration' => $course->duration_label ?: 'Chưa cập nhật',
                'price_label' => ((float) $price) > 0 ? number_format((float) $price, 0, ',', '.') . ' VND' : 'Liên hệ tư vấn',
                'url' => route('courses.show', $course),
                'reason' => $item['reason'] ?: 'phù hợp với nhu cầu hiện tại',
            ];
        })->all();
    }

    protected function buildFallbackReply(string $message, array $recommendations, string $reason, bool $courseIntent = false, array $matchedGuides = []): string
    {
        if (! $courseIntent) {
            return $this->buildNonCourseFallbackReply($message, $reason, $matchedGuides);
        }

        $intro = match ($reason) {
            'missing_api_key' => 'Trợ lý AI chưa được cấu hình đầy đủ nên mình đang tư vấn theo dữ liệu sẵn có trên website.',
            'api_error' => 'Trợ lý AI tạm thời gặp lỗi kết nối nên mình đang gợi ý nhanh theo dữ liệu website.',
            default => 'Mình đang trả lời dựa trên dữ liệu khóa học hiện có trên website.',
        };

        $suggestions = collect($recommendations)
            ->take(3)
            ->map(function ($course, $index) {
                return ($index + 1) . '. ' . $course['title'] . ' (' . $course['category'] . ', ' . $course['price_label'] . ')';
            })
            ->implode("\n");

        $parts = [$intro];

        if ($suggestions !== '') {
            $parts[] = "Bạn có thể tham khảo:\n" . $suggestions;
        } else {
            $parts[] = 'Hiện tại mình chưa tìm được khóa học thật sự sát nhu cầu từ nội dung bạn vừa gửi.';
        }

        $parts[] = 'Bạn có thể nói rõ hơn bạn muốn học môn gì, mục tiêu học là gì, trình độ hiện tại ra sao hoặc muốn học online/offline để mình gợi ý sát hơn.';

        return implode("\n\n", $parts);
    }

    protected function buildNonCourseFallbackReply(string $message, string $reason, array $matchedGuides = []): string
    {
        $intro = match ($reason) {
            'missing_api_key' => 'Trợ lý AI chưa được cấu hình đầy đủ nên mình đang hỗ trợ theo dữ liệu sẵn có trên website.',
            'api_error' => 'Trợ lý AI tạm thời gặp lỗi kết nối nên mình đang hỗ trợ theo dữ liệu sẵn có trên website.',
            default => 'Mình đang hỗ trợ bạn theo dữ liệu hiện có trên website.',
        };

        $primaryGuide = $matchedGuides[0] ?? null;
        if ($primaryGuide) {
            $stepLines = collect($primaryGuide['steps'] ?? [])
                ->map(fn ($step, $index) => ($index + 1) . '. ' . $step)
                ->implode("\n");

            $body = "Hướng dẫn nhanh — " . ($primaryGuide['title'] ?? 'Thao tác') . ":\n" . $stepLines;

            if (! empty($primaryGuide['notes'])) {
                $body .= "\nLưu ý: " . implode(' ', $primaryGuide['notes']);
            }

            return $intro . "\n\n" . $body;
        }

        return $intro . "\n\nBạn có thể nói rõ hơn bạn đang cần hỗ trợ về tài khoản, ví, thanh toán, liên hệ hay thao tác nào trên website để mình hướng dẫn đúng trọng tâm.";
    }

    protected function matchGuides(string $message, array $meta = []): array
    {
        if (empty($this->guides)) {
            return [];
        }

        $normalizedMessage = $this->normalizeText($message);
        $currentPath = $this->extractPath($meta['current_url'] ?? null);

        $scored = [];

        foreach ($this->guides as $key => $guide) {
            $score = 0;
            $keywords = $guide['keywords'] ?? [];

            foreach ($keywords as $keyword) {
                $needle = $this->normalizeText($keyword);
                if ($needle !== '' && Str::contains($normalizedMessage, $needle)) {
                    $score += 5;
                }
            }

            if ($currentPath !== '') {
                foreach (($guide['url_patterns'] ?? []) as $pattern) {
                    if (@preg_match($pattern, $currentPath) === 1) {
                        $score += 2;
                    }
                }
            }

            if ($score > 0) {
                $scored[] = [
                    'key' => is_string($key) ? $key : (string) $key,
                    'score' => $score,
                    'guide' => $guide,
                ];
            }
        }

        if (empty($scored)) {
            return [];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return collect($scored)
            ->take(3)
            ->map(fn ($item) => array_merge($item['guide'], ['key' => $item['key']]))
            ->all();
    }

    protected function formatGuidesForPrompt(array $matchedGuides): string
    {
        if (empty($matchedGuides)) {
            return '';
        }

        $blocks = [];

        foreach ($matchedGuides as $guide) {
            $title = $guide['title'] ?? 'Thao tác';
            $url = '';

            if (! empty($guide['route'])) {
                try {
                    $url = route($guide['route'], [], false);
                } catch (\Throwable) {
                    $url = '';
                }
            }

            $header = '### ' . $title . ($url !== '' ? ' (' . $url . ')' : '');
            $steps = collect($guide['steps'] ?? [])
                ->map(fn ($step, $index) => ($index + 1) . '. ' . $step)
                ->implode("\n");

            $block = $header . "\n" . $steps;

            if (! empty($guide['notes'])) {
                $block .= "\nLưu ý:\n- " . implode("\n- ", $guide['notes']);
            }

            $blocks[] = $block;
        }

        return "Cẩm nang thao tác phù hợp với câu hỏi (ưu tiên dùng các bước dưới đây để trả lời, giữ đúng tên trang và URL):\n\n"
            . implode("\n\n", $blocks);
    }

    protected function extractPath(?string $url): string
    {
        if (! is_string($url) || trim($url) === '') {
            return '';
        }

        $parsed = parse_url(trim($url));
        $path = $parsed['path'] ?? $url;

        return '/' . ltrim((string) $path, '/');
    }

    protected function matchesCatalogTerm(string $normalizedMessage): bool
    {
        $keywords = collect(explode(' ', $normalizedMessage))
            ->map(fn ($keyword) => trim($keyword))
            ->filter(fn ($keyword) => Str::length($keyword) >= 3)
            ->values();

        if ($keywords->isEmpty()) {
            return false;
        }

        $catalogTerms = Course::query()
            ->published()
            ->latest()
            ->take(40)
            ->pluck('title')
            ->merge(CourseCategory::query()->orderBy('name')->take(20)->pluck('name'));

        return $catalogTerms->contains(function ($term) use ($normalizedMessage, $keywords) {
            $normalizedTerm = $this->normalizeText((string) $term);

            if ($normalizedTerm === '') {
                return false;
            }

            if (Str::contains($normalizedTerm, $normalizedMessage) || Str::contains($normalizedMessage, $normalizedTerm)) {
                return true;
            }

            return $keywords->contains(fn ($keyword) => Str::contains($normalizedTerm, $keyword));
        });
    }

    protected function normalizeText(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}