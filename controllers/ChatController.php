<?php
namespace Controllers;
use OpenApi\Annotations as OA; 

use Models\ChatModel;
use Middlewares\AuthMiddleware;


class ChatController {
    private $model;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $this->model = new ChatModel();
        $this->auth = new AuthMiddleware();
        $this->gpt_model_extraction = 'gpt-4.1-mini';
        $this->gpt_model_response = 'gpt-4.1-nano';
        $this->api_key = $config['openaiApiKey'];
    }

    public function getArtList() {
        $arts = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode($arts, JSON_UNESCAPED_UNICODE);
    }
    
    public function postChat() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $data = json_decode(file_get_contents('php://input'), true);
        $userText = $data['text'];
        $todayDate = date('Y-m-d H:i:s');

        # 대화 내용 DB에 저장
        # addConversations($userId, 'user', userText);

        $extractPrompt = <<<PROMPT
            사용자 질문에서 의도를 파악하여 주어진 JSON 형식으로 작성하십시오. 필요하다면 대화 내역 context를 참고하십시오.
            사용자가 요구한 내용에 대한 key-value만 작성하십시오. 내용이 없는 key는 생략하십시오.
            날짜에 대한 정보가 필요할 경우, 기준이 되는 날짜는 {$todayDate}입니다.
            형식: 
            {
            "intent": {
                "object": "exhibition" | "artist" | "gallery" | "news" | "other"
            },
            "entity": {
                "title": "제목",
                "category": "카테고리" (없으면 생략),
                "date_range": ["YYYY-MM-DD", "YYYY-MM-DD"],
                "time_range": ["HH:mm", "HH:mm"],
                "location": "지역명 또는 장소명",
                "price": 가격 (exhibition일 때만, 정수로 작성) (단순히 "유료"일 경우, 999999로 작성),
                "tag": "태그" (주제를 단어로 작성) (object가 exhibition이 아니면 생략) (없으면 생략),
                "name": "이름" (artist, gallery일 때만 작성) (없으면 생략),
                "nation": "국적" (artist일 때만 작성) (없으면 생략)
            }
            }
            질문: "{$userText}"
            PROMPT;

        $extraction = $this->chatWithGPT($userId, $extractPrompt, $this->gpt_model_extraction);
        $jsonObject = json_decode($extraction, true);
        $intent = $jsonObject['intent']['object'];
        $filters = $jsonObject['entity'] ?? [];

        switch ($intent) {
            case 'exhibition':
                $gptResponse = exhibitionRoutine($userId, $filters, $userText);
                break;
            case 'artist':
                $gptResponse = artistRoutine($userId, $filters, $userText);
                break;
            case 'gallery':
                $gptResponse = galleryRoutine($userId, $filters, $userText);
                break;
            case 'news':
                $gptResponse = newsRoutine($userId, $filters, $userText);
                break;
            default:
                $gptResponse = defaultRoutine($userId, $userText);
        }
    }

    # GPT와 대화
    function chatWithGPT($userId, $extractPrompt, $gpt_model) {

        # $history = getConversationHistory($userId); // 대화 기록 배열 반환 (예: [['role'=>'user','content'=>'...'], ...])
        $messages = array_merge(
            [['role' => 'system', 'content' => "당신은 사용자의 질문에서 의도를 파악하여 정해진 json 형식으로 반환하는 봇입니다."]],
            # $history,
            [['role' => 'user', 'content' => $extractPrompt]]
        );

        $postData = [
            'model' => $gpt_model,
            'messages' => [
                ['role' => 'system', 'content' => "당신은 사용자의 질문에서 의도를 파악하여 정해진 json 형식으로 반환하는 봇입니다."],
                # $history,
                ['role' => 'user', 'content' => $extractPrompt]
            ],
            'temperature' => 0.6
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $this->api_key"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'curl error: ' .curl_error($ch);
        } else {
            $response = json_decode($result, true);
            if (isset($response['error'])) {
                echo "Error: ".$response['error']['message'];
            } else {
                return $response['choices'][0]['message']['content'] ?? 'GPT 응답 오류 발생';
            }
            curl_close($ch);
        }
    }
}

