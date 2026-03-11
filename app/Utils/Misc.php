<?php
namespace App\Utils;

class Misc
{
    /**
     * Send a standard text email using PHP's native mailer or SMTP.
     */
    public function send_mail(string $to, string $subject, string $body): bool
    {
        // Pull the 'From' address from the .env configuration, fallback to a local generated one
        $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $siteName = $_ENV['SITE_NAME'] ?? 'Imageboard';

        $headers = [
            'From' => "$siteName <$fromEmail>",
            'Reply-To' => $fromEmail,
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/plain; charset=utf-8'
        ];

        // Format headers for the native mail() function
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }

        // Send the email
        return mail($to, $subject, $body, $headerString);
    }

    /**
     * Generates standard HTML pagination links.
     */
    public function pagination(string $pageType, string $action, string|int|bool $id, int $limit, int $pageLimit, int $count, int $currentPage, string $tags = ""): string
    {
        $hasId = ($id !== false && $id !== '') ? "&amp;id=" . urlencode((string) $id) : "";
        $hasTags = ($tags !== "") ? "&amp;tags=" . urlencode($tags) : "";
        $actionStr = ($action) ? "&amp;s=" . urlencode($action) : "";

        $pages = (int) ceil($count / $limit);
        if ($pages < 1)
            $pages = 1;

        $output = "";
        $start = (int) floor($currentPage / $limit) + 1;
        $tmpLimit = min($pages, $start + $pageLimit);
        $lowerLimit = max(1, $pages - $pageLimit);

        if ($start > $lowerLimit) {
            $start = $lowerLimit;
        }

        if ($currentPage > 0) {
            $backPage = max(0, $currentPage - $limit);
            $output .= '<a href="?page=' . $pageType . $actionStr . $hasId . $hasTags . '&amp;pid=0" alt="first page">&lt;&lt;</a> ';
            $output .= '<a href="?page=' . $pageType . $actionStr . $hasId . $hasTags . '&amp;pid=' . $backPage . '" alt="back">&lt;</a> ';
        }

        for ($i = $start; $i <= $tmpLimit; $i++) {
            $pPage = $limit * ($i - 1);
            if ($pPage >= 0) {
                if ($pPage == $currentPage) {
                    $output .= '<b>' . $i . '</b> ';
                } else {
                    $output .= '<a href="?page=' . $pageType . $actionStr . $hasId . $hasTags . '&amp;pid=' . $pPage . '">' . $i . '</a> ';
                }
            }
        }

        $lastPageNum = $limit * ($pages - 1);
        if ($currentPage < $lastPageNum && $pages > 1) {
            $nextPage = $currentPage + $limit;
            $output .= '<a href="?page=' . $pageType . $actionStr . $hasId . $hasTags . '&amp;pid=' . $nextPage . '" alt="next">&gt;</a> ';
            $output .= '<a href="?page=' . $pageType . $actionStr . $hasId . $hasTags . '&amp;pid=' . $lastPageNum . '" alt="last page">&gt;&gt;</a> ';
        }

        return $output;
    }

    public function isHtml(string $text): bool
    {
        return preg_match("/<[^<]+>/", $text) !== 0;
    }

    public function windowsFilenameFix(string $text): string
    {
        return str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $text);
    }
}