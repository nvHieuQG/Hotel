<?php

namespace App\Services;

class ProfanityFilterService
{
    /**
     * Danh sách từ cấm (Vietnamese và English)
     */
    private $badWords = [
        // Vietnamese profanity
        'địt', 'đù', 'đụ', 'cặc', 'lồn', 'buồi', 'đéo', 'vãi', 'đĩ', 'cave',
        'óc chó', 'đồ chó', 'thằng chó', 'con chó', 'đồ khốn', 'khốn nạn',
        'đồ súc sinh', 'súc sinh', 'đồ đĩ', 'con đĩ', 'thằng đĩ',
        'đồ ngu', 'ngu ngốc', 'đồ ngốc', 'ngốc nghếch', 'đần độn',
        'đồ khùng', 'điên khùng', 'đồ điên', 'tâm thần', 'đồ dở hơi',
        'chết tiệt', 'đồ chết tiệt', 'đi chết', 'chết đi', 'đi chết đi',
        'đồ ranh', 'ranh con', 'đồ bẩn', 'bẩn thỉu', 'đồ bẩn thỉu',
        'mẹ kiếp', 'đồ mẹ kiếp', 'kiếp nạn', 'đồ kiếp nạn',
        'đồ phản bội', 'phản bội', 'đồ phản động', 'phản động',
        'đồ bán nước', 'bán nước', 'đồ tham nhũng', 'tham nhũng',
        'địt mẹ', 'đù mẹ', 'đụ má', 'cút đi', 'cút mẹ',
        
        // English profanity
        'fuck', 'shit', 'damn', 'bitch', 'ass', 'hell', 'bastard',
        'asshole', 'motherfucker', 'cocksucker', 'dickhead', 'prick',
        'whore', 'slut', 'cunt', 'pussy', 'cock', 'dick', 'penis',
        'vagina', 'boobs', 'tits', 'nipples', 'sex', 'porn', 'xxx'
    ];

    /**
     * Lọc tin nhắn và thay thế từ cấm bằng 3 dấu ***
     *
     * @param string $message
     * @return string
     */
    public function filterMessage(string $message): string
    {
        if (empty($message)) {
            return $message;
        }

        $words = explode(' ', $message);
        $filteredWords = [];

        foreach ($words as $word) {
            $cleanWord = $this->cleanWord($word);
            $isFiltered = false;

            foreach ($this->badWords as $badWord) {
                if ($this->isMatch($cleanWord, $badWord)) {
                    // Thay thế bằng 3 dấu ***
                    $filteredWords[] = '***';
                    $isFiltered = true;
                    break;
                }
            }

            if (!$isFiltered) {
                $filteredWords[] = $word;
            }
        }

        return implode(' ', $filteredWords);
    }

    /**
     * Kiểm tra tin nhắn có chứa từ cấm không
     *
     * @param string|null $message
     * @return bool
     */
    public function containsProfanity(?string $message): bool
    {
        if (empty($message)) {
            return false;
        }

        $cleanMessage = mb_strtolower($message, 'UTF-8');
        
        foreach ($this->badWords as $badWord) {
            if (mb_strpos($cleanMessage, mb_strtolower($badWord, 'UTF-8')) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Làm sạch từ để so sánh (loại bỏ dấu câu)
     *
     * @param string $word
     * @return string
     */
    private function cleanWord(string $word): string
    {
        // Loại bỏ dấu câu và ký tự đặc biệt
        $cleanWord = preg_replace('/[.,!?;:"\'()\[\]{}]/u', '', $word);
        return mb_strtolower(trim($cleanWord), 'UTF-8');
    }

    /**
     * Kiểm tra từ có khớp với từ cấm không
     *
     * @param string $cleanWord
     * @param string $badWord
     * @return bool
     */
    private function isMatch(string $cleanWord, string $badWord): bool
    {
        $badWordLower = mb_strtolower($badWord, 'UTF-8');
        
        // Kiểm tra khớp chính xác hoặc chứa từ cấm
        return $cleanWord === $badWordLower || 
               mb_strpos($cleanWord, $badWordLower) !== false;
    }

    /**
     * Thêm từ cấm mới vào danh sách
     *
     * @param array $newWords
     * @return void
     */
    public function addBadWords(array $newWords): void
    {
        $this->badWords = array_merge($this->badWords, $newWords);
        $this->badWords = array_unique($this->badWords);
    }

    /**
     * Lấy danh sách từ cấm hiện tại
     *
     * @return array
     */
    public function getBadWords(): array
    {
        return $this->badWords;
    }
}
