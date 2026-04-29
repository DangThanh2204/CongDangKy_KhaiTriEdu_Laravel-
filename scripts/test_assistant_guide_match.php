<?php

/**
 * Standalone test harness for the guide-matching logic.
 * Re-implements the same scoring rule as GeminiChatService::matchGuides()
 * to verify the knowledge base catches expected questions.
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Str;

$guides = require __DIR__ . '/../config/assistant_guides.php';
$guides = $guides['guides'] ?? [];

function normalizeText(string $value): string
{
    return Str::of($value)
        ->lower()
        ->ascii()
        ->replaceMatches('/[^a-z0-9]+/u', ' ')
        ->replaceMatches('/\s+/', ' ')
        ->trim()
        ->toString();
}

function extractPath(?string $url): string
{
    if (! is_string($url) || trim($url) === '') {
        return '';
    }
    $parsed = parse_url(trim($url));
    $path = $parsed['path'] ?? $url;
    return '/' . ltrim((string) $path, '/');
}

function matchGuides(array $guides, string $message, ?string $currentUrl): array
{
    $normalizedMessage = normalizeText($message);
    $currentPath = extractPath($currentUrl);
    $scored = [];
    foreach ($guides as $key => $guide) {
        $score = 0;
        foreach (($guide['keywords'] ?? []) as $keyword) {
            $needle = normalizeText($keyword);
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
            $scored[] = ['key' => $key, 'score' => $score, 'title' => $guide['title']];
        }
    }
    usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
    return array_slice($scored, 0, 3);
}

$cases = [
    ['msg' => 'lam sao de nap vi qua vnpay', 'url' => '/wallet'],
    ['msg' => 'toi quen mat khau roi', 'url' => null],
    ['msg' => 'cach dang ky khoa hoc tieng anh', 'url' => '/courses/tieng-anh-co-ban'],
    ['msg' => 'lam quiz the nao', 'url' => null],
    ['msg' => 'xem chung chi o dau', 'url' => null],
    ['msg' => 'tra cuu chung chi cua hoc vien', 'url' => null],
    ['msg' => 'huy dang ky de hoan tien', 'url' => null],
    ['msg' => 'doi mat khau moi', 'url' => '/profile/change-password'],
    ['msg' => 'toi muon lien he ho tro', 'url' => null],
    ['msg' => 'hello', 'url' => null],
];

foreach ($cases as $c) {
    $matched = matchGuides($guides, $c['msg'], $c['url']);
    echo 'Q: ' . $c['msg'] . ' | URL: ' . ($c['url'] ?? '-') . PHP_EOL;
    echo '  -> ' . (empty($matched)
        ? '(no match)'
        : implode(' | ', array_map(fn ($m) => $m['title'] . ' [' . $m['score'] . ']', $matched))) . PHP_EOL;
}
