<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiChatService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected string $model;
    protected string $extraContext;
    protected string $adminTrainingPrompt;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.assistant_model', 'gemini-2.5-flash-lite');
        $this->extraContext = trim((string) config('services.gemini.assistant_context', ''));
        $this->adminTrainingPrompt = trim((string) Setting::get('ai_assistant_prompt', ''));
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    public function chat(string $message, ?string $previousResponseId = null, array $meta = []): array
    {
        $shouldRecommendCourses = $this->shouldRecommendCourses($message, $meta);
        $recommendations = $shouldRecommendCourses ? $this->recommendCourses($message) : [];

        if (! $this->isConfigured()) {
            return [
                'success' => true,
                'message' => $this->buildFallbackReply($message, $recommendations, 'missing_api_key', $shouldRecommendCourses),
                'response_id' => null,
                'recommended_courses' => $recommendations,
                'source' => 'fallback_local',
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $this->buildInstructions()],
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

        $response = $this->client()->post(
            sprintf('/models/%s:generateContent?key=%s', $this->model, $this->apiKey),
            $payload
        );

        if (! $response->successful()) {
            $details = $response->json() ?: $response->body();
            $status = $response->status();

            return [
                'success' => true,
                'message' => $this->buildFallbackReply($message, $recommendations, 'api_error', $shouldRecommendCourses),
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
            'message' => $text ?: $this->buildFallbackReply($message, $recommendations, 'empty_response', $shouldRecommendCourses),
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

    protected function buildInstructions(): string
    {
        $siteName = Setting::get('site_name', 'Khai Tri Education');
        $tagline = Setting::get('site_tagline', 'Ná»n táº£ng há»c táº­p trá»±c tuyáº¿n');
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
                    '- %s | nhÃ³m ngÃ nh: %s | hÃ¬nh thá»©c: %s | giÃ¡: %s VND | trÃ¬nh Ä‘á»™: %s | thá»i lÆ°á»£ng: %s',
                    $course->title,
                    $course->category->name ?? 'ChÆ°a phÃ¢n loáº¡i',
                    $course->learning_type ?? 'online',
                    number_format((float) $price, 0, ',', '.'),
                    $course->level ?: 'ChÆ°a rÃµ',
                    $course->duration_label ?: 'ChÆ°a rÃµ'
                );
            })
            ->implode("\n");

        $parts = [
            "Báº¡n lÃ  trá»£ lÃ½ áº£o cá»§a {$siteName}.",
            'Má»¥c tiÃªu chÃ­nh: há»— trá»£ ngÆ°á»i dÃ¹ng vá» khÃ³a há»c, há»c phÃ­, Ä‘Äƒng kÃ½ há»c, lá»‹ch há»c, module, video, quiz, chá»©ng chá»‰, vÃ­, thanh toÃ¡n vÃ  liÃªn há»‡ há»— trá»£.',
            'LuÃ´n tráº£ lá»i báº±ng tiáº¿ng Viá»‡t cÃ³ dáº¥u, thÃ¢n thiá»‡n, rÃµ rÃ ng, tá»‘i Ä‘a 6 cÃ¢u ngáº¯n hoáº·c 5 gáº¡ch Ä‘áº§u dÃ²ng.',
            'Æ¯u tiÃªn tráº£ lá»i Ä‘Ãºng trá»ng tÃ¢m cÃ¢u há»i hiá»‡n táº¡i trÆ°á»›c, sau Ä‘Ã³ má»›i gá»£i Ã½ bÆ°á»›c tiáº¿p theo náº¿u cáº§n.',
            'KhÃ´ng tá»± Ä‘á»™ng chÃ¨n tÃªn khÃ³a há»c, khÃ´ng quáº£ng bÃ¡ khÃ³a há»c vÃ  khÃ´ng gá»£i Ã½ khÃ³a há»c náº¿u ngÆ°á»i dÃ¹ng khÃ´ng há»i vá» khÃ³a há»c, lá»™ trÃ¬nh há»c, há»c phÃ­, Ä‘Äƒng kÃ½ há»c hoáº·c tÆ° váº¥n chá»n mÃ´n phÃ¹ há»£p.',
            'Náº¿u ngÆ°á»i dÃ¹ng há»i vá» tÃ i khoáº£n, OTP, vÃ­, náº¡p tiá»n, thanh toÃ¡n, liÃªn há»‡, tin tá»©c hoáº·c thao tÃ¡c trÃªn website, chá»‰ tráº£ lá»i Ä‘Ãºng chá»§ Ä‘á» Ä‘Ã³.',
            'Náº¿u ngÆ°á»i dÃ¹ng Ä‘ang á»Ÿ trang chi tiáº¿t má»™t khÃ³a há»c vÃ  há»i vá» khÃ³a Ä‘ang xem, hÃ£y Æ°u tiÃªn giáº£i thÃ­ch khÃ³a Ä‘Ã³. KhÃ´ng tá»± kÃ©o thÃªm khÃ³a há»c khÃ¡c náº¿u ngÆ°á»i dÃ¹ng khÃ´ng yÃªu cáº§u.',
            'Náº¿u thiáº¿u dá»¯ liá»‡u Ä‘á»ƒ tÆ° váº¥n khÃ³a há»c, hÃ£y há»i láº¡i tá»‘i Ä‘a 1 cÃ¢u ngáº¯n Ä‘á»ƒ lÃ m rÃµ má»¥c tiÃªu há»c, trÃ¬nh Ä‘á»™ hiá»‡n táº¡i hoáº·c hÃ¬nh thá»©c há»c mong muá»‘n.',
            'Chá»‰ dá»±a trÃªn dá»¯ liá»‡u website. Náº¿u thiáº¿u thÃ´ng tin, nÃ³i rÃµ lÃ  chÆ°a cháº¯c vÃ  má»i ngÆ°á»i dÃ¹ng liÃªn há»‡ bá»™ pháº­n tÆ° váº¥n.',
            'Náº¿u tháº­t sá»± phÃ¹ há»£p, gá»£i Ã½ tá»‘i Ä‘a 3 khÃ³a há»c. KhÃ´ng bá»‹a Ä‘áº·t há»c phÃ­, lá»‹ch khai giáº£ng hay khuyáº¿n mÃ£i.',
            'ThÃ´ng tin website:',
            "- TÃªn website: {$siteName}",
            "- MÃ´ táº£: {$tagline}",
            '- Email liÃªn há»‡: ' . ($contactEmail ?: 'ChÆ°a cáº­p nháº­t'),
            '- Sá»‘ Ä‘iá»‡n thoáº¡i: ' . ($contactPhone ?: 'ChÆ°a cáº­p nháº­t'),
            '- Äá»‹a chá»‰: ' . ($contactAddress ?: 'ChÆ°a cáº­p nháº­t'),
            '- NhÃ³m ngÃ nh hiá»‡n cÃ³: ' . (! empty($categories) ? implode(', ', $categories) : 'ChÆ°a cáº­p nháº­t'),
            "- Má»™t sá»‘ khÃ³a há»c Ä‘ang hiá»ƒn thá»‹:\n" . ($courses ?: '- ChÆ°a cÃ³ dá»¯ liá»‡u khÃ³a há»c published.'),
        ];

        if ($this->adminTrainingPrompt !== '') {
            $parts[] = "HÆ°á»›ng dáº«n bá»• sung tá»« quáº£n trá»‹ viÃªn:\n{$this->adminTrainingPrompt}";
        }

        if ($this->extraContext !== '') {
            $parts[] = "Ngá»¯ cáº£nh bá»• sung tá»« biáº¿n mÃ´i trÆ°á»ng:\n{$this->extraContext}";
        }

        return implode("\n\n", $parts);
    }

    protected function buildUserPrompt(string $message, array $meta = [], array $recommendations = [], bool $shouldRecommendCourses = false): string
    {
        $segments = [];

        if (! empty($meta['current_url'])) {
            $segments[] = 'NgÆ°á»i dÃ¹ng Ä‘ang á»Ÿ trang: ' . $meta['current_url'];
        }

        if (! empty($meta['page_title'])) {
            $segments[] = 'TiÃªu Ä‘á» trang hiá»‡n táº¡i: ' . $meta['page_title'];
        }

        $segments[] = $shouldRecommendCourses
            ? 'Cháº¿ Ä‘á»™ gá»£i Ã½ khÃ³a há»c: Báº¬T. Chá»‰ nháº¯c Ä‘áº¿n hoáº·c gá»£i Ã½ khÃ³a há»c náº¿u tháº­t sá»± giÃºp tráº£ lá»i Ä‘Ãºng cÃ¢u há»i.'
            : 'Cháº¿ Ä‘á»™ gá»£i Ã½ khÃ³a há»c: Táº®T. KhÃ´ng gá»£i Ã½ hay liá»‡t kÃª khÃ³a há»c, chá»‰ tráº£ lá»i Ä‘Ãºng váº¥n Ä‘á» ngÆ°á»i dÃ¹ng Ä‘ang há»i.';

        $history = collect($meta['history'] ?? [])
            ->filter(fn ($item) => filled($item['message'] ?? null))
            ->take(-8)
            ->map(function ($item) {
                $role = ($item['role'] ?? 'user') === 'assistant' ? 'Trá»£ lÃ½' : 'NgÆ°á»i dÃ¹ng';

                return $role . ': ' . trim((string) $item['message']);
            })
            ->values()
            ->all();

        if (! empty($history)) {
            $segments[] = "Há»™i thoáº¡i gáº§n Ä‘Ã¢y:\n" . implode("\n", $history);
        }

        if (! empty($recommendations)) {
            $segments[] = "KhÃ³a há»c nÃªn Æ°u tiÃªn gá»£i Ã½ náº¿u phÃ¹ há»£p:\n" . collect($recommendations)
                ->map(fn ($course) => '- ' . $course['title'] . ' | ' . ($course['category'] ?: 'ChÆ°a phÃ¢n loáº¡i') . ' | ' . $course['price_label'])
                ->implode("\n");
        }

        $segments[] = 'CÃ¢u há»i cá»§a ngÆ°á»i dÃ¹ng: ' . trim($message);

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
                $matched[] = 'tiÃªu Ä‘á» trÃ¹ng nhu cáº§u';
            }

            foreach ($keywords as $keyword) {
                if (Str::contains($title, $keyword)) {
                    $score += 4;
                    $matched[] = 'phÃ¹ há»£p tá»« khÃ³a "' . $keyword . '"';
                } elseif (Str::contains($category, $keyword) || Str::contains($learningType, $keyword) || Str::contains($level, $keyword)) {
                    $score += 3;
                    $matched[] = 'gáº§n vá»›i chá»§ Ä‘á» "' . $keyword . '"';
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
                'category' => $course->category->name ?? 'ChÆ°a phÃ¢n loáº¡i',
                'learning_type' => $course->learning_type ?: 'online',
                'level' => $course->level ?: 'ChÆ°a rÃµ',
                'duration' => $course->duration_label ?: 'ChÆ°a cáº­p nháº­t',
                'price_label' => ((float) $price) > 0 ? number_format((float) $price, 0, ',', '.') . ' VND' : 'LiÃªn há»‡ tÆ° váº¥n',
                'url' => route('courses.show', $course),
                'reason' => $item['reason'] ?: 'phÃ¹ há»£p vá»›i nhu cáº§u hiá»‡n táº¡i',
            ];
        })->all();
    }

    protected function buildFallbackReply(string $message, array $recommendations, string $reason, bool $courseIntent = false): string
    {
        if (! $courseIntent) {
            return $this->buildNonCourseFallbackReply($message, $reason);
        }

        $intro = match ($reason) {
            'missing_api_key' => 'Trá»£ lÃ½ AI chÆ°a Ä‘Æ°á»£c cáº¥u hÃ¬nh Ä‘áº§y Ä‘á»§ nÃªn mÃ¬nh Ä‘ang tÆ° váº¥n theo dá»¯ liá»‡u sáºµn cÃ³ trÃªn website.',
            'api_error' => 'Trá»£ lÃ½ AI táº¡m thá»i gáº·p lá»—i káº¿t ná»‘i nÃªn mÃ¬nh Ä‘ang gá»£i Ã½ nhanh theo dá»¯ liá»‡u website.',
            default => 'MÃ¬nh Ä‘ang tráº£ lá»i dá»±a trÃªn dá»¯ liá»‡u khÃ³a há»c hiá»‡n cÃ³ trÃªn website.',
        };

        $suggestions = collect($recommendations)
            ->take(3)
            ->map(function ($course, $index) {
                return ($index + 1) . '. ' . $course['title'] . ' (' . $course['category'] . ', ' . $course['price_label'] . ')';
            })
            ->implode("\n");

        $parts = [$intro];

        if ($suggestions !== '') {
            $parts[] = "Báº¡n cÃ³ thá»ƒ tham kháº£o:\n" . $suggestions;
        } else {
            $parts[] = 'Hiá»‡n táº¡i mÃ¬nh chÆ°a tÃ¬m Ä‘Æ°á»£c khÃ³a há»c tháº­t sá»± sÃ¡t nhu cáº§u tá»« ná»™i dung báº¡n vá»«a gá»­i.';
        }

        $parts[] = 'Báº¡n cÃ³ thá»ƒ nÃ³i rÃµ hÆ¡n báº¡n muá»‘n há»c mÃ´n gÃ¬, má»¥c tiÃªu há»c lÃ  gÃ¬, trÃ¬nh Ä‘á»™ hiá»‡n táº¡i ra sao hoáº·c muá»‘n há»c online/offline Ä‘á»ƒ mÃ¬nh gá»£i Ã½ sÃ¡t hÆ¡n.';

        return implode("\n\n", $parts);
    }

    protected function buildNonCourseFallbackReply(string $message, string $reason): string
    {
        $normalizedMessage = $this->normalizeText($message);
        $intro = match ($reason) {
            'missing_api_key' => 'Trá»£ lÃ½ AI chÆ°a Ä‘Æ°á»£c cáº¥u hÃ¬nh Ä‘áº§y Ä‘á»§ nÃªn mÃ¬nh Ä‘ang há»— trá»£ theo dá»¯ liá»‡u sáºµn cÃ³ trÃªn website.',
            'api_error' => 'Trá»£ lÃ½ AI táº¡m thá»i gáº·p lá»—i káº¿t ná»‘i nÃªn mÃ¬nh Ä‘ang há»— trá»£ theo dá»¯ liá»‡u sáºµn cÃ³ trÃªn website.',
            default => 'MÃ¬nh Ä‘ang há»— trá»£ báº¡n theo dá»¯ liá»‡u hiá»‡n cÃ³ trÃªn website.',
        };

        if (Str::contains($normalizedMessage, ['dang nhap', 'dang ky tai khoan', 'quen mat khau', 'otp', 'xac thuc'])) {
            return $intro . "\n\nNáº¿u báº¡n cáº§n vÃ o tÃ i khoáº£n, hÃ£y dÃ¹ng má»¥c ÄÄƒng nháº­p hoáº·c ÄÄƒng kÃ½ á»Ÿ gÃ³c trÃªn. Náº¿u quÃªn máº­t kháº©u, chá»n QuÃªn máº­t kháº©u Ä‘á»ƒ nháº­n hÆ°á»›ng dáº«n Ä‘áº·t láº¡i.";
        }

        if (Str::contains($normalizedMessage, ['wallet', 'vi', 'nap tien', 'topup', 'thanh toan', 'hoa don'])) {
            return $intro . "\n\nNáº¿u báº¡n cáº§n náº¡p vÃ­, xem sá»‘ dÆ° hoáº·c giao dá»‹ch thanh toÃ¡n, hÃ£y vÃ o má»¥c VÃ­ cá»§a tÃ´i sau khi Ä‘Äƒng nháº­p. Náº¿u báº¡n nÃ³i rÃµ Ä‘ang vÆ°á»›ng á»Ÿ bÆ°á»›c nÃ o, mÃ¬nh sáº½ hÆ°á»›ng dáº«n tiáº¿p ngáº¯n gá»n hÆ¡n.";
        }

        if (Str::contains($normalizedMessage, ['lien he', 'dia chi', 'so dien thoai', 'email'])) {
            return $intro . "\n\nNáº¿u báº¡n cáº§n liÃªn há»‡ trung tÃ¢m, hÃ£y vÃ o má»¥c LiÃªn há»‡ Ä‘á»ƒ xem sá»‘ Ä‘iá»‡n thoáº¡i, email vÃ  Ä‘á»‹a chá»‰ hiá»‡n cÃ³ trÃªn website. Náº¿u muá»‘n, mÃ¬nh cÃ³ thá»ƒ hÆ°á»›ng dáº«n báº¡n tÃ¬m Ä‘Ãºng má»¥c Ä‘Ã³.";
        }

        if (Str::contains($normalizedMessage, ['tin tuc', 'news'])) {
            return $intro . "\n\nNáº¿u báº¡n muá»‘n xem bÃ i viáº¿t hoáº·c thÃ´ng bÃ¡o má»›i, hÃ£y vÃ o má»¥c Tin tá»©c trÃªn thanh menu. Náº¿u báº¡n cáº§n tÃ¬m má»™t chá»§ Ä‘á» cá»¥ thá»ƒ, cá»© nÃ³i rÃµ hÆ¡n Ä‘á»ƒ mÃ¬nh hÆ°á»›ng báº¡n nhanh hÆ¡n.";
        }

        return $intro . "\n\nBáº¡n cÃ³ thá»ƒ nÃ³i rÃµ hÆ¡n báº¡n Ä‘ang cáº§n há»— trá»£ vá» tÃ i khoáº£n, vÃ­, thanh toÃ¡n, liÃªn há»‡ hay thao tÃ¡c nÃ o trÃªn website Ä‘á»ƒ mÃ¬nh hÆ°á»›ng dáº«n Ä‘Ãºng trá»ng tÃ¢m.";
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