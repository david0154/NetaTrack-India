<?php
namespace NetaTrack\Helpers;

class Pagination {
    public static function render(array $data, string $baseUrl = ''): string {
        if ($data['last_page'] <= 1) return '';
        $current  = $data['current_page'];
        $last     = $data['last_page'];
        $baseUrl  = $baseUrl ?: strtok($_SERVER['REQUEST_URI'], '?');
        $html     = '<nav class="pagination" aria-label="Pagination">';
        $html    .= '<ul class="pagination__list">';

        if ($current > 1) {
            $html .= '<li><a href="' . $baseUrl . '?page=' . ($current - 1) . '" class="pagination__btn">← Prev</a></li>';
        }
        for ($i = max(1, $current - 2); $i <= min($last, $current + 2); $i++) {
            $active = $i === $current ? ' active' : '';
            $html  .= "<li><a href=\"$baseUrl?page=$i\" class=\"pagination__btn$active\">$i</a></li>";
        }
        if ($current < $last) {
            $html .= '<li><a href="' . $baseUrl . '?page=' . ($current + 1) . '" class="pagination__btn">Next →</a></li>';
        }
        $html .= '</ul></nav>';
        return $html;
    }
}
